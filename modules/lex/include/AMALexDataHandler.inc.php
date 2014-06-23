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
	 * @return array [module_lex_tipologie_fonti_id]=>'descrizione'
	 * 
	 * @access public
	 */
	public function getTypologies() {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'tipologie_fonti` ORDER BY `descrizione` ASC';
		return $this->getConnection()->getAssoc($sql);
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
		return $this->getConnection()->getAll($sql);
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
		
		$sql = ' SELECT B.`descripteur_id`, B.`libelle`  FROM `'.self::$PREFIX.'EUROVOC_RELATIONS_BT` A '.
		       'JOIN `'.self::$PREFIX.'EUROVOC_DESCRIPTEUR` B ON A.`source_id`= B.`descripteur_id`  '.
			   'WHERE `cible_id` =? AND A.version=? AND B.lng=? ORDER BY B.`libelle` ASC ';
		
		return $this->getAllPrepared($sql,array($descripteur_id,$version,$lng),AMA_FETCH_OBJECT);
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
	 * 
	 * @return array
	 * 
	 * @access public
	 */
	public function getDataForDataTable ($aColumns, $sIndexColumn, $sTable, $sMainClause='') {
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
										$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]['columnName'] : 
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
		
		foreach ($aColumns as $column) {
			if (is_array($column)) {
				$sqlColumns[] = '`'.$column['tableName'].'`.`'.$column['columnName'].'` AS `'.$column['fieldName'].'`';
				$joinTables[] = 'JOIN `'.$column['tableName'].'` ON `'.
								$sTable.'`.`'.$column['primaryKey'].'`=`'.$column['tableName'].'`.`'.$column['primaryKey'].'`';
			} else {
				$sqlColumns[] = '`'.$column.'`';
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
				"aaData" => array()
		);
		
		if (!AMA_DB::isError($rResult)) {
			foreach ($rResult as $count=>$aRow)
			{
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
