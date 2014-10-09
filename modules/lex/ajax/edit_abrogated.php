<?php
/**
 * LEX MODULE.
 *
 * @package        lex module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           lex
 * @version        0.1
 */

ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_AUTHOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';
require_once MODULES_LEX_PATH . '/include/form/formAbrogated.php';

$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));

$retArray = array('status'=>'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && 
	isset($_POST['assetID']) && intval(trim($_POST['assetID']))>0) {
	/**
	 * it's a POST, save the passed abrogation data
	 */
	$assetID = intval(trim($_POST['assetID']));
	
	/**
	 * must check if POST contains any 'abrogato_da_<number>' and 'data_abrogazione_<number>' 
	 * fields with matching numbers, build an array with all of the data and save everything 
	 */
	$saveArray = array();
	
	foreach ($_POST as $postname=>$postvalue) {
		$match = array();
		if (preg_match('/abrogato\_da\_(\d+)/', $postname, $match)) {
			$number = $match[1];
			if (isset($_POST['data_abrogazione_'.$number]) &&
				DataValidator::validate_not_empty_string($postvalue) && 
				DataValidator::validate_date_format($_POST['data_abrogazione_'.$number])) {
				$saveArray[$number]['abrogato_da'] = $postvalue;
				$saveArray[$number]['data_abrogazione'] = $_POST['data_abrogazione_'.$number];						
			}
		}
	}
	
	if (count($saveArray)>0) {
		$res = $dh->asset_set_abrogated ($assetID, $saveArray);
		
		if (AMA_DB::isError($res)) {
			// if it's an error display the error message
			$retArray['status'] = "ERROR";
			$retArray['msg'] = $res->getMessage();
		} else {
			// redirect to classrooms page
			$retArray['status'] = "OK";
			$retArray['msg'] = translateFN('Dati salvati');
		}
	} else $retArray = null; // nothing to be done
		
} else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && 
		   isset($_GET['assetID']) && intval(trim($_GET['assetID']))>0) {
	/**
	 * it's a GET with an assetID, load it and display
	 */
	$assetID = intval(trim($_GET['assetID']));
	$assetAbrogatedAr = $dh->asset_get_abrogated($assetID);

	if (AMA_DB::isError($res)) {
		// if it's an error display the error message without the form
		$retArray['status'] = "ERROR";
		$retArray['msg'] = $res->getMessage();
	} else {
		// display the form with loaded data
		
		$form = new FormAbrogated('editAbrogated',null, $assetID, $assetAbrogatedAr);
		
		$retArray['status'] = "OK";
		$retArray['html'] = $form->getHtml();
		$retArray['dialogTitle'] = translateFN('Abrogazione');
	}
}

echo json_encode($retArray);
