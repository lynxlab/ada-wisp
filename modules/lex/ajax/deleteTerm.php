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
	isset ($op) && strlen(trim($op))>0 && isset($nodeID) && intval($nodeID)>0) {

	$domaineID = intval ($domaineRootNodeID);
	$nodeID = intval($nodeID);

	switch ($op) {
		case 'check':

			$assetsAr = $dh->get_asset_from_descripteurs(array($nodeID));

			if (!AMA_DB::isError($assetsAr) && is_array($assetsAr) && count($assetsAr)>0) {
				/**
				 * the node you wish to delete has associated
				 * assets: show impossible to delete with details dialog
				 */

				$olElement = CDOMElement::create('ol');
				foreach ($assetsAr as $asset) {
					$liElement = CDOMElement::create('li','class:mainitem');
					$liElement->addChild (new CText($asset['titolo']));
					$nestedOl = CDOMElement::create('ol');
					$liElement->addChild($nestedOl);
					foreach ($asset['data'] as $anElement) {
						$nestedLi = CDOMElement::create('li','class:subitem');
						$nestedLi->addChild(new CText($anElement['label']));
						$nestedOl->addChild($nestedLi);
					}
					$olElement->addChild($liElement);
				}

				$retArray = array ("status"=>'FORCED', "msg"=>$olElement->getHtml(), "delButtonText"=>translateFN('Cancella'));				
			} else if (is_null($assetsAr) || (count($assetsAr)==0)) {
				/**
				 * the node you wish to delete has no associated
				 * assets just ask for confirmation and proceed
				 */
				$msg = translateFN('Questo cancellerà il termine definitivamente.').'<br/>'.
					   translateFN('Confermare l\'operazione cliccando su Conferma.');

				$retArray = array ("status"=>'CONFIRM', "msg"=>$msg);
			} else {
				/**
				 * don't know what's going on...
				 */
				$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Impossibile capire i termini associati a quello da cancellare"));
			}
			break;
		case 'delete':
				if ($dh->deleteEurovocDESCRIPTEURTERMS($domaineID, $nodeID, getLanguageCode(), EUROVOC_VERSION)) {
					$retArray = array ("status"=>"OK", "msg"=>translateFN("Termine cancellato"));
				} else {
					$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nella cancellazione del termine"));
				}
			break;
	}
} else {
	$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nella trasmissione dei dati"));
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"));
echo json_encode($retArray);
?>