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

$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	if (!isset($_POST['assetID']) && !isset($_POST['abrogatedBy'])) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Non so cosa cancellare"));
	else
	{
		$result = $dh->asset_delete_abrogated(intval($_POST['assetID']),intval($_POST['abrogatedBy']));
		if (!AMA_DB::isError($result))
		{
			$retArray = array ("status"=>"OK", "msg"=>translateFN("Abrogazione cancellata"));
		}
		else
			$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore di cancellazione") );
	}
}
else {
	$retArray = array ("status"=>"ERROR", "msg"=>trasnlateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);
