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
	 * eurovoc tables prefix inside the module
	 * @var string
	 */
	private static $_SUBPREFIX = 'EUROVOC';
	
	/**
	 * runs the import
	 * 
	 * @see lexManagement::run()
	 */
	public function save() {
		$this->_mustValidate = true;
		$this->_setLogFileName("eurovoc-import_".date('d-m-Y_His').".log");
		
		parent::run();
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
    			$toSaveha[] = $this->{$method}($record, $lng, $version);
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
    	 
    	foreach ($record->childNodes as $node) {
    		switch (strtoupper($node->nodeName)) {
    			case 'DESCRIPTEUR_ID':
    				$record_ha['descripteur_id'] = $node->nodeValue;
    				break;
    			case 'UF_EL':
    				$record_ha['uf_el'] = $node->nodeValue;
    				if ($node->hasAttribute('FORM')) {
    					$record_ha['uf_el_form'] = $node->getAttribute('FORM');
    				} else {
    					$record_ha['uf_el_form'] = null;
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
    	
    	return $record_ha;
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
    	 
    	return $record_ha;
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
    	
    	return $record_ha;
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
    	 
    	return $record_ha;
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
    	
    	return $record_ha;
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
    	 
    	return $record_ha;
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
    	
    	return $record_ha;
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
    	 
    	return $record_ha;
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
    	
    	return $record_ha;
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
    	
    	return $record_ha;
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