<?php
/**
 * checkFiscalCode.php - return HOLIS user type based on fiscal code
 *
 * @package
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009-2013, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_VISITOR);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_VISITOR => array('layout')
);
$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';

/*
 * YOUR CODE HERE
*/

$userTypeFilesDir = ROOT_DIR .'/browsing/';

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset ($_GET['fiscalcode']) && strlen(trim($_GET['fiscalcode']))>0) {
    	$fiscalcode = trim ($_GET['fiscalcode']);
    	
    	$msg = translateFN('Utenza di tipo').': ';
    	$userType = null;
    	
    	foreach ($GLOBALS['user_type_file'] as $aUserType=>$filename) {
    		if (!is_null($filename) && is_file($userTypeFilesDir.$filename)) {
    			$handle = fopen($userTypeFilesDir.$filename, 'r');
    			$foundUser = false;
    			while (($buffer = fgets($handle)) !== false) {
    				if (trim($buffer)===trim($fiscalcode)) {
    					$foundUser = true;
    					break; // Once you find the fiscalcode, break out the loop.    					
    				}
    			}
    			fclose($handle);
    			if ($foundUser) {
    				$userType = $aUserType;
    				break;
    			}
    		}
    	}
    	
    	if (is_null($userType)) {
    		$userType = AMA_TYPE_USER_GENERIC;
    	}
    	
    	$retArray = array ("status"=>"OK", "msg"=>$msg.translateFN($GLOBALS['user_type_labels'][$userType]), "userType"=>$userType);
    	
}

die (json_encode($retArray));
