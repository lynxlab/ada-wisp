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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
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


$dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
	isset ($op) && strlen(trim($op))>0) {

	switch ($op) {
		case 'confirm':
				$msg = translateFN('Questo cancellerà tutti i termini definiti').'<br/>'.
					   translateFN('manualmente e tutte le relative associazioni salvate').'<br/>'.
					   '<br/><strong>'.translateFN('USARE CON ATTENZIONE!').'</strong><br/><br/>'.
					   translateFN('Confermare l\'operazione cliccando su Conferma.');

				$retArray = array ("status"=>'CONFIRM', "msg"=>$msg);
			break;
		case 'delete':
				if ($dh->deleteAllUserDefinedDESCRIPTEURS(getLanguageCode(), EUROVOC_VERSION)) {
					$retArray = array ("status"=>"OK", "msg"=>translateFN("Eurovoc resettato correttamente"));
				} else {
					$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nella cancellazione dei termini"));
				}
			break;
	}
} else {
	$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));
echo json_encode($retArray);
?>