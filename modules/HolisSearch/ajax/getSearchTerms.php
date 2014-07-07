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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($searchTerms) && is_array($searchTerms) && count($searchTerms)>0) {
	$retArray = $searchTerms;
	
	// create curl resource
	$ch = curl_init();	
	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
	
	foreach ($searchTerms as $searchTerm) {
		// first use the search service
		$url = OPENLABOR_SEARCH_SERVICE_URL . $searchTerm;		
		// set url
		curl_setopt($ch, CURLOPT_URL, $url);		
		// run the request
		$responseObj = json_decode(curl_exec($ch));
		
		if (!is_null($responseObj)) {
			if (isset($responseObj->synonyms) && count ($responseObj->synonyms)>0)
				$retArray = array_unique(array_merge($retArray, $responseObj->synonyms));
			else {
				// no synonyms found, ask the multiwordnet if it has something
				$url = MULTIWORDNET_ANCESTORS_URL . $searchTerm;
				// set url
				curl_setopt($ch, CURLOPT_URL, $url);
				// run the request
				$responseObj = json_decode(curl_exec($ch));	
				if (!is_null($responseObj)) {
					if (isset($responseObj->answer) && count ($responseObj->answer)>0)
						$retArray = array_unique(array_merge($retArray, $responseObj->answer));
				}	
			}
		}
	}
	// close curl resource to free up system resources
	curl_close($ch);
}

echo json_encode(array_values($retArray));

