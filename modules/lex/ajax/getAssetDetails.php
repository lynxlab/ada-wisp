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

$retArray = array ('status'=>'ERROR');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($assetID) && intval($assetID)>0) {
	
	$assetID = intval($assetID);
	
	$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
	
	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
	
	$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));
	
	$languageId = getLanguageCode();
	
	$assetText = $dh->asset_get_text($assetID);
	
	if (!AMA_DB::isError($assetText)) {
		$retArray['status'] = 'OK';
		
		$htmlObj = CDOMElement::create('div','class:assetDetail');
		if (strlen($assetText)>0) {
			$htmlObj->addChild (new CText('<p>'.$assetText.'</p>'));
		}
		
		$assetEurovocAr = $dh->get_asset_eurovoc ($assetID,$languageId);
		
		/**
		 * build an ordered list with associated eurovoc terms
		 */
		if (!is_null($assetEurovocAr)) {
			foreach ($assetEurovocAr as $count=>$assetEurovoc) {
				if (isset($assetEurovoc['libelle']) && strlen($assetEurovoc['libelle'])>0) {
					// make an UL and a section title element if needed
					if (!isset($ulElement)) $ulElement = CDOMElement::create('ol','class:assetWords');
					
					$liElement = CDOMElement::create('li','class:assetWord');					
					$liElement->addChild(new CText($assetEurovoc['libelle']));
					
					if (isset($assetEurovoc['weight']) && strlen($assetEurovoc['weight'])>0) {
						$weightSpan = CDOMElement::create('span','class:assetWordWeight');
						$weightSpan->addChild(new CText(' ('.translateFN('peso: ').
								number_format($assetEurovoc['weight'],3).')'));
						$liElement->addChild($weightSpan);
					}
					$ulElement->addChild($liElement);
				}
			}
			
			/**
			 * if a list was built (i.e. there is at least one associated term)
			 * add it to the returned HTML
			 */			
			if (isset($ulElement)) {
				$spanWordsTitle = CDOMElement::create('span','class:assetWordsTitle');
				$spanWordsTitle->addChild(new CText(translateFN('Termini Associati')));
				$htmlObj->addChild($spanWordsTitle);
				$htmlObj->addChild($ulElement);
			}
		}
		
		/**
		 * Check if the asset has been abrogated
		 */
		$assetAbrogatedAr = $dh->asset_get_abrogated($assetID);
		
		if (!AMA_DB::isError($assetAbrogatedAr) && is_array($assetAbrogatedAr) && count($assetAbrogatedAr)>0) {
			$baseHref = MODULES_LEX_HTTP . '/view.php?assetID=';
			foreach ($assetAbrogatedAr as $count=>$assetAbrogated) {
				if (!isset($ulAbrogated)) $ulAbrogated = CDOMElement::create('ol','class:assetAbrogated');
								
				$link = CDOMElement::create('a','class:abrogatedLink,target:_lextarget,href:'.$baseHref.$assetAbrogated['abrogato_da']);
				$link->addChild (new CText($assetAbrogated['label']));
				
				$spanDate = CDOMElement::create('span');
				$spanDate->addChild (new CText(' '.translateFN('in data').' '.$assetAbrogated['data_abrogazione']));
				
				$liAbrogated = CDOMElement::create('li','class:abrogatedElement');
				$liAbrogated->addChild ($link);
				$liAbrogated->addChild ($spanDate);
				
				$ulAbrogated->addChild ($liAbrogated);
			}
			
			if (isset($ulAbrogated)) {
				$spanAbrogatedTitle = CDOMElement::create('span','class:assetAbrogatedTitle');
				$spanAbrogatedTitle->addChild(new CText(translateFN('Abrogato da').': '));
				$htmlObj->addChild($spanAbrogatedTitle);
				$htmlObj->addChild($ulAbrogated);
			}			
		}		
		
		$retArray['html'] = $htmlObj->getHtml();
	}
}

echo json_encode($retArray);
