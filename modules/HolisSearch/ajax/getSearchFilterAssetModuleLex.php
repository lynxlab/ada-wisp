<?php
/**
 * HOLISSEARCH MODULE.
 *
 * @package        HolisSearch module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           HolisSearch
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
require_once MODULES_HOLISSEARCH_PATH .'/config/config.inc.php';
require_once MODULES_LEX_PATH . '/include/functions.inc.php';

$retArray = null;
$getOnlyVerifiedAssets = true;
if (isset($abrogatedStatus) && is_numeric($abrogatedStatus)) $abrogatedStatus=intval($abrogatedStatus);
else $abrogatedStatus = -1;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && $searchType == HOLIS_SEARCH_FILTER)  {
	
	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
	$dh = AMALexDataHandler::instance(MultiPort::getDSN(MODULES_LEX_PROVIDER_POINTER));
	
	if (!AMA_DB::isError($dh)) {
		
		/**
		 * 1. do a fulltext search on asset associated text and title (if a text is passed) filtered by typology ID list
                 * 
		 */
		$fulltextResults = $dh->get_asset_from_typology($getOnlyVerifiedAssets,$typologyID,$abrogatedStatus);
		if (!is_null($fulltextResults)) {
			// merge the results
			foreach ($fulltextResults as $j=>$fulltextEl) {
				if (!isset($searchResults[$j]) || count($searchResults)<=0) {
					$searchResults[$j] = $fulltextEl;
				} else {
					// merge the data arrays if needed
					foreach ($fulltextEl['data'] as $aFullTextDataRow)
					{
						$key = array_search($aFullTextDataRow[AMALexDataHandler::$PREFIX.'assets_id'],$foundAssetsID[$j]);
						if (false===$key) {
							array_push($searchResults[$j]['data'],$aFullTextDataRow);
						} else {
							if ($searchResults[$j]['data'][$key]['weight'] < $aFullTextDataRow['weight']) {
								// if the new found weight is higher than the old one, update it
								$searchResults[$j]['data'][$key]['weight'] = $aFullTextDataRow['weight'];
							}
						}
					// results sorting is handled by jQuery dataTable
					}
				}
			}
		}
		
		/**
         * 2. build the html tables to be returned
		 */
		$thead_data = array(translateFN('Label'), translateFN('Peso'), translateFN('Abrogato'), translateFN('Tipo'));
		$resAr = array();
		$data = '';
		
		foreach ($searchResults as $j=>$resultEl) {	
			if (is_array($resultEl) && count($resultEl)>0) {
				
				$resultDIV = CDOMElement::create('div','id:moduleLexResult:'.$j.',class:moduleLexResult');
				
				$title = CDOMElement::create('h3','class:tooltip');
				$title->setAttribute('title', translateFN('Clicca per espandere/ridurre'));				
				$title->addChild (new CText( $resultEl['titolo'] ));
				
				$subtitle = CDOMElement::create('span','class:typology');
				$subtitle->addChild (new CText( $resultEl['tipologia'] ));
				
				/**
				 * use index.php?op=zoom to link to the source page with the datatable
				 * 
				 * $viewFontHref = MODULES_LEX_HTTP.'/index.php?op=zoom&id='.$j;
				 */
				$viewFontHref = MODULES_LEX_HTTP.'/view.php?mode=newwin&op=source&sourceID='.$j;
				$viewFontLink = CDOMElement::create('a','class:gotofont tooltip,target:_lextarget,href:'.$viewFontHref);
				$viewFontLink->setAttribute('title', translateFN('Clicca per andare alla fonte'));
				$viewFontLink->addChild(new CText(translateFN('Vai alla Fonte')));
				
				$resultDIV->addChild($viewFontLink);
				
				$resultDIV->addChild($title);
				$title->addChild($subtitle);

				/**
				 * if user is an author, link goes to zoom single asset
				 */
				$baseLink = MODULES_LEX_HTTP;
				if ($userObj->getType() == AMA_TYPE_AUTHOR) {
					$baseLink .= '/index.php?mode=newwin&op=zoom&assetID=';
				} else {
					$baseLink .= '/view.php?mode=newwin&assetID=';
				}
									
				foreach ($resultEl['data'] as $dataEl) {
					
					$labelHref = CDOMElement::create('a','target:_lextarget,class:tooltip,href:'.$baseLink.$dataEl[AMALexDataHandler::$PREFIX.'assets_id']);
					// $labelHref->setAttribute('title', translateFN('Clicca per andare al testo'));
					/**
					 * if user is an author, show asset id as a tooltip
					 */
					if ($userObj->getType() == AMA_TYPE_AUTHOR) {						
						$labelHref->setAttribute('title', 'assetID='.$dataEl[AMALexDataHandler::$PREFIX.'assets_id']);
					}
					
					$labelHref->addChild(new CText($dataEl['label']));
					
					$res_name = $labelHref->getHtml();
					$res_score =  number_format($dataEl['weight'],2);
					
					if (isset($dataEl['isabrogated']) && intval($dataEl['isabrogated'])>0) {
						$res_isabrogated = translateFN('Sì');
					} else {
						$res_isabrogated = translateFN('No');
					}
					
					$typeSpan = CDOMElement::create('span');
					$typeSpan->addChild(new CText($dataEl['type']));
					
					if (in_array($dataEl['type'], array('ID','FT'))) {
						$typeSpan->setAttribute('class', 'tooltip');
						if ('ID'==$dataEl['type']) {
							$typeSpan->setAttribute('title', translateFN('Risultato da termine Eurovoc'));
						} else if ('FT'==$dataEl['type']) {
							$typeSpan->setAttribute('title', translateFN('Risultato da ricerca testuale'));
						}
					}
					
					$res_type = $typeSpan->getHtml();
			
					$temp_results = array($thead_data[0] => $res_name, $thead_data[1] => $res_score, 
										  $thead_data[2] => $res_isabrogated, $thead_data[3]=>$res_type );
			
					array_push ($resAr,$temp_results);
				}
			
				if (count($resAr)>0) {
					$result_table = BaseHtmlLib::tableElement('class:moduleLexResultsTable', $thead_data, $resAr);
					$resAr = array();
					$resultDIV->addChild($result_table);
					$data .= $resultDIV->getHtml();
				}
			}
		}
		
		$retArray = array ('status'=>'OK', 'data'=>$data);
				
				
				
	} else {
		$retArray = array ('status'=>'ERROR', 'data'=>'Cannot get data handler info for pointer:'.MODULES_LEX_PROVIDER_POINTER);
	} // if (!AMA_DB::isError($dh))
}

echo json_encode($retArray);

