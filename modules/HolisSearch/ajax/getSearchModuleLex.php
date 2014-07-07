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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($searchTerms) && is_array($searchTerms) && count($searchTerms)>0) {
	
	$dh = AMALexDataHandler::instance(MultiPort::getDSN(MODULES_LEX_PROVIDER_POINTER));
	
	if (!AMA_DB::isError($dh)) {
		/**
		 * 1. ask the datahandler for the DESCRIPTEUR_ID of
		 *    the passed word. One day this will be done by a
		 *    webservice hopefully using the $querystring only
		 *    
		 *    NOTE: $querystring is passed in POST
		 */
		$descripteurAr = $dh->getEurovocDESCRIPTEURIDS(array_merge($searchTerms,array($querystring)), getLanguageCode());
		
		// close the session if not needed, this is important for
		// the ajax call to not be block until the script ends and the session is closed
		session_write_close();
		
		if (AMA_DB::isError($descripteurAr)) $descripteur_ids = array();
		else {
			foreach ($descripteurAr as $el) $descripteur_ids[] = $el['descripteur_id'];
		}
		
		/**
		 * 2. ask for the assets associated with the descripteur_ids,
		 *    the weight and the source they belong to
		 */
		if (is_array($descripteur_ids) && count($descripteur_ids)>0) {			
			$searchResults = $dh->get_asset_from_descripteurs($descripteur_ids);			
		} else $searchResults = array();
		
		$foundAssetsID = array();
		foreach ($searchResults as $j=>$aResult) {
			foreach ($aResult['data'] as $key=>$aDataRow)
				$foundAssetsID[$j][$key] = $aDataRow[AMALexDataHandler::$PREFIX.'assets_id'];
		}
		
		/**
		 * 3. now do a fulltext search on asset associated text and merge the results
		 *    with point 2
		 */
		$fulltextResults = $dh->get_asset_from_text($searchTerms);
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
         * 4. build the html tables to be returned
		 */
		$thead_data = array(translateFN('Label'), translateFN('Peso'));
		$resAr = array();
		$data = '';
		
		foreach ($searchResults as $j=>$resultEl) {	
			if (is_array($resultEl) && count($resultEl)>0) {
				
				$resultDIV = CDOMElement::create('div','id:moduleLexResult:'.$j.',class:moduleLexResult');
				
				$title = CDOMElement::create('h3');
				$title->addChild (new CText( $resultEl['titolo'] ));
				
				$viewFontLink = CDOMElement::create('a','class:gotofont,href:'.MODULES_LEX_HTTP.'/index.php?op=zoom&id='.$j);
				$viewFontLink->addChild(new CText(translateFN('Vai alla Fonte')));
				
				$resultDIV->addChild($viewFontLink);
				
				$resultDIV->addChild($title);
				
				$baseLink = MODULES_LEX_HTTP . '/view.php?assetID=';
									
				foreach ($resultEl['data'] as $dataEl) {
					
					$labelHref = CDOMElement::create('a','href:'.$baseLink.$dataEl[AMALexDataHandler::$PREFIX.'assets_id']);
					$labelHref->addChild(new CText($dataEl['label']));
					
					$res_name = $labelHref->getHtml();
					$res_score =  number_format($dataEl['weight'],2);
			
					$temp_results = array($thead_data[0] => $res_name, $thead_data[1] => $res_score );
			
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

