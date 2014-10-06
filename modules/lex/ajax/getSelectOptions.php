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
ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR => array ('layout'),
		AMA_TYPE_TUTOR => array ('layout'),
		AMA_TYPE_STUDENT => array ('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';
require_once MODULES_LEX_PATH. '/include/management/sourceTypologyManagement.inc.php';



$GLOBALS['dh'] = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retHTML = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($what) && in_array($what, array('categoria','classe'))) {
    	
    	if (!isset($typology) || $typology==='null' || is_null($typology)) {
    		$typology = null;
    	} else {
    		$typology = urldecode($typology);
    	}
    	
    	if (!isset($category) || $category==='null' || is_null($category)) {
    		$category = null;
    	} else {
    		$category = urldecode($category);
    	}
    	
    	if ($what==='categoria') {
    		$retval = sourceTypologyManagement::getTypologyChildren($typology);
    	} else if ($what==='classe') {
    		$retval = sourceTypologyManagement::getCategoryChildren($typology, $category);
    	}
    	
    	if (isset ($retval) && !is_null($retval)) {
    		// write 'all' intead of 'none' when in searchMode
    		if (isset($searchMode) && (intval($searchMode)>0) && array_key_exists('null', $retval)) $retval['null'] = translateFN('Tutte');
    		foreach ($retval as $key=>$element) {
    			$retHTML .= '<option value="'.$key.'">'.$element.'</option>';
    		}
    	}
}
if (!isset($returnArray)) die ($retHTML);
else die (json_encode($retval));
?>