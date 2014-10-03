<?php
/**
 * LEX MODULE.
 *
 * @package        lex module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           lex
 * @version		   0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMALexDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 *
	 * @var string
	 */
	public static $PREFIX = 'module_lex_';

	/**
	 * does a multi-row insert when importing the xml files
	 *
	 * @param array $valuesArray the array of values to be inserted
	 * @param string $tableName the table to insert into
	 * @param string $subPrefix if any, subprefix to use when building table name string
	 *
	 * @return Ambigous <mixed, boolean, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function insertMultiRow ($valuesArray=array(), $tableName=null, $subPrefix='') {

		if (is_array($valuesArray) && count($valuesArray)>0 && !is_null($tableName)) {

			// 0. init the query
			if (strlen($subPrefix)>0) $tableName = $subPrefix.'_'.$tableName;

			$sql = 'INSERT INTO `'.self::$PREFIX.$tableName.'` ';
			// 1. get the keys of the passed array
			$fields = array_keys(reset($valuesArray));
			// 2. build the placeholders string
			$flCount = count($fields);
			$lCount = ($flCount  ? $flCount - 1 : 0);
			$questionMarks = sprintf("?%s", str_repeat(",?", $lCount));

			$arCount = count($valuesArray);
			$rCount = ($arCount  ? $arCount - 1 : 0);
			$criteria = sprintf("(".$questionMarks.")%s", str_repeat(",(".$questionMarks.")", $rCount));
			// 3. build the fields list in sql
			$sql .= '(' . implode(',', $fields) . ')';
			// 4. append the placeholders
			$sql .= ' VALUES '.$criteria;
			$toSave = array();
			foreach ($valuesArray as $v) $toSave = array_merge($toSave, array_values($v));
			return $this->queryPrepared($sql,$toSave);
		}
	}

	/**
	 * gets all typologies as an array
	 *
	 * @return array
	 *
	 * @access public
	 */
	public function getTypologies() {
		$sql = 'SELECT DISTINCT(`descrizione`) FROM `'.self::$PREFIX.'tipologie_fonti` ORDER BY `descrizione` ASC';
		$result = $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
		if (AMA_DB::isError($result) || $result===false || count($result)<=0) return null;
		else {
			$retArray = array();
			foreach ($result as $id=>$aDescr) {
				$retArray[urlencode($aDescr['descrizione'])] = $aDescr['descrizione'];
			}
			return $retArray;
		}
	}
	
	/**
	 * get a typology id given a string triplet
	 *
	 * @param string $descrizione
	 * @param string $categoria
	 * @param string $classe
	 *
	 * @return NULL|mixed null if no record or error
	 *
	 * @access public
	 */
	public function getTypologyID ($descrizione, $categoria=null, $classe=null) {
		$sql = 'SELECT `'.self::$PREFIX.'tipologie_fonti_id` FROM `'.self::$PREFIX.'tipologie_fonti`'.
				' WHERE `descrizione`=? AND `categoria`';
		
		$params = array ($descrizione);
	
		if (is_null($categoria)) {
			$sql .= ' IS NULL';
		} else {
			$sql .= '=?';
			array_push($params, $categoria);
		}
		
		$sql .= ' AND `classe`';
		
		if (is_null($classe)) {
			$sql .= ' IS NULL';
		} else {
			$sql .= '=?';
			array_push($params, $classe);
		}
		
		$result = $this->getOnePrepared($sql,$params);
	
		if (AMA_DB::isError($result) || $result===false || count($result)<=0) return null;
		else return $result;
	}
	
	/**
	 * get a typology array given the id
	 *
	 * @param number $id the id to get
	 *
	 * @return NULL|array null if no record or error
	 *
	 * @access public
	 */
	public function getTypologyArray($id) {
		$sql = 'SELECT `descrizione`, `categoria`, `classe` FROM `'.self::$PREFIX.'tipologie_fonti`'.
				' WHERE `'.self::$PREFIX.'tipologie_fonti_id`=?';
	
		$result = $this->getRowPrepared($sql,$id,AMA_FETCH_ASSOC);
	
		if (AMA_DB::isError($result) || $result===false || count($result)<=0) return null;
		else return $result;
	}
	
	/**
	 * gets the children of the passed typology
	 * 
	 * @param stgring  $typology
	 * 
	 * @return NULL|array null if no record or error
	 * 
	 * @access public
	 */
	public function getTypologyChildren($typology) {
		$sql = 'SELECT DISTINCT(`categoria`) FROM `'.self::$PREFIX.'tipologie_fonti`'.
			   ' WHERE `descrizione`=? ORDER BY `categoria` ASC';
		
		$result = $this->getAllPrepared($sql, $typology, AMA_FETCH_ASSOC);
				
		if (AMA_DB::isError($result) || $result===false || count($result)<=0) return null;
		else {
			$retArray = array();
			foreach ($result as $id=>$aDescr) {
				$retArray[] = $aDescr['categoria'];
			}
			return $retArray;
		}
	}
	
	/**
	 * gets the children of the passed typology
	 *
	 * @param stgring  $typology
	 *
	 * @return NULL|array null if no record or error
	 *
	 * @access public
	 */
	public function getCategoryChildren($typology, $category) {
		$sql = 'SELECT DISTINCT(`classe`) FROM `'.self::$PREFIX.'tipologie_fonti`'.
				' WHERE `descrizione`=? AND `categoria`';
		if (is_null($category)) {
			$sql .= ' IS NULL';
			$params = $typology;
		} else {
			$sql .= '=?';
			$params = array($typology, $category);
		}
		$sql.=' ORDER BY `classe` ASC';
	
		$result = $this->getAllPrepared($sql, $params, AMA_FETCH_ASSOC);
		
		if (AMA_DB::isError($result) || $result===false || count($result)<=0) return null;
		else {
			$retArray = array();
			foreach ($result as $id=>$aDescr) {
				$retArray[] = $aDescr['classe'];
			}
			return $retArray;
		}
	}

	/**
	 * gets all typologies as an array
	 *
	 * @return array [module_lex_stati_id]=>'descrizione'
	 *
	 * @access public
	 */
	public function getStates() {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'stati` ORDER BY `descrizione` ASC';
		return $this->getConnection()->getAssoc($sql);
	}

	/**
	 * updates terms associated to an assets (aka saveTree)
	 *
	 * reads the associated terms id and weights from the DB
	 * if a passed id already has a weight then use it
	 * else set the weight to the default
	 *
	 * Then, delete all the associations and save
	 * the new ones with an insertMultiRow method call
	 *
	 * @param number $assetID
	 * @param array $selectedNodes
	 *
	 * @return Ambigous <mixed, boolean, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function updateAssociatedTerms($assetID, $selectedNodes) {

		/**
         * get asset terms stored in the DB.
         * language is of no importance, pass 'it' just to
         * be sure no duplicates are returned
		 */
		$assetEurovocAr = $this->get_asset_eurovoc ($assetID,'it');

		/**
		 * must set the array with descripteur_id as the key
		 * for easier look up, at the end storedArr will be
		 * $storedArr[<DESCRIPTEUR_ID>] = WEIGHT
		 */
		$storedArr = array();
		if (count($assetEurovocAr)>0) {
			foreach ($assetEurovocAr as $assetEurovoc) {
				$storedArr[$assetEurovoc['descripteur_id']] = $assetEurovoc['weight'];
			}
		}

		/**
		 * build array data to be saved
		 */
		if (count($selectedNodes)>0) {
			foreach ($selectedNodes as $selectedNode) {
				$weight = (isset($storedArr[$selectedNode])) ? $storedArr[$selectedNode] : DEFAULT_WEIGHT;
				$saveRelHa[] = array(
						'descripteur_id' => $selectedNode,
						AMALexDataHandler::$PREFIX.'assets_id' => $assetID,
						'weight' => $weight
				);
			}
		}

		$sql = 'DELETE FROM `'.self::$PREFIX.'eurovoc_rel` WHERE `'.self::$PREFIX.'assets_id`=?';
		$result = $this->queryPrepared($sql,$assetID);

		if (!AMA_DB::isError($result)) {
			if (isset($saveRelHa) && count($saveRelHa)>0) {
				$result = $this->insertMultiRow($saveRelHa,'eurovoc_rel');
			}
		}

		return $result;
	}

	/**
	 * Inserts a new typology in the module_lex_tipologie_fonti DB table
	 *
	 * @param string $newTypology
	 *
	 * @return number|AMA_Error
	 *
	 * @access public
	 */
	public function addTypology($newTypology=null) {
		if (!is_null($newTypology) && strlen ($newTypology)>0) {
			$sql = 'INSERT INTO `'.self::$PREFIX.'tipologie_fonti`(`descrizione`) VALUES (?)';

			$result = $this->queryPrepared($sql,$newTypology);

			if (!AMA_DB::isError($result)) {
				return $this->getConnection()->lastInsertID();
			} else return $result;
		} else {
			return new AMA_Error(AMA_ERR_WRONG_ARGUMENTS);
		}
	}
	
	/**
	 * Insert and update for table fonti
	 *
	 * @param array $fonteHa the array to be saved in the DB
	 *
	 * @return array|AMA_Error
	 *
	 * @access public
	 */
	public function fonti_set($fonteHa) {

		return $this->setFromArray(self::$PREFIX.'fonti', self::$PREFIX.'fonti_id', $fonteHa);

	}

	/**
	 * Insert and update fpr table assets
	 *
	 * @param array $assetHa the array to be saved in the DB
	 *
	 * @return array|AMA_Error
	 *
	 * @access public
	 */
	public function asset_set ($assetHa) {

		return $this->setFromArray(self::$PREFIX.'assets', self::$PREFIX.'assets_id', $assetHa);

	}

	public function asset_get ($assetID) {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'assets` WHERE `'.self::$PREFIX.'assets_id`=?';
		return $this->getRowPrepared($sql,$assetID,AMA_FETCH_OBJECT);
	}

	/**
	 * gets the asset text
	 *
	 * @param number $assetID the id of the asset
	 *
	 * @return AMA_Error|string
	 *
	 * @access public
	 */
	public function asset_get_text ($assetID) {

		$sql = 'SELECT `testo` FROM `'.self::$PREFIX.'testi` T JOIN `'.self::$PREFIX.'assets` A '.
			   'ON `T`.`'.self::$PREFIX.'testi_id`=`A`.`'.self::$PREFIX.'testi_id` AND '.
			   '`A`.`'.self::$PREFIX.'assets_id`=?';
		return $this->getOnePrepared($sql,$assetID);
	}

	/**
	 * gets abrogated infos about an asset
	 * 
	 * @param number $assetID he id of the asset
	 * 
	 * @return AMA_Error|array
	 * 
	 *  @access public
	 */
	public function asset_get_abrogated ($assetID) {
		$sql = 'SELECT `abrogato_da`, `data_abrogazione`, `label` FROM `'.self::$PREFIX.'assets_abrogati` '.
			   ' JOIN `'.self::$PREFIX.'assets` AS ASSET ON `abrogato_da`= ASSET.`'.self::$PREFIX.'assets_id`'.
			   ' WHERE `'.self::$PREFIX.'assets_abrogati`.`'.self::$PREFIX.'assets_id`=?';
		
		$result = $this->getAllPrepared($sql,$assetID,AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($result) && $result!==false && count($result)>0) {
			array_walk($result, function (&$value) { $value['data_abrogazione'] = $this->ts_to_date($value['data_abrogazione']); });
		}
		return $result;
	}

	/**
	 * gets descripteur_id, libelle and weight of terms associated with asset
	 *
	 * @param string $id
	 * @param string $lng
	 * @param double $version
	 *
	 * @return NULL|array
	 *
	 * @access public
	 */
	public function get_asset_eurovoc($id, $lng, $version=EUROVOC_VERSION) {

		$sql = 'SELECT `R`.`descripteur_id`, `D`.`libelle`, `R`.`weight` '.
			   'FROM `'.self::$PREFIX.'eurovoc_rel` AS R JOIN `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` AS D '.
		       'ON  `R`.`descripteur_id`= `D`.`descripteur_id` WHERE `'.self::$PREFIX.'assets_id`=? '.
		       'AND `D`.`version`=? AND `D`.`lng`=? ORDER BY `R`.`weight` DESC';

		$result = $this->getAllPrepared($sql,array($id,$version,$lng),AMA_FETCH_ASSOC);

		if (AMA_DB::isError($result) || count($result)<=0) return null;
		else return $result;
	}

	/**
	 * Insert and update for table testi
	 *
	 * @param array $testoHa the array to be saved in the DB
	 *
	 * @return array|AMA_Error
	 *
	 * @access public
	 */
	public function testi_set ($testoHa) {

		$testoHa['testo'] = utf8_encode($testoHa['testo']);

		return $this->setFromArray(self::$PREFIX.'testi', self::$PREFIX.'testi_id', $testoHa);

	}

	/**
	 * Performs the serach for the autocomplete form fields
	 *
	 * @param string $tableName the table to be searched
	 * @param string $fieldName the field to be searched
	 * @param string $term      the search term
	 *
	 * @return NULL|array
	 *
	 * @access public
	 */
	public function doSearchForAutocomplete ($tableName, $fieldName, $term) {
		$retArray = null;

		$sql = 'SELECT `'.$fieldName.'` FROM `'.self::$PREFIX.$tableName.'` WHERE `'.$fieldName."` LIKE ?";

		$result = $this->getConnection()->getAll($sql, array('%'.$term.'%'));

		if (!AMA_DB::isError($result)) {
			foreach ($result as $res) {
				$retArray[] = $res[0];
			}
		}
		return $retArray;
	}

	/**
	 * gets the eurovoc  languages that have been imported
	 */
	public function getSupportedLanguages () {
		$sql = 'SELECT DISTINCT(`lng`) FROM `'.self::$PREFIX.'EUROVOC_DOMAINES` '.
			   'WHERE `version`='.EUROVOC_VERSION;
		$res = $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
		
		if (!AMA_DB::isError($res) && count($res)>0) {
			foreach ($res as $langArray) {
				$supportedLangsArray[$langArray['lng']] = $langArray['lng'];
			}
		} else $supportedLangsArray = null;
		
		return $supportedLangsArray;		
	}

	/**
	 * gets a json encoded object from the domaine cache table
	 *
	 * @param string $domaine_id
	 * @param string $lng
	 * @param double $version
	 *
	 * @return Ambigous <NULL, mixed, unknown, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function getEurovocDOMAINECache ($domaine_id, $lng, $version) {
		$sql = 'SELECT `content` FROM `'.self::$PREFIX.'EUROVOC_DOMAINES_CACHE` '.
			   'WHERE `domaine_id`=? AND `version`=? AND `lng`=?';

		$result = $this->getOnePrepared($sql, array($domaine_id, $version, $lng));

		return ($result===false) ? null : $result;
	}

	/**
	 * sets a domaine subtree as a json encoded object in the domaine cache table
	 *
	 * @param stdClass $cacheObj
	 * @param string $lng
	 * @param double $version
	 *
	 * @access public
	 */
	public function setEurovocDOMAINECache ($cacheObj, $lng, $version) {
		// delete old cache value
		$sql = 'DELETE FROM `'.self::$PREFIX.'EUROVOC_DOMAINES_CACHE` '.
			   'WHERE `domaine_id`=? AND `version`=? AND `lng`=?';
		$this->queryPrepared($sql, array($cacheObj->key,$version,$lng));

		// insert new cache value
		$sql = 'INSERT INTO `'.self::$PREFIX.'EUROVOC_DOMAINES_CACHE` ( '.
		       '`domaine_id` ,`content` ,`version` ,`lng`) VALUES (?,?,?,?)';
		$this->queryPrepared($sql, array($cacheObj->key, json_encode($cacheObj), $version, $lng));
	}

	/**
	 * gets the domaines in the passed language and for the passed eurovoc version
	 *
	 * @param string $lng
	 * @param double $version
	 *
	 * @return Ambigous <mixed, unknown, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function getEurovocDOMAINES($lng, $version) {

		$sql = 'SELECT `domaine_id`, `libelle` FROM `'.self::$PREFIX.'EUROVOC_DOMAINES` '.
			   'WHERE `version`=? AND `lng`=? ORDER BY `domaine_id`';

		return $this->getAllPrepared($sql,array($version,$lng),AMA_FETCH_OBJECT);
	}

	/**
	 * gets the thesaurus terms related to the passed domaine in the passed language and for the passed eurovoc version
	 *
	 * @param string $domaine_id
	 * @param string $lng
	 * @param double $version
	 *
	 * @return Ambigous <mixed, unknown, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function getEurovocTHESAURUS($domaine_id, $lng, $version) {

		$sql = 'SELECT `thesaurus_id`, `libelle` FROM `'.self::$PREFIX.'EUROVOC_THESAURUS` '.
			   'WHERE `thesaurus_id` LIKE ? AND `version`=? AND `lng`=? ORDER BY `thesaurus_id` ASC';

		return $this->getAllPrepared($sql,array($domaine_id.'%',$version,$lng),AMA_FETCH_OBJECT);
	}

	/**
	 * gets the topterms associated to the passed thesaurus term in the passed language and for the passed eurovoc version
	 *
	 * @param string $thesaurus_id
	 * @param string $lng
	 * @param double $version
	 *
	 * @return Ambigous <mixed, unknown, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function getEurovocTOPTERMS($thesaurus_id, $lng, $version) {

		$sql = 'SELECT B.`descripteur_id`, B.`libelle`  FROM `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR_THESAURUS` A '.
		       'JOIN `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` B ON A.`descripteur_id` = B.`descripteur_id` '.
		       'WHERE A.topterm=\'O\' AND `thesaurus_id` LIKE ? AND B.`version`=? AND B.`lng`=? ORDER BY `libelle` ASC';

		return $this->getAllPrepared($sql,array($thesaurus_id.'%',$version,$lng),AMA_FETCH_OBJECT);
	}

	/**
	 * gets the descripteur terms Broader Then the passed descripteur in the passed language and for the passed eurovoc version
	 *
	 * @param string $descripteur_id
	 * @param string $lng
	 * @param double $version
	 *
	 * @return Ambigous <mixed, unknown, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function getEurovocDESCRIPTEURTERMS($descripteur_id, $lng, $version) {

		$sql = ' SELECT B.`descripteur_id`, B.`libelle`, B.`is_user_defined`  FROM `'.self::$PREFIX.'EUROVOC_RELATIONS_BT` A '.
		       'JOIN `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` B ON A.`source_id`= B.`descripteur_id`  '.
			   'WHERE `cible_id` =? AND A.version=? AND B.lng=? ORDER BY B.`libelle` ASC ';

		return $this->getAllPrepared($sql,array($descripteur_id,$version,$lng),AMA_FETCH_OBJECT);
	}

	/**
	 * gets raw table data to be used when exporting eurovoc in XML
	 * 
	 * @param string $tableName table name to be queryed
	 * @param string $lng
	 * @param string $version
	 * @param boolean $isUserDefined true if query must get user defined descripteurs
	 * 
	 * @return mixed whatever getAllPrepared method call returns
	 * 
	 * @access public
	 */
	public function getEurovocRawTable ($tableName, $lng, $version, $isUserDefined) {
		$sql = 'SELECT `'.self::$PREFIX.$tableName.'`.* FROM `'.self::$PREFIX.$tableName.'`';				
		
		if ($tableName==eurovocManagement::$_SUBPREFIX.'_RELATIONS_BT') {
			$sql .= ' LEFT JOIN `'.self::$PREFIX.eurovocManagement::$_SUBPREFIX.
				    '_DESCRIPTEUR` ON `source_id`=`descripteur_id`';
		}
		
		$sql .= ' WHERE `'.self::$PREFIX.$tableName.'`.version=?';
		$params = array ( $version );		

		if (!is_null($lng)) {
			$sql .= ' AND `'.self::$PREFIX.$tableName.'`.lng=?';
			$params[] = $lng;
		}
		
		if ($tableName==eurovocManagement::$_SUBPREFIX.'_DESCRIPTEUR' ||
			$tableName==eurovocManagement::$_SUBPREFIX.'_RELATIONS_BT') {
				$sql .= ' AND `is_user_defined`=' . ($isUserDefined ? '1' : '0');
				if ($tableName==eurovocManagement::$_SUBPREFIX.'_RELATIONS_BT') {
					$sql .= ' GROUP BY `source_id`, `cible_id`';
				}
		}
		
		return $this->getAllPrepared($sql, $params, AMA_FETCH_ASSOC);
	}

	/**
	 * deletes a descripteur_id in the EUROVOC_DESCRIPTEUR table
	 * USED ONLY FOR USER DEFINED TERMS!! This method cannot edit imported terms!
	 *
	 * @param number $domaineID the domaine id of the node, used to rebuild the cache
	 * @param number $nodeID the id or array of ids to delete
	 * @param string $lng
	 * @param string $version
	 *
	 * @return bool true on success, false on failure
	 *
	 * @access public
	 */
	public function deleteEurovocDESCRIPTEURTERMS($domaineID, $nodeID, $lng = ADA_LOGIN_PAGE_DEFAULT_LANGUAGE, $version = EUROVOC_VERSION) {
		if (!is_array($nodeID)) $nodeID = array($nodeID);

		$updateCache = false;

		$sql = 'DELETE FROM `'.self::$PREFIX.'EUROVOC_RELATIONS_BT` '.
			   'WHERE `source_id` IN ('.implode(',', $nodeID).') AND `version`=?';
		$params = array ($version);

		if (!AMA_DB::isError($this->queryPrepared($sql,$params))) {
			$sql = 'DELETE FROM `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` '.
					'WHERE `descripteur_id` IN ('.implode(',', $nodeID).') AND `version`=? AND `lng`=?';
			$params = array ($version,$lng);
			$updateCache = !AMA_DB::isError($this->queryPrepared($sql,$params));
		}

		if ($updateCache) {
			$eurovocObj = new eurovocManagement($lng);
			// force a cache rebuild for the passed domain and return
			$eurovocObj->getEurovocTree(array($domaineID));
			return true;
		}

		return false;
	}

	/**
	 * insert or updates a descripteur_id in the EUROVOC_DESCRIPTEUR table
	 * USED ONLY FOR USER DEFINED TERMS!! This method cannot edit imported terms!
     *
	 * @param unknown $descripteur_id
	 * @param unknown $cible_id
	 * @param unknown $libelle
	 * @param string $lng
	 * @param string $version
	 *
	 * @return number descripteur_id on success or -1 on error
	 *
	 * @access public
	 */
	public function setEurovocDESCRIPTEURTERMS ($domaineID, $descripteur_id, $cible_id, $libelle, $lng = ADA_LOGIN_PAGE_DEFAULT_LANGUAGE, $version = EUROVOC_VERSION) {

		$updateCache = false;

		if (is_null($descripteur_id)) {

			// get max id +1 in passed lang and for passed version
			$descripteur_id = $this->getOnePrepared('SELECT MAX(`descripteur_id`)+1 FROM `'.
					self::$PREFIX.'EUROVOC_DESCRIPTEUR` WHERE `version`=? AND `lng`=?', array ($version,$lng));

			if (!AMA_DB::isError($descripteur_id)) {

				$sql = 'INSERT INTO `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` (`descripteur_id`, `libelle`, `version`,`lng`, `is_user_defined`)'.
					   ' VALUES (?, ?, ?, ?, ?)';
				$params = array ($descripteur_id, $libelle, $version, $lng, 1);

				// if descripteur ok, save its relation
				if (!AMA_DB::isError($this->queryPrepared($sql,$params))) {

					$sql = 'INSERT INTO `'.self::$PREFIX.'EUROVOC_RELATIONS_BT` VALUES (?,?,?)';
					$params = array ($descripteur_id, $cible_id, $version);

					if (!AMA_DB::isError($this->queryPrepared($sql,$params))) {
						$updateCache = true;
					} else {
						// inserting the relation has failed, delete the term
						$sql = 'DELETE FROM `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` '.
								'WHERE `descripteur_id`=? AND `version`=? AND `lng`=?';
						$this->queryPrepared($sql,array($descripteur_id,$version,$lng));
					}
				}
			}
		} else {
			// update
			$sql = 'UPDATE `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` SET `libelle`=? '.
			       'WHERE `descripteur_id`=? AND `version`=? AND `lng`=?';
			$params = array ($libelle, $descripteur_id, $version, $lng);
			$result = $this->queryPrepared($sql,$params);
			if (!AMA_DB::isError($result)) $updateCache = true;
		}

		if ($updateCache) {
			$eurovocObj = new eurovocManagement($lng);
			// force a cache rebuild for the passed domain and return
			$eurovocObj->getEurovocTree(array($domaineID));
			return $descripteur_id;
		}
		return -1;
	}

	/**
	 * deletes all user defined DESCRIPTEUR in the Eurovoc Tree,
	 * together with all their RELATIONS in the tree and
	 * associations with assets
	 * 
	 * @param string $lng
	 * @param double $version
	 * 
	 * @return boolean true on success
	 */
	public function deleteAllUserDefinedDESCRIPTEURS ($lng, $version=EUROVOC_VERSION) {
		$sql = array();
		$result = true;
		
		$sql[] = 'DELETE rel FROM `'.self::$PREFIX.'eurovoc_rel` AS rel '.
		         'JOIN `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` AS D ON '.
		         'rel.`descripteur_id`=D.`descripteur_id` WHERE is_user_defined=1';
		
		$sql[] = 'DELETE BT FROM `'.self::$PREFIX.'EUROVOC_RELATIONS_BT`'.
		         ' AS BT JOIN `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` AS D ON '.
		         '(BT.`source_id`=D.`descripteur_id` OR BT.`cible_id`=D.`descripteur_id`) '.
		         'WHERE is_user_defined=1';
		
		$sql[] = 'DELETE D FROM `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` AS D WHERE `is_user_defined` = 1';
		
		$sql[] = 'DELETE D FROM `'.self::$PREFIX.'EUROVOC_DOMAINES_CACHE` AS D WHERE `content` LIKE '.
				 '\'%"isUserDefined":true%\'';
		
		foreach ($sql as $q) {
			$q .= ' AND D.`lng`=? AND D.`version`=?';
			$result = $result && !AMA_DB::isError($this->queryPrepared($q, array($lng, $version)));
		}
		
		if ($result) {
			$eurovocObj = new eurovocManagement($lng);
			// force a cache rebuild
			$eurovocObj->getEurovocTree();
		}
		
		return $result;
	}

	/**
	 * given a term or an array of terms, gets its or their descripteur_id
	 *
	 * @param string $terms the term or arrray of terms
	 * @param string $lng
	 * @param double $version
	 *
	 * @return Ambigous <mixed, unknown, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function getEurovocDESCRIPTEURIDS ($terms, $lng, $version=EUROVOC_VERSION) {

		if (!is_array($terms)) $libelles = array ($terms);
		else $libelles = $terms;

		$wordsClause = '';

		foreach ($libelles as $num=>$libelle) {
			if (strlen($libelle)>0) {
				$wordsClause .= '(`'.self::$PREFIX.'EUROVOC_DESCRIPTEUR`.`libelle` LIKE ?)';
				if ($num < count($libelles)-1) $wordsClause .= ' OR ';
			}
		}

		$sql = 'SELECT DISTINCT(`'.self::$PREFIX.'EUROVOC_DESCRIPTEUR`.`descripteur_id`) FROM '.
			   '`'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` WHERE ';
		if (strlen($wordsClause)>0) $sql .= $wordsClause;
		else $sql .= '1';

		$sql .= ' AND `version`=? AND `lng`=? ';

		if (strlen($wordsClause)>0) {
			// prepend and append % to libelles value for LIKE operand
			array_walk($libelles, function(&$value){ $value = '%'.$value.'%'; });
			$params = array_merge(array_values($libelles),array($version,$lng));
		} else {
			$params = array($version,$lng);
		}

		return $this->getAllPrepared($sql,$params,AMA_FETCH_ASSOC);
	}

	/**
	 * gets the asset list associated to the passed searchTerms matched
	 * with MySQL NATURAL LANGUAGE MODE FULLTEXT search
	 * the following fields are returned
	 * associated source id, field name: module_lex_fonti_id
	 * asset id, field name: module_lex_assets_id
	 * asset label, field name: label
	 * asset/descripteur_id weight, field name: weight
	 * associated source title, field name: title
	 *
	 * @param array $searchTerm the array of terms to be matched
	 * @param bool $verifiedOnly true if verified assets only are to be returned. defaults to false
	 *
	 * @retrun NULL|Array
	 *
	 * @access public
	 */
	public function get_asset_from_text($searchTerms, $verifiedOnly=false) {
		/**
		 * weight selection with a subquery
		 */
		$subquery = ' (SELECT TESTI.`'.self::$PREFIX.'testi_id`, MATCH (TESTI.`testo`) AGAINST (\''.implode(' ', $searchTerms).'\' IN NATURAL LANGUAGE MODE) as weight '.
		            'FROM `'.self::$PREFIX.'testi` AS TESTI WHERE MATCH (TESTI.`testo`) AGAINST (\''.implode(' ', $searchTerms).'\' IN NATURAL LANGUAGE MODE)>0 ORDER BY weight DESC)'.
		            ' AS TTESTI ';

		$sql = 'SELECT FONTI.`'.self::$PREFIX.'fonti_id`, ASSETS.`'.self::$PREFIX.'assets_id`, ASSETS.`label`, weight, FONTI.`titolo`, TIPOLOGIE.`descrizione` AS `tipologia` '.
		       'FROM `'.self::$PREFIX.'assets` AS ASSETS INNER JOIN'.$subquery.' ON TTESTI.`'.self::$PREFIX.'testi_id` = ASSETS.`'.self::$PREFIX.'testi_id` '.
		       'JOIN `'.self::$PREFIX.'fonti` AS FONTI ON FONTI.`'.self::$PREFIX.'fonti_id` = ASSETS.`'.self::$PREFIX.'fonti_id` '.
		       'JOIN `'.self::$PREFIX.'tipologie_fonti` AS `TIPOLOGIE` ON `FONTI`.`'.self::$PREFIX.'tipologie_fonti_id` = `TIPOLOGIE`.`'.self::$PREFIX.'tipologie_fonti_id` ';

		// gets only verified assets if requested
		if ($verifiedOnly===true) {
			$sql .= ' AND ASSETS.`'.self::$PREFIX.'stati_id`='.MODULES_LEX_ASSET_STATE_VERIFIED;
		}

		$sql .= ' ORDER BY FONTI.`'.self::$PREFIX.'fonti_id` ASC , weight DESC';

		$res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);

		if (!AMA_DB::isError($res) && count($res)>0) {
			$retArray = array();
			foreach ($res as $count=>$element) {
				$key = $element[self::$PREFIX.'fonti_id'];
				$retArray[$key]['titolo'] = $element['titolo'];
				unset($element[self::$PREFIX.'fonti_id']);
				unset($element['titolo']);
				// set element type to fulltext search
				$element['type'] = FULLTEXT_SEARCHTYPE_DISPLAY;
				$retArray[$key]['data'][] = $element;
			}
			return $retArray;
		} else return null;
	}

	/**
	 * gets the asset list associated to the passed descripteur_id array
	 * the following fields are returned
	 * associated source id, field name: module_lex_fonti_id
	 * asset id, field name: module_lex_assets_id
	 * asset label, field name: label
	 * asset/descripteur_id weight, field name: weight
	 * associated source title, field name: title
	 *
	 * @param array $descripteurAr the array of descripteur_id
	 * @param bool $verifiedOnly true if verified assets only are to be returned. defaults to false
	 *
	 * @retrun NULL|Array
	 *
	 * @access public
	 */
	public function get_asset_from_descripteurs ($descripteurAr, $verifiedOnly=false) {
		/**
         * max weight selection with a subquery
		 */
		$subquery = '(SELECT `'.self::$PREFIX.'assets_id`, MAX(weight) as weight FROM `'.self::$PREFIX.'eurovoc_rel` REL';
		// build descripteur clause
		if (is_array($descripteurAr) && count($descripteurAr)>0) {
			$subquery .= ' WHERE REL.`descripteur_id` IN (';
			$subquery .= implode(',', $descripteurAr);
			$subquery .= ' ) ';
		}
		$subquery .='GROUP BY `REL`.`'.self::$PREFIX.'assets_id` ) AS `GROUPEDREL`';

		$sql  = 'SELECT FONTI.`'.self::$PREFIX.'fonti_id`, ASSETS.`'.self::$PREFIX.'assets_id`, ASSETS.`label`, weight,  FONTI.`titolo`, TIPOLOGIE.`descrizione` AS `tipologia` ';
		$sql .= 'FROM `'.self::$PREFIX.'assets` ASSETS INNER JOIN '.$subquery.' ON `GROUPEDREL`.`'.self::$PREFIX.'assets_id` = `ASSETS`.`'.self::$PREFIX.'assets_id` ';
		$sql .= 'JOIN `'.self::$PREFIX.'fonti` AS `FONTI` ON  `FONTI`.`'.self::$PREFIX.'fonti_id`=`ASSETS`.`'.self::$PREFIX.'fonti_id` ';
		$sql .= 'JOIN `'.self::$PREFIX.'tipologie_fonti` AS `TIPOLOGIE` ON `FONTI`.`'.self::$PREFIX.'tipologie_fonti_id` = `TIPOLOGIE`.`'.self::$PREFIX.'tipologie_fonti_id` ';

		if ($verifiedOnly===true) {
			$sql .= ' AND ASSETS.`'.self::$PREFIX.'stati_id`='.MODULES_LEX_ASSET_STATE_VERIFIED;
		}

		$sql .=' ORDER BY FONTI.`'.self::$PREFIX.'fonti_id` ASC , weight DESC ';

		$res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);

		if (!AMA_DB::isError($res) && count($res)>0) {
			$retArray = array();
			foreach ($res as $count=>$element) {
				$key = $element[self::$PREFIX.'fonti_id'];
				$retArray[$key]['titolo'] = $element['titolo'];
				unset($element[self::$PREFIX.'fonti_id']);
				unset($element['titolo']);
				// set element type to descripteur_id match search
				$element['type'] = ID_SEARCHTYPE_DISPLAY;
				$retArray[$key]['data'][] = $element;
			}
			return $retArray;
		} else return null;

	}
	
	/**
	 * deletes a source (aka fonti) together with all its linked
	 * assets and testi; relations between eurovoc terms and assets are as well deleted.
	 * 
	 * @param number $id the id of the source to be deleted
	 * 
	 * @return AMA_Error|boolean true on success
	 * 
	 * @access public
	 */
	public function delete_source ($id) {
		// get all assets and testi id for the source
		$sql = 'SELECT `'.self::$PREFIX.'assets_id` AS `assetID`, `'.self::$PREFIX.'testi_id` AS `testoID` '.
			   'FROM `'.self::$PREFIX.'assets` WHERE `'.self::$PREFIX.'fonti_id`=?';
		
		$res = $this->getAllPrepared($sql,$id,AMA_FETCH_OBJECT);
		
		if (!AMA_DB::isError($res) && is_array($res) && count($res)>0) {
			foreach ($res as $element) {
				$this->queryPrepared('DELETE FROM `'.self::$PREFIX.'eurovoc_rel` WHERE `'.self::$PREFIX.'assets_id`=?',$element->assetID);
				$this->queryPrepared('DELETE FROM `'.self::$PREFIX.'assets` WHERE `'.self::$PREFIX.'assets_id`=?',$element->assetID);
				$this->queryPrepared('DELETE FROM `'.self::$PREFIX.'testi` WHERE `'.self::$PREFIX.'testi_id`=?',$element->testoID);
			}			
		}
		
		$res = $this->queryPrepared('DELETE FROM `'.self::$PREFIX.'fonti` WHERE `'.self::$PREFIX.'fonti_id`=?',$id);
		
		return (!AMA_DB::isError($res) ? true : $res);
	}

	/**
	 * gets the sources list from the module_lex_fonti table
	 *
	 * @param array $fields the array of the fields to get, if 'tipologia' is found in the array then a join with module_lex_tipologie_fonti is performed
	 * @param boolean $idOrdered true if must order by insertion id asc
	 *
	 * @return AMA_Error on error, result of query execution on success
	 *
	 * @access public
	 */
	public function get_sources($fields=array(),$idOrdered=true, $clause=null) {
		$sql = 'SELECT ';

		$typologyKey = array_search('tipologia', $fields);
		if ($typologyKey !== false) {
			unset ($fields[$typologyKey]);
		}

		if (empty($fields)) $sql .= 'F.*';
		else $sql .= implode(',', $fields);

		if ($typologyKey!==false) {
			$sql .= ', T.`descrizione` AS `tipologia`';
		}

		$sql .= ' FROM `'.self::$PREFIX.'fonti` AS F';

		if ($typologyKey!==false) {
			$sql .= ' JOIN `'.self::$PREFIX.'tipologie_fonti` AS T ON `F`.`'.self::$PREFIX.'tipologie_fonti_id`'.
			        ' = `T`.`'.self::$PREFIX.'tipologie_fonti_id`';
		}

		if (!is_null($clause)) $sql .= ' WHERE ' .$clause;

		if ($idOrdered) $sql .= ' ORDER BY `'.self::$PREFIX.'fonti_id` DESC';

		$res = $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);

		if (!AMA_DB::isError($res)) {
			for ($i=0; $i<count($res); $i++) {
				$res[$i]['data_pubblicazione'] = ts2dFN($res[$i]['data_pubblicazione']);
			}
		}

		return $res;
	}

	/**
	 * gets the data to fill a table managed by jQuery dataTable plugin
	 * usually this method gets called using ajax
	 *
	 * aoColumns is an array of table field name to be loaded and
	 * if one of its elements is an array it has to be passed this way:
 	 *	array (
	 *		'fieldName'=>alias for the selected column,
	 *		'columnName'=>column name to be selected,
	 *		'primaryKey'=>primary key of target joined table,
	 *		'tableName'=>name of the table to be joined
	 *	)
	 *
	 * see ajax/getAssetsTableData.php or
	 * ajax/getSourcesTableData.php for examples
	 *
	 * @param array  $aColumns array of columns to load
	 * @param string $sIndexColumn primary key name of the table
	 * @param string $sTable the table to load
	 * @param string $sMainClause main where clause
	 * @param bool   $removeDuplicates true to remove duplicates row, based on.
	 * 				 based on first field of return array (usually the id)Defaults to false
	 *
	 * @return array
	 *
	 * @access public
	 */
	public function getDataForDataTable ($aColumns, $sIndexColumn, $sTable, $sMainClause='', $removeDuplicates=false) {
		/*
		 * Paging
		 */
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
					intval( $_GET['iDisplayLength'] );
		}

		/*
		 * Ordering
		 */
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					if (in_array(strtolower($_GET['sSortDir_'.$i]),array('asc','desc'))) $direction = $_GET['sSortDir_'.$i];
					else $direction = '';

					$realColumnName = (is_array($aColumns[ intval( $_GET['iSortCol_'.$i] ) ])) ?
										$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]['fieldName'] :
										$aColumns[ intval( $_GET['iSortCol_'.$i] ) ];

					$sOrder .= "`".$realColumnName."` ".$direction.", ";
				}
			}

			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
			{
				$sOrder = "";
			}
		}

		/*
		 * Filtering
		 */
		$sWhere = "";
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
		{
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
				{
					if (!is_array($aColumns[$i]))
						$sWhere .= "`".$aColumns[$i]."`";
					else
						$sWhere .= "`".$aColumns[$i]['tableName']."`.`".$aColumns[$i]['columnName']."`";

					$sWhere .= " LIKE ".$this->getConnection()->quote( '%'.$_GET['sSearch'].'%' )." OR ";
				}
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}

		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$realColumnName = (is_array($aColumns[$i])) ? $aColumns[$i]['columnName'] : $aColumns[$i];

			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}

				if (!is_array($aColumns[$i]))
					$sWhere .= "`".$aColumns[$i]."`";
				else
					$sWhere .= "`".$aColumns[$i]['tableName']."`.`".$aColumns[$i]['columnName']."`";

				$sWhere .= "` LIKE ".$this->getConnection()->quote('%'.$_GET['sSearch_'.$i].'%')." ";
			}
		}

		if (strlen ($sMainClause)>0) {
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $sMainClause;
		}


		/*
		 * SQL queries
		 * set up joined tables
		 */
		$sqlColumns = array();
		$joinTables = array();
		$tablesToAlias = array();

		foreach ($aColumns as $column) {
			if (is_array($column)) {
				// generate a unique column name
				if (!in_array($column['tableName'], $tablesToAlias)) {
					$tablesToAlias[] = $column['tableName'];
					$tableAlias = $column['tableName'];
				} else {
					$tableAlias = uniqid($column['tableName'].'_');
				}

				$sqlColumns[] = $tableAlias.'.`'.$column['columnName'].'` AS `'.$column['fieldName'].'` ';
				$operation = (isset($column['operation'])) ? $column['operation'] : ' JOIN ';
				$joinTables[] = $operation.'`'.$column['tableName'].'` AS '.$tableAlias.' ON `'.
								$sTable. '`.`'.$column['primaryKey'].'`='.$tableAlias.'.`'.$column['primaryKey'].'` ';
			} else {
				$sqlColumns[] = '`'.$sTable.'`.`'.$column.'`';
			}
		}

		/*
		 * SQL queries
		 * Get data to display
		 */
		$sQuery = 'SELECT SQL_CALC_FOUND_ROWS '.implode(", ", $sqlColumns).' FROM  `'.$sTable.'`';
		$sQuery .= implode(' ', $joinTables);
		$sQuery .= $sWhere.' '.$sOrder.' '.$sLimit;

		$rResult = $this->getAllPrepared($sQuery,null,AMA_FETCH_ASSOC);

		if (AMA_DB::isError($rResult)) {
			$iTotalDisplayRecords = 0;
			$iTotal = 0;
		} else {
			$iTotalDisplayRecords = $this->getOnePrepared('SELECT FOUND_ROWS()');
			/* Total data set length */
			$sTotalClause = '';
			if (strlen ($sMainClause)>0) $sTotalClause .= ' WHERE '.$sMainClause;
			$iTotal = $this->getOnePrepared('SELECT COUNT(?) FROM `'.$sTable.'`'.$sTotalClause,$sIndexColumn);
		}

		/*
		 * Output
		*/
		$output = array(
				"sEcho" => intval($_GET['sEcho']),
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iTotalDisplayRecords,
				"aaData" => array(),
				"sColumns" => ''
		);

		if (!AMA_DB::isError($rResult) && count($rResult)>0) {

			$output['sColumns'] = implode(',', array_keys(reset($rResult)));
			$alreadyProcessedIDs = array();
			foreach ($rResult as $count=>$aRow)
			{
				if ($removeDuplicates) {
					if (!in_array($aRow[$sIndexColumn], $alreadyProcessedIDs)) $alreadyProcessedIDs[] = $aRow[$sIndexColumn];
					else {
						$output['iTotalDisplayRecords']--;
						continue;
					}
				}
				
				$row = array();

				// Add the row ID and class (if needed) to the object
				$row['DT_RowId'] = $sTable.':'.$aRow[$sIndexColumn];
// 				$row['DT_RowClass'] = 'grade'.$aRow['grade'];

				foreach ($aColumns as $column) {
					$resultArrayKey = (is_array($column)) ? $column['fieldName'] : $column;
					if ( $resultArrayKey == "version" )
					{
						/* Special output formatting for 'version' column */
						$row[] = ($aRow[ $resultArrayKey ]=="0") ? '-' : $aRow[ $resultArrayKey ];
					}
					else if (strpos($resultArrayKey,'data')!==false) {
						/* if is a date, format it */
						$row[] = $this->ts_to_date($aRow[ $resultArrayKey ]);
					}
					else if (strpos($resultArrayKey,'abrogato')!==false) {
						$editAbrogatedLink = CDOMElement::create('a','onclick:javascript:editAbrogated('.$aRow[$sIndexColumn].');');
						$editAbrogatedLink->setAttribute('class', 'tooltip editAbrogatedLink');
						$editAbrogatedLink->setAttribute('title', translateFN('Clic per modificare'));
						$editAbrogatedLink->addChild (new CText(is_null($aRow[ $resultArrayKey ]) ? translateFN('No') : translateFN('Sì')));
						
						$row[] = $editAbrogatedLink->getHtml();
					}
					else if ( $resultArrayKey != ' ' )
					{
						/* General output */
						$row[] = $aRow[ $resultArrayKey ];
					}
				}
				$output['aaData'][] = $row;
			}
		}

		return $output;
	}

	/**
	 * updates a row for both the source and asset in-table saving
	 *
	 * @param string $table table to update
	 * @param string $columnName column in table to update
	 * @param unknown $value value to be set
	 * @param string $id id value of the key of the table defined as '<TABLE>_id'
	 *
	 * @return Ambigous <mixed, boolean, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 *
	 * @access public
	 */
	public function updateModuleLexRow ($table, $columnName, $value, $id) {

		$sql = 'UPDATE `'.$table.'` SET `'.$columnName.'`=?';

		/**
		 * if setting an asset as verified, update verification date to now
		 */
		if ($columnName===self::$PREFIX.'stati_id' && $table===self::$PREFIX.'assets') {
			$sql .= ', `data_verifica`=';
			if (intval($value)===MODULES_LEX_ASSET_STATE_VERIFIED) {
				$sql .= $this->date_to_ts('now');
			} else $sql .= 'NULL';
		}

		$sql .=' WHERE `'.$table.'_id`=?';

		return $this->queryPrepared($sql, array($value,$id));
	}

	/**
	 * Perform an insert or update on the DB in the passed table with
	 * the passed primarykey and the passed array of data to be set.
	 *
	 * @param string $tableName
	 * @param string $primaryKey
	 * @param array $setHa
	 *
	 * @return array|AMA_Error
	 *
	 * @access private
	 */
	private function setFromArray ($tableName, $primaryKey, $setHa) {
		if (!empty($setHa)) {
			$isInsert = null;
			if (isset($setHa[$primaryKey]) && intval($setHa[$primaryKey])>0) {
				$isInsert = false;
				/**
				 * must move primary key to last setHa element
				 * for the queryPrepared to underdstand parameters associations
				 */
				$primaryKeyVal = $setHa[$primaryKey];
				unset ($setHa[$primaryKey]);
				$args = array_values($setHa) + array ($primaryKeyVal);
			} else {
				$isInsert = true;
				if (isset($setHa[$primaryKey])) unset ($setHa[$primaryKey]);
				$args = array_values($setHa);
			}

			if (!is_null($isInsert)) {
				$sql = $this->buildQuery($tableName, $primaryKey, array_keys($setHa),$isInsert);
				$result = $this->queryPrepared($sql,$args);
				if (!AMA_DB::isError($result)) {
					if ($isInsert) $setHa[$primaryKey] = $this->getConnection()->lastInsertID();
					return $setHa;
				} else {
					return $result;
				}
			}
		}
	}

	/**
	 * Builds the SQL to be executed when inserting or updating
	 * guess the fields from the passed array and uses the passed tableName
	 * and primaryKey
	 *
	 * @param string $tableName
	 * @param string $primaryKey
	 * @param fields $fields
	 * @param boolean $generateInsert true to genrate an INSERT. defaults to true
	 *
	 * @return string
	 *
	 * @access private
	 */
	private function buildQuery ($tableName, $primaryKey, $fields, $generateInsert=true) {
		if (!$generateInsert) {
			$sql = 'UPDATE `'.$tableName.'` SET ';
			foreach ($fields as $count=>$field) {
				$sql = '`'.$field.'`=?';
				if ($count < count($fields)) $sql.=', ';
			}
			$sql = 'WHERE `'.$primaryKey.'`=?';

		} else {
			$sql  = 'INSERT INTO `'.$tableName.'` ('. implode(',', $fields) . ') VALUES (';
			$sql .= sprintf("?%s", str_repeat(",?", (count($fields)  ? (count($fields) - 1) : 0))) .')';
		}

		return $sql;
	}

	/**
	 * Returns an instance of AMALexDataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMALexDataHandler
	 */
	static function instance($dsn = null) {
		if(self::$instance === NULL) {
			self::$instance = new AMALexDataHandler($dsn);
		}
		else {
			self::$instance->setDSN($dsn);
		}
		//return null;
		return self::$instance;
	}

}
?>
