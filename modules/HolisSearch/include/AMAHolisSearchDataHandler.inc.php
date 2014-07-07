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
