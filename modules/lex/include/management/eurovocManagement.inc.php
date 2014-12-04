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
require_once MODULES_LEX_PATH. '/include/form/formExportEurovoc.php';

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
	public static $_SUBPREFIX = 'EUROVOC';
	
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
	 * runs the export
	 * 
	 * @param string $exportLang 2 char language code to be exported or '*' for all langs
	 */
	public function export($exportLang) {
		$this->_setLogFileName("eurovoc-export_".date('d-m-Y_His').".log");

		ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes
		set_time_limit(0);
		
		if ($exportLang!=='*') $exportLangs = array($exportLang);
		else $exportLangs = $this->_dh->getSupportedLanguages();
		
		$tableFileArray = array(
				'COMPOUND_NON_PT' =>
					array ('xml' => 'compnpt_', 'dtd' => 'compound_non_pt'),
				'DESCRIPTEUR' => 
					array ('xml' => 'desc_', 'dtd' => 'descripteur'),
				'DESCRIPTEUR_THESAURUS' =>
					array ('xml' => 'desc_thes', 'dtd' => 'descripteur_thesaurus'),
				'DOMAINES' => 
					array ('xml' => 'dom_', 'dtd' => 'domaine'),
				'LANGUES' =>
					array ('xml' => 'langue', 'dtd' => 'langue'),
				'RELATIONS_BT' =>
					array ('xml' => 'relation_bt', 'dtd' => 'relation_bt'),
				'RELATIONS_RT' =>
					array ('xml' => 'relation_rt', 'dtd' => 'relation_rt'),
				'RELATIONS_UI' =>
					array ('xml' => 'relation_ui', 'dtd' => 'relation_ui'),
				'SCOPE_NOTE' =>
					array ('xml' => 'sn_', 'dtd' => 'scope_note'),
				'THESAURUS' =>
					array ('xml' => 'thes_', 'dtd' => 'thesaurus'),
				'USED_FOR' =>
					array ('xml' => 'uf_', 'dtd' => 'used_for'),
				'USERDEFINED_DESCRIPTEUR' => 
					array ('xml' => 'userdefined_desc_', 'dtd' => 'descripteur'),
				'USERDEFINED_RELATIONS_BT' =>
					array ('xml' => 'userdefined_relation_bt', 'dtd' => 'relation_bt'),
		);
		
		$exportDir = ADA_UPLOAD_PATH . $_SESSION['sess_userObj']->getId() . DIRECTORY_SEPARATOR .'eurovoc';
		if (!is_dir($exportDir) && !mkdir($exportDir)) {
			// dir is not there and cannot be created, abort
			$this->_logMessage('Export dir '.$exportDir.' does not exists and cannot be created, export aborted.', false);
		} else {
			$this->_logMessage('**** EXPORT STARTED at '.date('d/m/Y H:i:s'). '(timestamp: '.$this->_dh->date_to_ts('now').') ****', false);
			
			foreach (array_values($exportLangs) as $count=>$language) {
				foreach ($tableFileArray as $tableName=>$elementData) {
					if (substr($elementData['xml'],-1)==='_') {
						// if filename ends with _ it's a language xml file
						$XMLFilename = $elementData['xml'].$language;
						$doExport = true;
					} else {
						$XMLFilename = $elementData['xml'];
						$doExport = ($count==0);
					}
					
					if ($doExport) {						
						$XMLFilename .= '.xml';						
						$DTDFilename = $elementData['dtd'].'.dtd';

						$this->_logMessage('Exporting '.$tableName.' into '.$XMLFilename, false);
						
						$fp = fopen ($exportDir . DIRECTORY_SEPARATOR .$XMLFilename , 'w');
						$writtenElements = $this->_writeExport($fp, $tableName, $DTDFilename, $language);
						fclose ($fp);
						
						$this->_logMessage('DONE',false);
						
						if ($writtenElements<=0) {
							$this->_logMessage($XMLFilename.' has 0 records, deleting it', false);
							unlink ($exportDir . DIRECTORY_SEPARATOR .$XMLFilename);
						}
						else {
							// copy DTDs to exportDir
							@copy (MODULES_LEX_PATH . DIRECTORY_SEPARATOR .'exportDTD'.
									DIRECTORY_SEPARATOR . $DTDFilename, $exportDir . DIRECTORY_SEPARATOR .$DTDFilename );
						}						
					}
				}
			}
			
			// make the zipfile
			$zip = new ZipArchive();
			$ret = $zip->open($exportDir . DIRECTORY_SEPARATOR . EXPORT_FILENAME, ZipArchive::OVERWRITE);			
			if ($ret !== TRUE) {
				$this->_logMessage('ZIP creation failed, import aborted.',false);
			} else {
				$this->_logMessage('ZIP creation:',false);
				$files = glob( $exportDir . DIRECTORY_SEPARATOR .'*.{dtd,xml}', GLOB_BRACE);
				// first add the files, then unlink them
				foreach (array ('add','delete') as $op) {
					foreach ($files as $file) {
						if ($op==='add') {
							$this->_logMessage('ZIP adding: '.$file,false);
							$zip->addFile($file,substr($file,strrpos($file,'/') + 1));
						} else if ($op==='delete') {
							$this->_logMessage('DELETE: '.$file,false);
							unlink ($file);
						}
					}
					if ($op==='add') $zip->close();
				}    			
			}
			
			$this->_logMessage('**** EXPORT ENDED   at '.date('d/m/Y H:i:s'). '(timestamp: '.$this->_dh->date_to_ts('now').') ****', false);
			
			if (is_file($exportDir.DIRECTORY_SEPARATOR.EXPORT_FILENAME)) return $exportDir.DIRECTORY_SEPARATOR.EXPORT_FILENAME;
			else return false; 
		} 
	}
	
	/**
	 * gets the eurovoc tree as an array of objects, one per each domaine
	 * first tries to get the json object from the cache table and if it's
	 * not found or is invalid, run the code to generate the domaine subtree
	 * and stores it in the cache table
	 * 
	 * @param array $rebuildCache array of domaine_id to force cache rebuild
	 * 
	 * @return NULL|array
	 * 
	 * @access public
	 */
	
	public function getEurovocTree ($rebuildCache=array()) {
		$treeObj = null;
		
		$rebuildOnly = (count($rebuildCache)>0);
				
		$domaines = $this->_dh->getEurovocDOMAINES($this->_language,EUROVOC_VERSION);
		if (!AMA_DB::isError($domaines)) {
			foreach ($domaines as $count=>$domaine) {
				// instantiate new empty object
				$treeObj[$count] = new stdClass();
				$cacheAccepted = false;
				$cachedObj = null;
				$mustRebuildCache = in_array($domaine->domaine_id,$rebuildCache);
				
				/**
				 * read the cache only if the current domain is
				 * not in the array of the rebuild cache ids
				 */
				if (!$mustRebuildCache) {
					$cachedObj = $this->_dh->getEurovocDOMAINECache($domaine->domaine_id,$this->_language,EUROVOC_VERSION);					
				}
				
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
					
					if (!$rebuildOnly || ($rebuildOnly && $mustRebuildCache)) {
						
						$thesaurusTree = $this->getThesaurusTree($domaine->domaine_id);
						if (!is_null($thesaurusTree)) $treeObj[$count]->children = $thesaurusTree;
						$this->_dh->setEurovocDOMAINECache($treeObj[$count],$this->_language,EUROVOC_VERSION);
					}
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
				$treeObj[$count]->data['isUserDefined'] = (bool) $term->is_user_defined;
				$treeObj[$count]->data['isNew'] = false;
				
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
     * @param string $tablename
     * @param isUserDefined true if importing user defined data, only used in eurovoc class
     * 
     * @return number total count of imported items or -1 on aborted import
     * 
     * @access protected
     */
    protected function _importXMLRoot ($XMLObj, $tableName, $isUserDefined = false) {
    	
    	$lng = $XMLObj->getAttribute('LNG');
    	$version = $XMLObj->getAttribute('VERSION');
    	
    	$records = $XMLObj->getElementsByTagName('RECORD');
    	
    	$toSaveha = array();
    	
    	foreach ($records as $record) {
    		$method = '_import'.$tableName;
    		if (method_exists($this, $method)) {
    			if ($isUserDefined) $record->is_user_defined = 1;
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
	    		$msg = ($result instanceof PDOException) ? $result->getMessage() : print_r($result, true); 	    		
	    		$this->_logMessage('**'.$msg.'**');
	    		// return error
	    		return -1;
	    	} else {
	    		$this->_logMessage('['.translateFN('OK').']');
	    	}
    	}
    	
    	return count($toSaveha);
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
    	/**
    	 * it looks like the EuroVoc uf_*.xml files may have some duplicate rows, here's
    	 * an example from the italian file uf_it.xml:
    	 * <RECORD>
    	 * 	<DESCRIPTEUR_ID>3667</DESCRIPTEUR_ID>
    	 * 		<UF>
    	 * 			<UF_EL>sperimentazione animale</UF_EL>
    	 * 			<UF_EL>allevamento di cavie</UF_EL>
    	 * 			<UF_EL>allevamento di cavie</UF_EL> <!-- this is a duplicate -->
    	 * 			<UF_EL>cavia</UF_EL>
    	 * 			<UF_EL>animale da laboratorio</UF_EL>
    	 * 			<UF_EL>esperimento su cavie</UF_EL>
    	 * 		</UF>
    	 * </RECORD>
    	 * 
    	 * but in the online version at http://eurovoc.europa.eu/3667&language=it
    	 * the UF section has 5 items, so it really should be a bug in the Eurovoc XML
    	 * 
    	 * the $preventDuplicate array stores each uf_el value for the current record
    	 * and prevents it to be added to the saved array ($record_ha)
    	 */
    	$record_ha = array();
    	$preventDuplicate = array();
    	
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
    						// prevents duplicate rows
    						if (!in_array($uf_el->nodeValue, $preventDuplicate)) {
    							$preventDuplicate[] = $uf_el->nodeValue;
    						
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
    	
    	if (isset($record->is_user_defined)) $record_ha['is_user_defined'] = 1;
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
    			$this->_language = $lang;
    			$this->_logMessage(translateFN('codice lingua').': '.$this->_language.' ...');
    			$this->getEurovocTree();
    			$this->_logMessage('['.translateFN('OK').']');
    		}
    		
    		$this->_logMessage(translateFN('Generazione degli oggetti in cache completata'));
    	}
    }
    
    /**
     * writes XML into passed file handle
     * 
     * @param resource $fp the opened file to write to
     * @param string $tableName the table name to use
     * @param string $DTDFilename the DTD filename to use
     * @param string $lng
     * @param string $version
     * 
     * @access private
     */
    private function _writeExport ($fp, $tableName, $DTDFilename ,$lng, $version=EUROVOC_VERSION) {
    	$elementCount = 0;
    	$isUserDefined = (strpos($tableName, 'USERDEFINED')!==false);    	 
    	if ($isUserDefined)  {
    		// if it's the user defined descripteur
    		$tableName = str_replace('USERDEFINED_', '', $tableName);
    	}
    	    	 
    	$string = '<?xml version="1.0" encoding ="'.ADA_CHARSET.'"?>' . PHP_EOL .
				  '<!DOCTYPE '.$tableName.' SYSTEM "'.$DTDFilename.'">';
    	
    	fwrite ($fp, $string);
    	
    	$noLangAttr = array ('DESCRIPTEUR_THESAURUS', 'LANGUES', 'RELATIONS_BT', 'RELATIONS_RT', 'RELATIONS_UI');
    	
    	if (!in_array($tableName, $noLangAttr)) {
    		fwrite ($fp, sprintf ('<%s VERSION="%.2f" LNG="%2s">',$tableName, $version, $lng));
    	} else {
    		fwrite ($fp, sprintf ('<%s VERSION="%.2f">',$tableName, $version));
    	}
    	
    	$method = '_writeExport'.$tableName;
    	
    	if (method_exists($this, $method)) {
    		if (!in_array($tableName, $noLangAttr)) {	    		
    			$res = $this->_dh->getEurovocRawTable(self::$_SUBPREFIX.'_'.$tableName, $lng, $version, $isUserDefined);
    		} else {
    			$res = $this->_dh->getEurovocRawTable(self::$_SUBPREFIX.'_'.$tableName, null, $version, $isUserDefined);
    		}    		 
    		if (!AMA_DB::isError($res) && count($res)>0) {    		
    			$elementCount = $this->$method($fp, $res);
    		}    		
    	}
    	fwrite ($fp, '</'.$tableName.'>');
    	
    	return $elementCount;
    }
    
    /**
     * writes COMPOUND_NON_PT records to xml file
     * 
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     * 
     * @return number count of written records
     * 
     * @access private
     */
    private function _writeExportCOMPOUND_NON_PT ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, sprintf('<RECORD><UF_EL>%s</UF_EL><USE><DESCRIPTEUR_ID>%s</DESCRIPTEUR_ID><DESCRIPTEUR_ID>%s</DESCRIPTEUR_ID></USE></RECORD>',
    		$element['uf_el'],
    		$element['use_descripteur_id_1'],
    		$element['use_descripteur_id_2']));
    	}
    	return count($res);
    }
    
    /**
     * writes DESCRIPTEUR records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */    
    private function _writeExportDESCRIPTEUR ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<DESCRIPTEUR_ID>%s</DESCRIPTEUR_ID>',$element['descripteur_id']));
    		fwrite ($fp, '<LIBELLE');
    		if (strlen($element['libelle_form'])>0) fwrite ($fp, sprintf(' FORM="%s"',$element['libelle_form'])); 
    		fwrite ($fp, sprintf('>%s</LIBELLE>',$element['libelle']));
    		if (strlen($element['def'])>0) fwrite ($fp, sprintf('<DEF>%s</DEF>',$element['def'])); 
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
    
    /**
     * writes DESCRIPTEUR_THESAURUS records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */    
    private function _writeExportDESCRIPTEUR_THESAURUS ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<THESAURUS_ID>%s</THESAURUS_ID>',$element['thesaurus_id']));
    		fwrite ($fp, '<DESCRIPTEUR_ID');
    		if (strlen($element['country'])>0) fwrite ($fp, sprintf(' COUNTRY="%s"',$element['country']));
    		if (strlen($element['iso_country_code'])>0) fwrite ($fp, sprintf(' ISO_COUNTRY_CODE="%s"',$element['iso_country_code']));
    		fwrite ($fp, sprintf('>%s</DESCRIPTEUR_ID>',$element['descripteur_id']));
    		fwrite ($fp, sprintf('<TOPTERM>%s</TOPTERM>',$element['topterm']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
    
    /**
     * writes DOMAINES records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportDOMAINES ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<DOMAINE_ID>%s</DOMAINE_ID>',$element['domaine_id']));
    		fwrite ($fp, sprintf('<LIBELLE>%s</LIBELLE>',$element['libelle']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }

    /**
     * writes LANGUES records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportLANGUES ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<LIBELLE>%s</LIBELLE>',$element['libelle']));
    		fwrite ($fp, sprintf('<COURTE>%s</COURTE>',$element['courte']));
    		fwrite ($fp, sprintf('<TRI>%s</TRI>',$element['tri']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
    
    /**
     * writes RELATIONS_BT records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportRELATIONS_BT ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<SOURCE_ID>%s</SOURCE_ID>',$element['source_id']));
    		fwrite ($fp, sprintf('<CIBLE_ID>%s</CIBLE_ID>',$element['cible_id']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
    
    /**
     * writes RELATIONS_RT records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */    
    private function _writeExportRELATIONS_RT ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<DESCRIPTEUR1_ID>%s</DESCRIPTEUR1_ID>',$element['descripteur1_id']));
    		fwrite ($fp, sprintf('<DESCRIPTEUR2_ID>%s</DESCRIPTEUR2_ID>',$element['descripteur2_id']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
    
    /**
     * writes RELATIONS_UI records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportRELATIONS_UI ($fp, $res) {
    	return $this->_writeExportRELATIONS_BT($fp, $res);
    }
    
    /**
     * writes SCOPE_NOTE records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportSCOPE_NOTE ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<DESCRIPTEUR_ID>%s</DESCRIPTEUR_ID>',$element['descripteur_id']));
    		if (strlen($element['scope_note'])>0) fwrite ($fp, sprintf('<SN>%s</SN>',$element['scope_note']));
    		if (strlen($element['history_note'])>0) fwrite ($fp, sprintf('<HN>%s</HN>',$element['history_note']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
    
    /**
     * writes THESAURUS records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportTHESAURUS ($fp, $res) {
    	foreach ($res as $element) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<THESAURUS_ID>%s</THESAURUS_ID>',$element['thesaurus_id']));
    		fwrite ($fp, sprintf('<LIBELLE>%s</LIBELLE>',$element['libelle']));
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($res);
    }
        
    /**
     * writes USED_FOR records to xml file
     *
     * @param unknown $fp the opened file to write to
     * @param unknown $res resultset to be written
     *
     * @return number count of written records
     *
     * @access private
     */
    private function _writeExportUSED_FOR ($fp, $res) {
    	
    	foreach ($res as $element) {
    		if (!isset($writeArr[$element['descripteur_id']]['elements'])) $writeArr[$element['descripteur_id']]['elements'] = array();
    		$writeArr[$element['descripteur_id']]['elements'][] = array ( 'uf_el'=>$element['uf_el'], 'uf_el_form'=>$element['uf_el_form'] );
    		if (strlen($element['def'])>0) {
    			$writeArr[$element['descripteur_id']]['def'] = $element['def'];
    		}
    	}
    	
    	foreach ($writeArr as $descripteur_id=>$writeElement) {
    		fwrite ($fp, '<RECORD>');
    		fwrite ($fp, sprintf('<DESCRIPTEUR_ID>%s</DESCRIPTEUR_ID>',$descripteur_id));
    		if (is_array($writeElement['elements']) && count($writeElement['elements'])>0) {
    			fwrite ($fp, '<UF>');
    			foreach ($writeElement['elements'] as $ufElement) {
    				fwrite($fp, '<UF_EL');
    				if (strlen($ufElement['uf_el_form'])>0) fwrite ($fp, sprintf(' FORM="%s"',$ufElement['uf_el_form']));
    				fwrite($fp, sprintf('>%s</UF_EL>', str_replace('&', '&amp;', $ufElement['uf_el'] )));    				
    			}
    			fwrite ($fp, '</UF>');
    		}
    		
    		if (strlen($writeElement['def'])>0) {
    			fwrite ($fp, sprintf('<DEF>%s</DEF>',$writeElement['def']));
    		}
    		fwrite ($fp, '</RECORD>');
    	}
    	return count($writeArr);
    }
                
    /**
     * gets the label to be used in the UI tab
     * 
     * @return string
     */
    public static function getTabTitle($actionCode) {
    	if ($actionCode===IMPEXPORT_EUROVOC)
    		return translateFN('Importa/Esporta Ontologia Eurovoc');
    	else if ($actionCode===EDIT_EUROVOC)
    		return translateFN('Modifica Termini');
    }
    
    /**
     * gets the HTML form to be rendered as the UI tab contents
     * for the import/export XML tab
     * 
     * @return CDOMElement
     */
	public static function getImpExportForm() {
		$htmlObj = CDOMElement::create('div','id:eurovocContainer');
		
		$title = CDOMElement::create('span', 'class:importTitle');
		$title->addChild (new CText(translateFN('Importa da EUROVOC')));
		
		$importForm = new FormUploadImportFile('eurovoc', MODULES_LEX_HTTP. '/doImportEurovoc.php' );
		
		$tmpManagement = new eurovocManagement();
		$supportedLangsArray = $tmpManagement->_dh->getSupportedLanguages();
		if (is_array($supportedLangsArray) && count ($supportedLangsArray)>0) {
			
			$exportForm = new FormExportEurovoc('exporteurovoc', $supportedLangsArray );
			
			$exportStartedMsgSpan = CDOMElement::create('span','id:exportStartedMsg');
			$exportStartedMsgSpan->setAttribute('style', 'display:none');
			$exportStartedMsgSpan->addChild (new CText(translateFN('Esportazione in corso, il download si avvierà automaticamente.')));
			
			$h2SepExport = CDOMElement::create('h2','class:impexportseparator');
			$h2SepExport->addChild(new CText('Esporta Ontologia in XML EUROVOC'));
			
			$resetBtnContainer = CDOMElement::create('div','id:resetEurovocBtnContainer');
			
			$resetButton = CDOMElement::create('button','id:resetEurovocBtn');
			$resetButton->setAttribute('onclick', 'javascript:doResetEurovoc(\'confirm\');');
			$resetButton->addChild (new CText(translateFN('Reset Albero EUROVOC')));
			
			$resetBtnContainer->addChild($resetButton);
			
			$h2SepReset = CDOMElement::create('h2','class:impexportseparator');
			$h2SepReset->addChild(new CText('Reset Ontologia EUROVOC'));
		}		
		
		$iFrame = CDOMElement::create('iframe','id:eurovocResults,name:eurovocResults');
		$iFrame->setAttribute('style', 'background-color:#000');

		$htmlObj->addChild($title);
		$htmlObj->addChild(new CText($importForm->getHtml()));
		$htmlObj->addChild($iFrame);
		
		if (isset($exportForm)) {
			$htmlObj->addChild($h2SepExport);
			$htmlObj->addChild(new CText($exportForm->getHtml()));
			$htmlObj->addChild($exportStartedMsgSpan);
		}
		
		if (isset($resetBtnContainer)) {
			$htmlObj->addChild($h2SepReset);
			$htmlObj->addChild($resetBtnContainer);
		}
		
		return $htmlObj;
	}
	
    /**
     * gets the HTML form to be rendered as the UI tab contents
     * for the edit terms tab
     * 
     * @return CDOMElement
     */
     public static function getEditPage() {
     	
     	$htmlObj = CDOMElement::create('div','id:eurovocEditContainer');
     	
     	$treeDIV = CDOMElement::create('div','id:editTermsContainer');
     	
     	/**
     	 * new node default title
     	 */
     	$defaultNewNode = CDOMElement::create('span','id:defaultNewNodeTitle');
     	$defaultNewNode->setAttribute('style', 'display:none');
     	$defaultNewNode->addChild(new CText(translateFN('Nuovo Nodo')));
     	$treeDIV->addChild($defaultNewNode);
     	
     	/**
     	 * non empty string in node text error message
     	 */
     	$nonEmptyMsg = CDOMElement::create('span','id:nonEmptyMsg');
     	$nonEmptyMsg->setAttribute('style', 'display:none');
     	$nonEmptyMsg->addChild(new CText(translateFN('Inserire una stringa non vuota')));
     	$treeDIV->addChild($nonEmptyMsg);
     	
     	/**
     	 * ajax node saving has failed message
     	 */
     	$nodeSavingFailMsg = CDOMElement::create('span','id:nodeSavingFailMsg');
     	$nodeSavingFailMsg->setAttribute('style', 'display:none');
     	$nodeSavingFailMsg->addChild(new CText(translateFN('Salvataggio del nodo fallito')));
     	$treeDIV->addChild($nodeSavingFailMsg);
     	
     	/**
     	 * div holding the dialog to display the associated
     	 * assets when user tries to delete a term
     	 */
     	$divCannotDelete = CDOMElement::create('div','id:cannot-delete');
     	$divCannotDelete->setAttribute('title', translateFN('Impossibile Cancellare'));
     	$divCannotDelete->setAttribute('style', 'display:none');
     	
     	$cannotDeleteMsg = CDOMElement::create('h3');
     	$cannotDeleteMsg->addChild(new CText(translateFN('I seguenti asset sono associati al termine da cancellare').':'));
     	
     	$divCannotDelete->addChild($cannotDeleteMsg);
     	$divCannotDelete->addChild(CDOMElement::create('div','id:cannot-delete-details'));
     	
     	$cannotDeleteQuestion = CDOMElement::create('span','class:cannot-delete-question');
     	$cannotDeleteQuestion->addChild (new CText(translateFN('Cliccando "Cancella" si cancellerà comunque il termine e tutte le sue associazioni con gli asset.')));
     	
     	$divCannotDelete->addChild($cannotDeleteQuestion);
     	     	
     	$treeDIV->addChild($divCannotDelete);
     	
     	/**
     	 * div holding the dialog to ask user confirmation before delete
     	 */
     	$divAskConfirm = CDOMElement::create('div','id:ask-confirm-delete');
     	$divAskConfirm->setAttribute('title', translateFN('Conferma cancellazione'));
     	$divAskConfirm->setAttribute('style', 'display:none');
     	
     	$divAskConfirm->addChild(CDOMElement::create('div','id:ask-confirm-message'));
     	
     	$treeDIV->addChild ($divAskConfirm);
     	
     	/**
     	 * ajax node deletion has failed message
     	 */
     	$nodeDelFailMsg = CDOMElement::create('span','id:nodeDelFailMsg');
     	$nodeDelFailMsg->setAttribute('style', 'display:none');
     	$nodeDelFailMsg->addChild(new CText(translateFN('Cancellazione del nodo fallita')));
     	$treeDIV->addChild($nodeDelFailMsg);
     	
     	/**
     	 * fancytree div, when the 'edit terms' tab is activated
     	 * the js shall load the tree inside this div if it's not been done already
     	 */
     	$fancyTree = CDOMElement::create('div','id:editTerms');
     	
     	/**
     	 * tree context menu, displayed on right click on a node
     	 */
     	 
     	$contextMenuOptions = array (
     			array (
     					'action' => 'new',
     					'label'  => translateFN('Nuovo Termine'),
     					'icon'   => 'ui-icon-plus' ),
     			array (
     					'action' => 'edit',
     					'label'  => translateFN('Rinomina Termine'),
     					'icon'   => 'ui-icon-pencil' ),
     			array (
     					'action' => 'delete',
     					'label'  => translateFN('Cancella Termine'),
     					'icon'   => 'ui-icon-minus' )
     	);
     	 
     	$contextMenuUL = CDOMElement::create('ul','id:treeContextMenu,class:ui-helper-hidden');
     	foreach ($contextMenuOptions as $contextMenuItem) {
     		$li = CDOMElement::create('li');
     		$li->setAttribute('data-command', $contextMenuItem['action']);
     		$a = CDOMElement::create('a');
     		$span = CDOMElement::create('span','class:ui-icon '.$contextMenuItem['icon']);
     		$a->addChild($span);
     		$a->addChild(new CText($contextMenuItem['label']));
     		$li->addChild($a);
     		$contextMenuUL->addChild($li);
     	}
     	 
     	$fancyTree->addChild($contextMenuUL);
     	
     	/**
     	 * container div for tree tools: filter text with reset filter button
     	 */
     	$toolsContainer = CDOMElement::create('div','class:treeTools');
     	     	
     	/**
     	 * filter container for label, input text and filter reset button
     	 */
     	$divFilter = CDOMElement::create('div','class:treeFilter');
     	/**
     	 * label
     	 */
     	$lblFilter = CDOMElement::create('label','for:treeFilterInput');
     	$lblFilter->addChild (new CText(translateFN('filtra').': '));
     	/**
     	 * input text
     	 */
     	$inputFilter = CDOMElement::create('text','id:treeFilterInput,class:dontuniform');
     	/**
     	 * filter reset button
     	 */
     	$resetFilter = CDOMElement::create('button','id:resetTreeFilter,class:dontuniform');
     	$resetFilter->addChild (new CText('&times;'));
     	 
     	$divFilter->addChild($lblFilter);
     	$divFilter->addChild($inputFilter);
     	$divFilter->addChild($resetFilter);
     	 
     	/**
     	 * add button and filter container to tree tools
     	 */
     	$toolsContainer->addChild($divFilter);
     	 
     	/**
     	 * add tools to tree container
     	 */
     	$treeDIV->addChild($toolsContainer);
     	/**
     	 * add fancytree to the tree container
     	 */     	
     	$treeDIV->addChild($fancyTree);
     	/**
     	 * add tree container to whole page
     	 */
     	$htmlObj->addChild($treeDIV);
     	
     	// div to fix firefox display
     	$htmlObj->addChild(CDOMElement::create('div','class:clearfix'));
     	return $htmlObj;
	 }
} // class ends here