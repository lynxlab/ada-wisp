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
	 * does a multi-row insert when importing the eurovoc xml files
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
			$fields = array_keys($valuesArray[0]);
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
	
	public function getTypologies() {
		$sql = 'SELECT * FROM `'.self::$PREFIX.'tipologie_fonti` ORDER BY `descrizione` ASC';
		return $this->getConnection()->getAssoc($sql);
	}
	
	/**
	 * Inserts a new typology in the module_lex_tipologie_fonti DB table
	 * 
	 * @param string $newTypology
	 * 
	 * @return AMA_Error if error | number inserted typology id
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
	 * insert and update for table fonti
	 * 
	 * @param number $id_fonte
	 * @param unknown $fonteAr
	 * @return number|Ambigous <mixed, boolean, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 */
	public function fonti_set($id_fonte=0, $fonteAr) {
		
		$isUpdate = $id_fonte>0;
		
		if (!$isUpdate) {
			$sql = 'INSERT INTO `'.self::$PREFIX.'fonti` '.
			       '(`numero`,`titolo`,`data_pubblicazione`,`'.self::$PREFIX.'tipologie_fonti_id`) VALUES ('.
			       '?,?,?,?)';
			$args = array_values($fonteAr);
		} else {
			$sql = 'UPDATE `'.self::$PREFIX.'fonti` SET `numero`=?, `titolo`=?, `data_pubblicazione`=?, '.
				   '`'.self::$PREFIX.'tipologie_fonti_id`=? WHERE `'.self::$PREFIX.'fonti_id=?';
			$args = array_values($fonteAr) + array($id_fonte);
		}
		
		$result = $this->queryPrepared($sql,$args);
		
		if (!AMA_DB::isError($result)) {
			if (!$isUpdate) return $this->getConnection()->lastInsertID();
			else return $id_fonte;
			
		} else return $result;
	}
	
	/**
	 * insert and update for table testi
	 * 
	 * @param unknown $testoHa
	 * @return unknown|Ambigous <mixed, boolean, object, AMA_Error, PDOException, PDOStatement, unknown_type>
	 */
	public function testi_set ($testoHa=array()) {
		if (!empty($testoHa)) {
			
			$isInsert = null;
			
			if (isset($testoHa['id']) && isset($testoHa['testo'])) {
				$sql = 'UPDATE `'.self::$PREFIX.'testi` SET `testo` = ? WHERE `module_lex_testi_id`=?';
				$args = array (utf8_encode($testoHa['testo']), $testoHa['id']);
				$isInsert = false;
			} else if (isset($testoHa['testo'])) {
				$sql = 'INSERT INTO `'.self::$PREFIX.'testi` (`testo`) VALUES (?)';
				$args = array (utf8_encode($testoHa['testo']));
				$isInsert = true;
			}
			
			if (!is_null($isInsert)) {
				$result = $this->queryPrepared($sql,$args);
				if (!AMA_DB::isError($result)) {
					if ($isInsert) $testoHa['id'] = $this->getConnection()->lastInsertID();
					return $testoHa;
				} else {
					return $result;
				}
			}
		}
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
