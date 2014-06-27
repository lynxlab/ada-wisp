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


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	$sourceID = (isset($sourceID) && intval($sourceID)>0) ? intval($sourceID) : -1;
	
	$languageId = getLanguageCode();
	
	if (isset ($userObj) && $userObj instanceof ADALoggableUser) {
		$templateFamily = (isset($userObj->template_family) && strlen($userObj->template_family)>0) ? $userObj->template_family : ADA_TEMPLATE_FAMILY;
	} else {
		$templateFamily = ADA_TEMPLATE_FAMILY;
	}
	
	/**
     * build and add the expand/reudce button
	 */
	$expandImg = CDOMElement::create('img','src:'.MODULES_LEX_HTTP.'/layout/'.$templateFamily.'/img/trasp.png');
	$expandImg->setAttribute('class', 'expandAssetButton tooltip');
	$expandImg->setAttribute('title', translateFN('Clic: Mostra/Nasconde i dettagli dell\'asset'));
	
	$dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	
	$output = $dh->getDataForDataTable (
			array ( $dh::$PREFIX.'assets_id',
					$dh::$PREFIX.'assets_id', // duplicate select field is a hack to make room for expand button
					'label',
					'url',
					'data_inserimento',
					'data_verifica',
					array (
 							'fieldName'=>'stato',
 							'columnName'=>'descrizione',
 							'primaryKey'=>$dh::$PREFIX.'stati_id',
 							'tableName'=>$dh::$PREFIX.'stati') 
			        ),
					$dh::$PREFIX.'assets_id',
					$dh::$PREFIX.'assets',
					'`'.$dh::$PREFIX.'assets`.`'.$dh::$PREFIX.'fonti_id`='.$sourceID);
	
	foreach ($output['aaData'] as $i=>$elem) {
		// generate expand link for each row
		$id = intval(str_replace($dh::$PREFIX.'assets:', '', $elem['DT_RowId']));

		/**
		 * get the associated terms array to be used
		 * as a JavaScript parameter to the showEurovocTree js function
		 */
		$eurovocIDArr = $dh->get_asset_eurovoc($id, $languageId);
		$parameters = array();
		if (!is_null($eurovocIDArr)) {
			foreach ($eurovocIDArr as $eurovocID) {
				$parameters[] = intval($eurovocID['descripteur_id']);
			}
		}										
		$linkExpand = 'assetExpand (this, '.json_encode($parameters).');';					
		$expandImg->setAttribute('onclick', 'javascript:'.$linkExpand);					
		// add expand button replacing duplicate id selected field
		$output['aaData'][$i][1] = $expandImg->getHtml();
		
		// generate link button
		$link = MODULES_LEX_HTTP . '/view.php?assetID='.$id;
		$linkAssetButton = CDOMElement::create('button');
		$linkAssetButton->setAttribute('onclick','javascript:self.document.location.href=\''.$link.'\';');
		$linkAssetButton->setAttribute('class', 'linkAssetButton tooltip');
		$linkAssetButton->setAttribute('title',translateFN('Clicca per andare alla pagina dell\'asset'));
		// add link button to output array
		array_push($output['aaData'][$i], $linkAssetButton->getHtml());
	}
	
	// fix the sColumns used by dataTable by adding the
	// expand button sName at array index number 1
	$sColumns = explode (',',$output['sColumns']);
	array_splice($sColumns, 1 ,0, 'expandAssetButton');
	$output['sColumns'] = implode(',', $sColumns);
	// add link button to output column list
	$output['sColumns'] .= ',azioni';
	
	echo json_encode($output);	
}
