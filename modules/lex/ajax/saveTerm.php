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
    isset ($domaineRootNodeID) && intval($domaineRootNodeID)>0 && 
    isset ($parentNodeID) && intval($parentNodeID)>0 &&
    isset ($term) && strlen(trim($term))>0) {
	
	$domaineID = intval ($domaineRootNodeID);
	$parentNodeID = intval ($parentNodeID);
	// this is passed with POST, but not mandatory. set to null if not passed (new node save)
	$descripteur_id = (isset ($descripteur_id) && intval($descripteur_id)) ? intval($descripteur_id) : null;
	$term = trim($term);
	
	$result = $dh->setEurovocDESCRIPTEURTERMS($domaineID, $descripteur_id, $parentNodeID, $term, getLanguageCode(), EUROVOC_VERSION);
	
	if ($result>0) {
		$retArray = array ("status"=>"OK", "msg"=>translateFN("Termini Aggiornati"), "nodeKey"=>$result);
	} else {
		$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nel salvataggio"));
	}
	
} else {
	$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));

echo json_encode($retArray);
?>