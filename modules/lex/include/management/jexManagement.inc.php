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
	private $_id_fonte;
	
	/**
	 * runs the import
	 *
	 * @see lexManagement::run()
	 */
	public function save() {
		
		$this->_mustValidate = false;
		
		// make the module's own log dir if it's needed
		if (!is_dir(MODULES_LEX_LOGDIR)) mkdir (MODULES_LEX_LOGDIR, 0777, true);
		// set the log file name
		$this->_logFile = MODULES_LEX_LOGDIR . "jex-import_".date('d-m-Y_His').".log";
		
		/**
		 * save into table fonti first
		 */
		$form = new FormJexImport('jex', null, $this->_dh->getTypologies());
		$form->fillWithPostData();
		if ($form->isValid()) {
			$fonteAr = array(
				'numero'=> trim($_POST['numero_fonte']),
				'titolo'=> trim($_POST['titolo_fonte']),
				'data_pubblicazione'=> dt2tsFN($_POST['data_pubblicazione']),
				'module_lex_tipologie_fonti_id'=> intval($_POST['tipologia'])
			);
			$result = $this->_dh->fonti_set(intval($_POST['id_fonte']), $fonteAr);
			
			if (!AMA_DB::isError($result)) {
				$this->_id_fonte = $result;
				$this->_logMessage(translateFN('Fonte salvata correttamente').' id='.$this->_id_fonte);
				// fonte saved ok, now run the import on the zip file
				parent::run();
			} else {
				$this->_logMessage('**'.translateFN('Problema nel salvataggio della fonte').'**');
				$this->_logMessage('**'.print_r($result, true).'**');
			}			
		}
		
	}
	
	/**
	 * This will read the attributes of the root node and
	 * call the appropriate method to import the tableName
	 *
	 * @param DOMElement $XMLObj
	 * @param unknown $tablename
	 *
	 * @access protected
	 */
	protected function _importXMLRoot ($XMLObj, $tableName) {
		
		$documents = $XMLObj->getElementsByTagName('document');
		$savedAssetCount = 0;
		
		foreach ($documents as $document) {
			$fileName = basename(preg_replace('/(\\\\(\S))/', "/$2", $document->getAttribute('id')));			
			if (is_file($this->_destDir . DIRECTORY_SEPARATOR . $fileName)) {
				$this->_logMessage(translateFN('Sto salvando').' '.$fileName.'...');
				$testoHa['testo'] = file_get_contents($this->_destDir . DIRECTORY_SEPARATOR . $fileName);
				$savedTestoHa = $this->_dh->testi_set ($testoHa);
				
				if (!AMA_DB::isError($savedTestoHa)) {
					$this->_logMessage('['.translateFN('OK').']');
					$savedAssetCount++;
				} else {
					$this->_logMessage('**'.translateFN('Errore').'**');
					$this->_logMessage('**'.print_r($savedTestoHa, true).'**');
				}
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
	 */
    public static function getTabTitle() {
    	return translateFN('Importa File JEX');
    	
    }
    
    /**
     * gets the HTML form to be rendered as the UI tab contents
     *
     * @return CDOMElement
     */
	public static function getImportForm() {
		
		$dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
		
		$typologiesArr = $dh->getTypologies();
		if (AMA_DB::isError($typologiesArr)) $typologiesArr = array();
		
		$htmlObj = CDOMElement::create('div','id:jexContainer');
		
		$title = CDOMElement::create('span', 'class:importTitle');
		$title->addChild (new CText(translateFN('Importa da JEX')));
		
		$form = new FormJexImport('jex', MODULES_LEX_HTTP. '/doImportJex.php', $typologiesArr);
		
		$addTypologyEmptyText = CDOMElement::create('span','id:addTypologyEmptyText');
		$addTypologyEmptyText->setAttribute('style', 'display:none');
		$addTypologyEmptyText->addChild (new CText(translateFN('La tipologia non può essere vuota')));
		
		$htmlObj->addChild($addTypologyEmptyText);
		$htmlObj->addChild($title);
		$htmlObj->addChild(new CText($form->getHtml()));
		
		$iFrame = CDOMElement::create('iframe','id:jexResults,name:jexResults');
		$iFrame->setAttribute('style', 'background-color:#000');
		$htmlObj->addChild ($iFrame);
		return $htmlObj;
	}
} // class ends here