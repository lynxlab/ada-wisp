<?php
/**
 * sourceTypologyManagement Class
 *
 * @package 	lex
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	    0.1
 */

/**
 * class for managing the typologies of a source
 *
 * @author giorgio
 */

class sourceTypologyManagement 
{
	/**
	 * statically gets the data handler
	 * 
	 * @return AMALexDataHandler
	 * 
	 * @access private
	 */
	private static function getDBHandler() {
		$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
		if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
		return AMALexDataHandler::instance(MultiPort::getDSN($pointer));
	}
	
	/**
	 * gets the children of the passed element
	 * 
	 * @param string $what 'typology' or 'category' to get children for
	 * @param string $typology
	 * @param string $category
	 * @param boolean $isJexForm true to remove 'all' option from the select
	 * 
	 * @return Ambigous <string, unknown, string>
	 * 
	 * @access private
	 */
	private static function getChildren ($what, $typology, $category=null, $isJexForm=false) {
		$db = self::getDBHandler();
		
		if ($what==='typology') {
			$result = $db->getTypologyChildren(urldecode($typology));
		}
		else if ($what==='category') {
			if (!is_null($category)) $category = urldecode($category);
			$result = $db->getCategoryChildren(urldecode($typology), $category);
		}
		else $result = null;		
		
		// if not in the Jex import form, have a special child for all categories/classes
		if (!$isJexForm) $retArray['null'] = translateFN('Tutte');
		foreach ($result as $value) {
				$retArray[urlencode($value)] = $value;
		}
		
		return $retArray;
	}
	
	/**
	 * get a typology id given a string triplet
	 * 
	 * @param string $description
	 * @param string $category
	 * @param string $class
	 * 
	 * @return Ambigous <NULL, mixed> null if no record or error
	 * 
	 * @access public
	 */
	public static function getIDFromTriple ($description, $category=null, $class=null) {
		$db = self::getDBHandler();
		if (is_null($description)) return 0;
		if ($category==='null') $category = null;
		if ($class==='null') $class = null;
		return $db->getTypologyID ($description, $category, $class);
	}
	
	/**
	 * get a typology array given the id
	 * 
	 * @param number $id
	 * 
	 * @return Ambigous <NULL, mixed> null if no record or error
	 * 
	 * @access public
	 */
	public static function getTripleFromID ($id) {
		$db = self::getDBHandler();
		return $db->getTypologyArray($id);
	}
	
	/**
	 * get the categories children of a typology
	 * 
	 * @param string $typology
	 * @param boolean $isJexForm true to remove 'all' option from the select 
	 * 
	 * @return Ambigous <string, unknown, string>
	 * 
	 * @access public
	 */
	public static function getTypologyChildren ($typology, $isJexForm) {
		return self::getChildren('typology', $typology, null, $isJexForm);
	}
	
	/**
	 * get the classes children of a given typology and category
	 * 
	 * @param string $typology
	 * @param string $category
	 * @param boolean $isJexForm true to remove 'all' option from the select
	 * 
	 * @return Ambigous <string, unknown, string>
	 * 
	 * @access public
	 */
	public static function getCategoryChildren($typology, $category, $isJexForm) {
		if ($category==='null') $category = null;
		return self::getChildren('category', $typology, $category, $isJexForm);
	}

} // class ends here