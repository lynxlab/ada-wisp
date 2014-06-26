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
		AMA_TYPE_SWITCHER => array('layout','user'),
		AMA_TYPE_AUTHOR => array('layout','user'),
		AMA_TYPE_TUTOR => array('layout','user'),
		AMA_TYPE_STUDENT => array('layout','user')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';
require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';

$retArray = null;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($what) && strlen($what)>0) {
	
	$dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	
	switch ($what) {
		case 'stati':
				$selectAr = $dh->getStates();
			break;
		case 'tipologie':
				$selectAr = $dh->getTypologies();				
			break;
	}

	if (!AMA_DB::isError($selectAr) && count($selectAr)>0) {
		// translate all values
		array_walk ($selectAr, function (&$value, $index){
			$value = translateFN($value);
		});
		$retArray = array ('status'=>'OK', 'data'=>$selectAr);		
	}
}

echo json_encode($retArray);
