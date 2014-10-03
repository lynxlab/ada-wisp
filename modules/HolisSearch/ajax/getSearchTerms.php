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

$retArray = null;

/**
 * terms can be searched in two ways:
 * 1. by passing an array of search terms
 * 2. by passing a query string
 * 
 * if an array is passed, its pieces are glued together and worked as a query string
 * 
 */

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
   (isset($searchTerms) && is_array($searchTerms) && count($searchTerms)>0) || 
   (isset($querystring) && strlen(trim($querystring))>0)) {

   	$retArray = array ( 'searchTerms'=>array(), 'descripteurIds'=>array(), 'searchedURI'=>array() );
   	
   	// create curl resource
   	$ch = curl_init();
   	//return the transfer as a string
   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // connection timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // curl timeout in seconds
   	
   	if (!isset ($querystring)) $querystring = '';
   	
   	if (isset($searchTerms)) {
   		$querystring .= ' '.implode(' ', $searchTerms);
   		$retArray['searchTerms'] = $searchTerms;
   	} else {
   		$retArray['searchTerms'] = array();
   		$searchTerms = explode(' ', trim($querystring));
   	}
   	
	// first use the search service
	$url = EUROVOC_SEARCH_SERVICE_URL . urlencode($querystring);
	$retArray['searchedURI'][] = $url;
	// set url
	curl_setopt($ch, CURLOPT_URL, $url);
	// run the request
	$responseObj = json_decode(curl_exec($ch));

	if (!is_null($responseObj) && curl_errno($ch)===0) {
		// get the synonyms from the response
		if (isset($responseObj->synonyms) && count ($responseObj->synonyms)>0)
			$retArray['searchTerms'] = array_unique(array_merge($retArray['searchTerms'], $responseObj->synonyms));
		else {
			// no synonyms found, ask the multiwordnet if it has something, word by word
			foreach ($searchTerms as $searchTerm) {
				$url = MULTIWORDNET_SYNONYMS_URL . urlencode($searchTerm);
				$retArray['searchedURI'][] = $url;
				// set url
				curl_setopt($ch, CURLOPT_URL, $url);
				// run the request
				$responseObj = json_decode(curl_exec($ch));	
				if (!is_null($responseObj)) {
					if (isset($responseObj->answer) && count ($responseObj->answer)>0)
						$retArray['searchTerms'] = array_unique(array_merge($retArray['searchTerms'], $responseObj->answer));
				}
			}
		}
		
		// get the descripteur_ids from the response
		if (isset($responseObj->categories) && count($responseObj->categories)>0) {
			foreach ($responseObj->categories as $category) {
				$retArray['descripteurIds'][] = $category->category;
			}
		}
	} else {
		// on curl error use the search terms
		$retArray['searchTerms'] = array_unique($searchTerms);
	}
	
	// close curl resource to free up system resources
	curl_close($ch);
}

// substitute underscores with spaces
if (!is_null($retArray['searchTerms'])) {
	array_walk ($retArray['searchTerms'],function(&$value){ $value = str_replace('_', ' ', $value); });
}
// return the array
echo json_encode($retArray);
