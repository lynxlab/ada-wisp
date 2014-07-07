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
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR,AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR => array('layout'),
		AMA_TYPE_TUTOR => array('layout'),
		AMA_TYPE_STUDENT => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_HOLISSEARCH_PATH .'/config/config.inc.php';

$self = 'HolisSearch';

require_once MODULES_HOLISSEARCH_PATH . '/include/management/holisSearchManagement.inc.php';

// $searchtext is coming from $_GET
if (!isset($searchtext) || strlen (trim($searchtext)) <=0) {
	
	$holisSearch = new HolisSearchManagement();
	$data = $holisSearch->index();
	 
} else if ((isset($searchtext) && strlen (trim($searchtext)) >0)) {
	
	$common_dh = $GLOBALS['common_dh'];
	
	/**
	 * get the ids of the courses to be searched
	 * that will be passed to javascript to perform
	 * searches with ajax calls.
	 * 
	 * This is done by reading all testers of the logged user
	 * and obtaining the courses foreach user tester
	 * 
	 */
	$searchCoursesIDs = array();
	
	$testerPointers = $common_dh->get_testers_for_user($userObj->getId());
	
	if (!AMA_DB::isError($testerPointers) && count ($testerPointers)>0) {
		foreach ($testerPointers as $testerPointer) {
			$testerInfo = $common_dh->get_tester_info_from_pointer ($testerPointer);
			if (!AMA_DB::isError($testerInfo) && count($testerInfo)>0) {
				$courseIDs = $common_dh->get_courses_for_tester($testerInfo[0]);
				if (!AMA_DB::isError($courseIDs) && count($courseIDs)>0) {
					$searchCoursesIDs = array_merge($searchCoursesIDs, $courseIDs);
				}				
			}
		}
	}
	
	/**
	 * if searchCoursesIDs is not empty, cast all its values to integers
	 */
	if (count($searchCoursesIDs)>0)
		array_walk($searchCoursesIDs, function (&$value) { $value = intval($value); });
	
	$searchtext = trim ($searchtext);
	$holisSearch = new HolisSearchManagement();
	$data = $holisSearch->runSearch ($searchtext, count($searchCoursesIDs));
		
} else {
	
	$data['help'] = '';
	$data['title'] = '';
	$data['htmlObj'] = CDOMElement::create('div');
}
	

/**
 * include proper jquery ui css file depending on wheter there's one
 * in the template_family css path or the default one
*/
if (!is_dir(MODULES_HOLISSEARCH_PATH.'/layout/'.$userObj->template_family.'/css/jquery-ui'))
{
	$layout_dataAr['CSS_filename'] = array(
			JQUERY_UI_CSS
	);
}
else
{
	$layout_dataAr['CSS_filename'] = array(
			MODULES_HOLISSEARCH_PATH.'/layout/'.$userObj->template_family.'/css/jquery-ui/jquery-ui-1.10.3.custom.min.css'
	);
}

array_push($layout_dataAr['CSS_filename'], JQUERY_DATATABLE_CSS);

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'status' => $status,
		'help'  =>  $data['help'],
		'title' =>  $data['title'],
		'data'  =>  $data['htmlObj']->getHtml()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDoc();';
/**
 * if there are courses to be searched and MODULE_LEX is there
 * instruct javascript to do the serach accordingly
 */
if (count($searchCoursesIDs)>0) { 
	$optionsAr['onload_func'] = 'initDoc('.json_encode($searchCoursesIDs).','.(defined('MODULES_LEX') ? 'true' : 'false').');';
}

$avatar = CDOMElement::create('img','class:img_user_avatar,src:'.$userObj->getAvatar());
$content_dataAr['user_avatar'] = $avatar->getHtml();
$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
