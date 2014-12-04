<?php
/**
 * HOLISSEARCH MODULE.
 *
 * @package        HolisSearch module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           HolisSearch
 * @version		   0.1
 */

require_once(ROOT_DIR.'/include/ama.inc.php');
class AMAHolisSearchDataHandler extends AMA_DataHandler {

	/**
	 * module's own data tables prefix
	 * 
	 * @var string
	 */
	public static $PREFIX = 'module_HolisSearch_';
	
	
	/**
	 * gets the stop words array from the common DB
	 * 
	 * @return Ambigous <multitype:, unknown>
	 * 
	 * @access public
	 */
	public static function getStopWordsArray() {		
		$sql = 'SELECT * FROM `'.self::$PREFIX.'stopwords` ORDER BY `word` ASC';
		$result =  $GLOBALS['common_dh']->getConnection()->getAssoc($sql);
		return (!AMA_DB::isError($result) && count($result)>0) ? $result : array();
	}
	
	/**
	 * gets the array of searchable course ids
	 * 
	 * @return Ambigous <NULL, multitype:>
	 * 
	 * @access public
	 */
	public function get_searchable_courses_id() {
		
		$retArray = array();
		/**
		 * The array of searchable service levels shall
		 * come from a DB query, one day or the other...
		 */
		$searchableServiceTypes = $GLOBALS['searchable_service_type'];
		
		if (!AMA_DB::isError($searchableServiceTypes) && is_array($searchableServiceTypes) 
				&& count($searchableServiceTypes)>0) {
			foreach ($searchableServiceTypes as $serviceType) {				
				$servicesRes = $GLOBALS['common_dh']->get_services(null,'s.livello='.$serviceType);
				if (!AMA_DB::isError($servicesRes) && is_array($servicesRes) && count($servicesRes)>0) {
					foreach ($servicesRes as $service) {
						$res = $GLOBALS['common_dh']->get_courses_for_service($service[0]);
					    if (!AMA_DB::isError($res) && is_array($res) && count($res)>0) {
					    	foreach ($res as $record) {
					    		if (!in_array($record['id_corso'], $retArray)) $retArray[] = $record['id_corso'];
					    	}
						}
					}
				}
			}
		}
		
		return (count($retArray)>0) ? $retArray : null;		
	}
	
	/**
	 * wrapper for the connection quote method
	 * 
	 * @param string $text
	 * 
	 * @return string
	 * 
	 * @access public
	 */
	public function quote($text) {		
		return $this->getConnection()->quote($text);
	}

	/**
	 * Returns an instance of AMAHolisSearchDataHandler.
	 *
	 * @param  string $dsn - optional, a valid data source name
	 *
	 * @return an instance of AMAHolisSearchDataHandler
	 */
	static function instance($dsn = null) {
		if(self::$instance === NULL) {
			self::$instance = new AMAHolisSearchDataHandler($dsn);
		}
		else {
			self::$instance->setDSN($dsn);
		}
		//return null;
		return self::$instance;
	}
	
}
?>
