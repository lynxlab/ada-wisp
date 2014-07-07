<?php
/**
 * Eurovoc Management Class for lex module
 *
 * @package 	lex
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	    0.1
 */

/**
 * class for managing Eurovoc
 *
 * @author giorgio
 */

require_once MODULES_LEX_PATH. '/include/management/abstractImportManagement.inc.php';
require_once MODULES_LEX_PATH. '/include/form/formUploadFile.php';

class eurovocManagement extends importManagement
{
	/**
	 * the language to use
	 * @var string
	 */
	private $_language;
	
	/**
	 * eurovoc tables prefix inside the module
	 * @var string
	 */
	private static $_SUBPREFIX = 'EUROVOC';
	
	public function __construct($language='en') {
		parent::__construct();
		$this->_language = (isset($language) && strlen($language)>0) ? $language : 'en';
	}
	
	/**
	 * runs the import
	 * 
	 * @see lexManagement::run()
	 */
	public function save() {
		$this->_mustValidate = true;
		$this->_setLogFileName("eurovoc-import_".date('d-m-Y_His').".log");
		
		ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes
		set_time_limit(0);
		
		parent::run();
		$this->_buildImportedJSONCache();
		$this->_logMessage(translateFN('Importazione EUROVOC terminata'));
	}
	
	/**
	 * gets the eurovoc tree as an array of objects, one per each domaine
	 * first tries to get the json object from the cache table and if it's
	 * not found or is invalid, run the code to generate the domaine subtree
	 * and stores it in the cache table
	 * 
	 * @return NULL|array
	 * 
	 * @access public
	 */
	
	public function getEurovocTree () {
		$treeObj = null;
				
		$domaines = $this->_dh->getEurovocDOMAINES($this->_language,EUROVOC_VERSION);
		if (!AMA_DB::isError($domaines)) {
			foreach ($domaines as $count=>$domaine) {
				// instantiate new empty object
				$treeObj[$count] = new stdClass();
				
				$cachedObj = $this->_dh->getEurovocDOMAINECache($domaine->domaine_id,$this->_language,EUROVOC_VERSION);				
				$cacheAccepted = false;
				
				if (!is_null($cachedObj) && !AMA_DB::isError($cachedObj)) {
					// try to json_decode cached object
					$treeObj[$count] = json_decode($cachedObj);					
					// if cached object is not valid json, run all the code
					$cacheAccepted = (json_last_error() == JSON_ERROR_NONE);
				}
					
				if (!$cacheAccepted) {
					// domaine tree is not cached, must run all the code
					$treeObj[$count]->key = $domaine->domaine_id;
					$treeObj[$count]->title = $domaine->libelle;
					$treeObj[$count]->folder= true;
					$treeObj[$count]->hideCheckbox = true;
					$treeObj[$count]->unselectable = true;
					
					$thesaurusTree = $this->getThesaurusTree($domaine->domaine_id);
					if (!is_null($thesaurusTree)) $treeObj[$count]->children = $thesaurusTree;
					
					$this->_dh->setEurovocDOMAINECache($treeObj[$count],$this->_language,EUROVOC_VERSION);
				}
			}
		} // if (!AMA_DB::isError($domaines))
		return $treeObj;
	}
	
	/**
	 * gets the thesaurus subtree of a domain
	 * 
	 * @param $domaine_id
	 * 
	 * @return NULL|stdClass
	 * 
	 * @access private
	 */
	private function getThesaurusTree ($domaine_id) {
		$treeObj = null;
				
		$thesauri = $this->_dh->getEurovocTHESAURUS($domaine_id, $this->_language, EUROVOC_VERSION);		
		if (!AMA_DB::isError($thesauri)) {
			foreach ($thesauri as $count=>$thesaurus) {
				$treeObj[$count] = new stdClass();
				$treeObj[$count]->key = $thesaurus->thesaurus_id;
				$treeObj[$count]->title = $thesaurus->libelle;
				$treeObj[$count]->folder= true;
				$treeObj[$count]->hideCheckbox = true;
				$treeObj[$count]->unselectable = true;
				
				$topTermsTree = $this->getTopTermsTree($thesaurus->thesaurus_id);
				if (!is_null($topTermsTree)) $treeObj[$count]->children = $topTermsTree;
			}			
		} // if (!AMA_DB::isError($domaines))
		return $treeObj;
	}
	
	/**
	 * gets the topterm subtree of a thesaurus term
	 * 
	 * @param $thesaurus_id
	 * 
	 * @return NULL|stdClass
	 * 
	 * @access private
	 */
	private function getTopTermsTree ($thesaurus_id) {
		$treeObj = null;
		
		$topTerms = $this->_dh->getEurovocTOPTERMS($thesaurus_id, $this->_language, EUROVOC_VERSION);
		if (!AMA_DB::isError($topTerms)) {
			foreach ($topTerms as $count=>$topTerm) {
				$treeObj[$count] = new stdClass();
				$treeObj[$count]->key = $topTerm->descripteur_id;
				$treeObj[$count]->title = $topTerm->libelle;
				$treeObj[$count]->folder= false;
				$treeObj[$count]->hideCheckbox = false;
				$treeObj[$count]->unselectable = false;
				
				$descripteurTree = $this->getDescripteurTree($topTerm->descripteur_id);
				
				if (!is_null($descripteurTree)) {
					$treeObj[$count]->folder = true;
					$treeObj[$count]->children = $descripteurTree;
				}
			}
		}
		return $treeObj;
	}
	
	/**
	 * recursively gets the descripteur tree of a descripteur
	 * 
	 * @param $descripteur_id
	 * 
	 * @return NULL|stdClass
	 * 
	 * @access private
	 */
	private function getDescripteurTree($descripteur_id) {
		$treeObj = null;
		
		$terms = $this->_dh->getEurovocDESCRIPTEURTERMS($descripteur_id, $this->_language, EUROVOC_VERSION);
		
		if (!AMA_DB::isError($terms)) {
			
			foreach ($terms as $count=>$term) {
				$treeObj[$count] = new stdClass();
				$treeObj[$count]->key = $term->descripteur_id;
				$treeObj[$count]->title = $term->libelle;
				$treeObj[$count]->folder= false;
				$treeObj[$count]->hideCheckbox = false;
				$treeObj[$count]->unselectable = false;
				
				$descripteurTree = $this->getDescripteurTree($term->descripteur_id);
				
				if (!is_null($descripteurTree)) {
					$treeObj[$count]->folder = true;
					$treeObj[$count]->children = $descripteurTree;
				}
			}
		}
		return $treeObj;
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
    	
    	$lng = $XMLObj->getAttribute('LNG');
    	$version = $XMLObj->getAttribute('VERSION');
    	
    	$records = $XMLObj->getElementsByTagName('RECORD');
    	
    	$toSaveha = array();
    	
    	foreach ($records as $record) {
    		$method = '_import'.$tableName;
    		if (method_exists($this, $method)) {
    			$tmpToSaveha = $this->{$method}($record, $lng, $version);
    			
    			if (count($tmpToSaveha)>1) {
    				foreach ($tmpToSaveha as $el) $toSaveha[] = $el;
    			} else {
    				$toSaveha[] = reset($tmpToSaveha);
    			}
    			
    		} else {
    			$this->_logMessage('**'.translateFN('Errore').': '.translateFN('Non posso importare in').' '.$tableName.'**');
    			break;
    		}
    	}
    	
    	if (!empty($toSaveha)) {
	    	$this->_logMessage(sprintf(translateFN('Sto salvando %d record...'),count($toSaveha)));
	    	
	    	$result = $this->_dh->insertMultiRow ($toSaveha, $tableName, self::$_SUBPREFIX);
	    	
	    	if (AMA_DB::isError($result)) {
	    		$this->_logMessage('**'.translateFN('Errore').'**');
	    		$this->_logMessage('**'.print_r($result, true).'**');
	    	} else {
	    		$this->_logMessage('['.translateFN('OK').']');
	    	}
    	}
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the 
     * uf_*.xml/used_for.dtd files in the proper array for the USED_FOR table
     * 
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */
    private function _importUSED_FOR  ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	$count=0; 
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'DESCRIPTEUR_ID':
    				$desc_id = $node->nodeValue;
    				$record_ha[$count]['descripteur_id'] = $desc_id;
    				break;
    			case 'UF':
    				if ($node->hasChildNodes()) {
    					foreach ($node->childNodes as $uf_el) {
    						
    						if (!isset($record_ha[$count]['descripteur_id'])) $record_ha[$count]['descripteur_id'] = $desc_id;
    						 
    						$record_ha[$count]['uf_el'] = $uf_el->nodeValue;
    						if ($uf_el->hasAttribute('FORM')) {
    							$record_ha[$count]['uf_el_form'] = $uf_el->getAttribute('FORM');
    						} else {
    							$record_ha[$count]['uf_el_form'] = null;
    						}
    						
    						if ($uf_el->hasAttribute('DEF')) {
    							$record_ha[$count]['def'] = $uf_el->getAttribute('DEF');
    						} else {
    							$record_ha[$count]['def'] = null;
    						}
    						
    						if (!is_null($lng) && strlen($lng)>0) $record_ha[$count]['lng'] = $lng;
    						if (!is_null($version) && strlen($version)>0) $record_ha[$count]['version'] = doubleval($version);
    						
    						$count++;    						
    					}
    				}
    				break;
    		}
    	}
    	 
    	return $record_ha;
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * thes_*.xml/thesaurus.dtd files in the proper array for the THESAURUS table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */    
    private function _importTHESAURUS ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'THESAURUS_ID':
    				$record_ha['thesaurus_id'] = $node->nodeValue;
    				break;
    			case 'LIBELLE':
    				$record_ha['libelle'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!is_null($lng) && strlen($lng)>0) $record_ha['lng'] = $lng;
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * sn_*.xml/scope_note.dtd files in the proper array for the SCOPE_NOTE table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */    
    private function _importSCOPE_NOTE ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	 
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'DESCRIPTEUR_ID':
    				$record_ha['descripteur_id'] = $node->nodeValue;
    				break;
    			case 'SN':
    				$record_ha['scope_note'] = $node->nodeValue;
    				break;
    			case 'HN':
    				$record_ha['history_note'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!isset($record_ha['scope_note'])) $record_ha['scope_note'] = null;
    	if (!isset($record_ha['history_note'])) $record_ha['history_note'] = null;
    	if (!is_null($lng) && strlen($lng)>0) $record_ha['lng'] = $lng;
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	 
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * relation_ui.xml/relation_ui.dtd files in the proper array for the RELATIONS_UI table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */    
    private function _importRELATIONS_UI ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'SOURCE_ID':
    				$record_ha['source_id'] = $node->nodeValue;
    				break;
    			case 'CIBLE_ID':
    				$record_ha['cible_id'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * relation_rt.xml/relation_rt.dtd files in the proper array for the RELATIONS_RT table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */    
    private function _importRELATIONS_RT ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	 
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'DESCRIPTEUR1_ID':
    				$record_ha['descripteur1_id'] = $node->nodeValue;
    				break;
    			case 'DESCRIPTEUR2_ID':
    				$record_ha['descripteur2_id'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	 
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * relation_bt.xml/relation_bt.dtd files in the proper array for the RELATIONS_BT table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */    
    private function _importRELATIONS_BT ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'SOURCE_ID':
    				$record_ha['source_id'] = $node->nodeValue;
    				break;
    			case 'CIBLE_ID':
    				$record_ha['cible_id'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	
    	return array($record_ha);
    }

    /**
     * maps an item contained in the <RECORD> tags of the
     * language.xml/langue.dtd files in the proper array for the LANGUES table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */
    private function _importLANGUES ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	 
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'LIBELLE':
    				$record_ha['libelle'] = $node->nodeValue;
    				break;
    			case 'COURTE':
    				$record_ha['courte'] = $node->nodeValue;
    				break;
    			case 'TRI':
    				$record_ha['tri'] = $node->nodeValue;
    				break;
    		}
    		
    	}
    	
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	 
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * dom_*.xml/domaine.dtd files in the proper array for the DOMAINES table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */
    private function _importDOMAINES ($record=null, $lng=null, $version=null) {
    	$record_ha = array();
    	
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'DOMAINE_ID':
    				$record_ha['domaine_id'] = $node->nodeValue;
    				break;
    			case 'LIBELLE':
    				$record_ha['libelle'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!is_null($lng) && strlen($lng)>0) $record_ha['lng'] = $lng;
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * desc_*.xml/descripteur_thesaurus.dtd files in the proper array for the DESCRIPTEUR_THESAURUS table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
     * @access private
     */
    private function _importDESCRIPTEUR_THESAURUS ($record=null, $lng=null, $version=null) {

    	$record_ha = array();
    	 
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'THESAURUS_ID':
    				$record_ha['thesaurus_id'] = $node->nodeValue;
    				break;
    			case 'DESCRIPTEUR_ID':
    				$record_ha['descripteur_id'] = $node->nodeValue;
    				if ($node->hasAttribute('COUNTRY')) {
    					$record_ha['country'] = $node->getAttribute('COUNTRY');
    				} else {
    					$record_ha['country'] = null;
    				}
    				
    				if ($node->hasAttribute('ISO_COUNTRY_CODE')) {
    					$record_ha['iso_country_code'] = $node->getAttribute('ISO_COUNTRY_CODE');
    				} else {
    					$record_ha['iso_country_code'] = null;
    				}
    				break;
    			case 'TOPTERM':
    				$record_ha['topterm'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	 
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * desc_*.xml/descripteur.dtd files in the proper array for the DESCRIPTEUR table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
	 * @access private 
     */
    private function _importDESCRIPTEUR ($record=null, $lng=null, $version=null) {
    	
    	$record_ha = array();
    	
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'DESCRIPTEUR_ID':
    				$record_ha['descripteur_id'] = $node->nodeValue;
    				break;
    			case 'LIBELLE':
    				$record_ha['libelle'] = $node->nodeValue;
    				if ($node->hasAttribute('FORM')) {
    					$record_ha['libelle_form'] = $node->getAttribute('FORM');
    				} else {
    					$record_ha['libelle_form'] = null;
    				}
    				break;
    			case 'DEF':
    				$record_ha['def'] = $node->nodeValue;
    				break;
    		}
    	}
    	
    	if (!isset($record_ha['def'])) $record_ha['def'] = null;
    	if (!is_null($lng) && strlen($lng)>0) $record_ha['lng'] = $lng;
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	
    	return array($record_ha);
    }
    
    /**
     * maps an item contained in the <RECORD> tags of the
     * compnpt_*.xml/compound_non_pt.dtd files in the proper array for the COMPOUND_NON_PT table
     *
     * @param array $record
     * @param string $lng
     * @param string $version
     * @return array
     * 
	 * @access private
     */
    private function _importCOMPOUND_NON_PT ($record=null, $lng=null, $version=null) {
    	/**
    	 * record does not have any attributes, let's build
    	 * the array of records to be saved
    	 */
    	$record_ha = array();
    	
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'UF_EL':
    				$record_ha['uf_el'] = $node->nodeValue;
    				break;
    			case 'USE':
    				if ($node->hasChildNodes()) {
    					for ($i=0; $i < $node->childNodes->length; $i++)
    						$record_ha['use_descripteur_id_'.($i+1)] = intval($node->childNodes->item($i)->nodeValue);
    				}    				
    				break;
    		}
    	}
    	
    	if (!is_null($lng) && strlen($lng)>0) $record_ha['lng'] = $lng;
    	if (!is_null($version) && strlen($version)>0) $record_ha['version'] = doubleval($version);
    	
    	return array($record_ha);
    }
    
    /**
     * build the cache table contents to speed up tree generation
     * 
     * @access private
     */
    private function _buildImportedJSONCache() {
    	
    	$langArr = $this->_dh->getSupportedLanguages();
    	
    	if (!AMA_DB::isError($langArr)) {
    		
    		$this->_logMessage(translateFN('Generazione degli oggetti in cache'));
    		
    		foreach ($langArr as $lang) {
    			$this->_language = $lang[0];
    			$this->_logMessage(translateFN('codice lingua').': '.$this->_language.' ...');
    			$this->getEurovocTree();
    			$this->_logMessage('['.translateFN('OK').']');
    		}
    		
    		$this->_logMessage(translateFN('Generazione degli oggetti in cache completata'));
    	}
    }
    
    
    /**
     * gets the label to be used in the UI tab
     * 
     * @return string
     */
    public static function getTabTitle() {
    	return translateFN('Importa XML Eurovoc');
    }
    
    /**
     * gets the HTML form to be rendered as the UI tab contents
     * 
     * @return CDOMElement
     */
	public static function getImportForm() {
		$htmlObj = CDOMElement::create('div','id:eurovocContainer');
		
		$title = CDOMElement::create('span', 'class:importTitle');
		$title->addChild (new CText(translateFN('Importa da EUROVOC')));
		
		$form = new FormUploadImportFile('eurovoc', MODULES_LEX_HTTP. '/doImportEurovoc.php' );
		
		$iFrame = CDOMElement::create('iframe','id:eurovocResults,name:eurovocResults');
		$iFrame->setAttribute('style', 'background-color:#000');

		$htmlObj->addChild($title);
		$htmlObj->addChild(new CText($form->getHtml()));		
		$htmlObj->addChild($iFrame);
		
		return $htmlObj;
	}
} // class ends here