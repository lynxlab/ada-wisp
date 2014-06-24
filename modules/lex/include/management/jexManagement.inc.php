<?php
/**
 * JEX Management Class for lex module
 *
 * @package 	lex
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	    0.1
 */

/**
 * class for managing JEX
 *
 * @author giorgio
 */

require_once MODULES_LEX_PATH. '/include/management/abstractImportManagement.inc.php';
require_once MODULES_LEX_PATH. '/include/form/formJexImport.php';
require_once MODULES_LEX_PATH. '/include/functions.inc.php';
		
class jexManagement extends importManagement
{   
	/**
	 * userObj owner of the inserted assets
	 * @var ADALoggableUser 
	 */
	private $_userObj;
	
	/**
	 * id_fonte for asset association
	 * @var number
	 */
	private $_id_fonte;
	
	public function __construct($userObj = null) {
		parent::__construct();
		
		if (!is_null($userObj)) $this->_userObj = $userObj;
	}
	
	/**
	 * saves the fonte from POST array and runs the import
	 *
	 * @see lexManagement::run()
	 */
	public function save() {
		$this->_mustValidate = false;
		$this->_setLogFileName("jex-import_".date('d-m-Y_His').".log");
		
		/**
		 * save into table fonti first
		 */
		$result = $this->saveFromPOST();
		
		if (!AMA_DB::isError($result)) {
			$this->_id_fonte = $result[AMALexDataHandler::$PREFIX.'fonti_id'];
			$this->_logMessage(translateFN('Fonte salvata correttamente').' id='.$this->_id_fonte);
			
			// fonte saved ok, now run the import on the zip file
			parent::run();
		} else {
			$this->_logMessage('**'.translateFN('Problema nel salvataggio della fonte').'**');
			$this->_logMessage('**'.print_r($result, true).'**');
		}			
		
		/**
		 * send a javascript to the browser that will show the add new fonte button
		 */
		sendToBrowser('<script type="text/javascript">parent.showAddNewButton();</script>');
	}
	
	/**
	 * does the actual saving in the db from POST array
	 * 
	 * @return Ambigous <multitype:, AMA_Error, array, mixed, boolean, object, PDOException, PDOStatement, unknown_type>|AMA_Error
	 * 
	 * @access public
	 */
	public function saveFromPOST() {
		$form = new FormJexImport('jex', null, $this->_dh->getTypologies());
		$form->fillWithPostData();
		if ($form->isValid()) {			
			$fonteAr = array(
					AMALexDataHandler::$PREFIX.'fonti_id' => intval($_POST['id_fonte']),
					'numero'=> trim($_POST['numero_fonte']),
					'titolo'=> trim($_POST['titolo_fonte']),
					'data_pubblicazione'=> $this->_dh->date_to_ts($_POST['data_pubblicazione']),
					AMALexDataHandler::$PREFIX.'tipologie_fonti_id'=> intval($_POST['tipologia'])
			);
			return $this->_dh->fonti_set($fonteAr);
		} else return new AMA_Error(AMA_ERR_INCONSISTENT_DATA);		
	}
	
	/**
	 * gets a sources from the module lex table
	 * 
	 * @param string $id the id of the source to load
	 * 
	 * @return NULL|array
	 */
	public function getSource($id=null) {		
		if (!is_null($id)) {
			// load the source by passing a clause to the datahandler
			$res = $this->_dh->get_sources(array(),true,'`'.AMALexDataHandler::$PREFIX.'fonti_id`='.$id);
			if (!AMA_DB::isError($res)) {
				/**
				 * if it's only one element (as it should be) return it
				 * else return the whole array
				 */
				if (count($res)===1) return $res[0];
				else return $res;
				
			} else return $res;
		} else {
			return null;
		}
	}
	
	/**
	 * This will read the attributes of the root node and
	 * call the appropriate method to import the tableName
	 *
	 * @param DOMElement $XMLObj
	 * @param string $tablename
	 *
	 * @access protected
	 */
	protected function _importXMLRoot ($XMLObj, $tableName) {
		
		$documents = $XMLObj->getElementsByTagName('document');
		$savedAssetCount = 0;
		
		foreach ($documents as $document) {
			$fileName = basename(preg_replace('/(\\\\(\S))/', "/$2", $document->getAttribute('id')));			
			if (is_file($this->_destDir . DIRECTORY_SEPARATOR . $fileName)) {
				$this->_logMessage(translateFN('Sto salvando').' '.$fileName);
				$testo = file_get_contents($this->_destDir . DIRECTORY_SEPARATOR . $fileName);
				$savedTestoHa = $this->_dh->testi_set (array('testo'=>$testo));				
				
				if (!AMA_DB::isError($savedTestoHa)) {
					// now must generate and save the asset
					$assetHa = array(							
						'label'            => pathinfo($fileName, PATHINFO_FILENAME), // fileName without ext.
						'url'              => null,
						AMALexDataHandler::$PREFIX.'fonti_id' => $this->_id_fonte,						
						'id_utente'        => $this->_userObj->getId(),
						AMALexDataHandler::$PREFIX.'testi_id' => $savedTestoHa[AMALexDataHandler::$PREFIX.'testi_id'],
						'data_inserimento' => $this->_dh->date_to_ts('now'),
						'data_verifica'    => null,
						'stato'            => MODULES_LEX_ASSET_STATE_UNVERIFIED
					);					
					$savedAssetHa = $this->_dh->asset_set ($assetHa);
					
					if (!AMA_DB::isError($savedAssetHa)) {
						
						$this->_logMessage(translateFN('Asset salvato correttamente').' id='.$savedAssetHa[AMALexDataHandler::$PREFIX.'assets_id']);
						// now must generate and save eurovoc/asset relation

						if ($document->hasChildNodes()) {
							foreach ($document->childNodes as $child) {
								switch (strtoupper($child->nodeName)) {
									case 'CATEGORY':
										if ($child->hasAttribute('code')) {
											$weight = ($child->hasAttribute('weight')) ? ($child->getAttribute('weight')) : 0;
											
											$eurovoc_relHa[] = array(
												'descripteur_id' => $child->getAttribute('code'),
												AMALexDataHandler::$PREFIX.'assets_id' => $savedAssetHa[AMALexDataHandler::$PREFIX.'assets_id'],
												'weight' => $weight
											);
										}
										break;
								}
							}
							
							// actually save eurovoc relations in a single insert
							if (isset($eurovoc_relHa) && !empty($eurovoc_relHa)) {
								$result = $this->_dh->insertMultiRow($eurovoc_relHa,'eurovoc_rel');
								unset ($eurovoc_relHa);
								if (!AMA_DB::isError($result)) {
									$this->_logMessage(translateFN('Relazioni con eurovoc salvate').'...');
									$this->_logMessage('['.translateFN('Asset OK').']');
								} else {
									$this->_logMessage('**'.translateFN('Errore').'**');
									$this->_logMessage('**'.print_r($result, true).'**');
								}
							}
						}
						$savedAssetCount++;
					} else {
						$this->_logMessage('**'.translateFN('Errore').'**');
						$this->_logMessage('**'.print_r($savedAssetHa, true).'**');			
					} // ends if (!AMA_DB::isError($savedAssetHa))
				} else {
					$this->_logMessage('**'.translateFN('Errore').'**');
					$this->_logMessage('**'.print_r($savedTestoHa, true).'**');
				} // ends if (!AMA_DB::isError($savedTestoHa))
			} else {
				$this->_logMessage('**'.translateFN('File non leggibile').': '.$fileName.'**');
			}
		}
		
		$this->_logMessage($savedAssetCount.' '.translateFN('asset importati'));
	}
	
	/**
	 * gets the label to be used in the UI tab
	 *
	 * @return string
	 * 
	 * @access public
	 */
    public static function getTabTitle($actionCode) {
    	
    	if ($actionCode===IMPORT_JEX)
    		return translateFN('Nuova Fonte');
    	else if ($actionCode===EDIT_SOURCE)
    		return translateFN('Modifica Fonte');
    }
    
    /**
     * gets the HTML to be rendered on index.php?op=zoom page
     * basically, it's a table for asset data display and an
     * empty div that will be used by jQuery fancytree plugin
     * 
     * @param string $title title and caption of the table
     * @param number $sourceID the id of the source to be displayed
     * 
     * @return CDOMElement
     * 
     * @access public
     */
    public static function getSourceZoomContent($title, $sourceID) {
    	
    	$htmlObj = CDOMElement::create('div','id:assetsContainer_'.$sourceID);
    	
    	/**
    	 * generate an empty table that will be filled by the jQuery dataTable ajax calls
    	 */
    	$labels = array ('&nbsp;',translateFN('etichetta'), translateFN('URL'), translateFN('Data Inserimento') , translateFN('Data Verifica'), translateFN('stato'));
    	
    	foreach ($labels as $label) {
    		$assetsData[0][$label] = '';
    	}
    	
    	$assetsTable = new Table();
    	$assetsTable->initTable('0','center','1','1','90%','','','','','1','0','','default','assetsTable');
    	$assetsTable->setTable($assetsData,$title,$title);
    	
    	$tableDIV = CDOMElement::create('div','class:assetTableContainer ui-widget-content');
    	$treeDIV = CDOMElement::create('div','class:assetTreeContainer');
    	
    	$tableDIV->addChild(new CText($assetsTable->getTable()));
    	
    	$fancyTree = CDOMElement::create('div','id:selectEurovocTerms');
    	$echo = CDOMElement::create('div','id:echoSelection');
    	$treeDIV->addChild($fancyTree);
    	$treeDIV->addChild($echo);
    	
    	$htmlObj->addChild($treeDIV);
    	$htmlObj->addChild($tableDIV);
    	$htmlObj->addChild(CDOMElement::create('div','class:clearfix'));
    	
    	return $htmlObj;
    }
    
    /**
     * gets the HTML form to be rendered as the UI tab edit
     * 
     * @return CDOMElement
     * 
     * @access public
     */
    public static function getEditContent() {
    	
    	$htmlObj = CDOMElement::create('div','id:editSourceContainer');

		/**
         * generate an empty table that will be filled by the jQuery dataTable ajax calls
		 */    	
    	$labels = array (translateFN('Numero'), translateFN('Titolo'), translateFN('Data Pubb. G.U.') , translateFN('Tipologia'), translateFN('azioni'));
    	
    	foreach ($labels as $label) {
    		$sourcesData[0][$label] = '';
    	}

    	$sourcesTable = new Table();
    	$sourcesTable->initTable('0','center','1','1','90%','','','','','1','0','','default','sourcesTable');
    	$sourcesTable->setTable($sourcesData,translateFN('Archivio Fonti'),translateFN('Archivio Fonti'));

    	$htmlObj->addChild(new CText($sourcesTable->getTable()));
    	
    	return $htmlObj;
    }
    
    /**
     * gets the HTML form to be rendered as the UI tab new
     *
     * @return CDOMElement
     * 
     * @access public
     */
	public static function getImportForm() {
		
		$dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
		
		$typologiesArr = $dh->getTypologies();
		if (AMA_DB::isError($typologiesArr)) $typologiesArr = array();
		
		$htmlObj = CDOMElement::create('div','id:jexContainer');
		
		$title = CDOMElement::create('span', 'class:importTitle');
		$title->addChild (new CText(translateFN('Nuova Fonte (importa da JEX)')));
		
		$form = new FormJexImport('jex', MODULES_LEX_HTTP. '/doImportJex.php', $typologiesArr);
		
		$addTypologyEmptyText = CDOMElement::create('span','id:addTypologyEmptyText');
		$addTypologyEmptyText->setAttribute('style', 'display:none');
		$addTypologyEmptyText->addChild (new CText(translateFN('La tipologia non può essere vuota')));
		
		$iFrame = CDOMElement::create('iframe','id:jexResults,name:jexResults');
		$iFrame->setAttribute('style', 'background-color:#000');
		
		$add_btn = CDOMElement::create('button','id:nuova_fonte_btn');
		$add_btn->addChild(new CText(translateFN('Nuova Fonte')));
		$add_btn->setAttribute('style', 'display:none');
		$add_btn->setAttribute('class', 'dontuniform');
		$add_btn->setAttribute('onclick', 'javascript:addFonte();');
		
		$htmlObj->addChild($addTypologyEmptyText);
		$htmlObj->addChild($title);
		$htmlObj->addChild(new CText($form->getHtml()));
		$htmlObj->addChild($iFrame);
		$htmlObj->addChild($add_btn);
		
		return $htmlObj;
	}
} // class ends here