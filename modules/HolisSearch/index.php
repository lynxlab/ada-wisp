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

/**
 * $forceAbrogated is set by legSearch.php that is including index.php
 */
if (!isset($forceAbrogated) || $forceAbrogated!==true) $forceAbrogated = false;

$holisSearch = new HolisSearchManagement($forceAbrogated);

// $s is coming from $_GET
if (!isset($s) || strlen (trim($s)) <=0) {
		
	$data = $holisSearch->index();
	 
} else if ((isset($s) && strlen (trim($s)) >0)) {
	$searchtext = trim($s);	
	$common_dh = $GLOBALS['common_dh'];
	$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
	$dh = AMAHolisSearchDataHandler::instance(MultiPort::getDSN($pointer));
	
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
	
	if ($userObj->getType()==AMA_TYPE_TUTOR) {
		/**
         * If user it's a tutor, search only in assigned courses
         * otherwise the generated browsing/view.php links won't work
		 */
		$tutoredInstances = $dh->get_tutors_assigned_course_instance($userObj->getId());
		
		if (!AMA_DB::isError($tutoredInstances)) {
			foreach ($tutoredInstances as $tutoredInstanceAr) {
				foreach ($tutoredInstanceAr as $instance) {
					$searchCoursesIDs[] = $instance['id_corso'];
				}
			}
		}
		// remove duplicate ids, if any
		if (count($searchCoursesIDs)>0) {
			$searchCoursesIDs = array_unique($searchCoursesIDs); 
		}
	} else if ($userObj->getType()==AMA_TYPE_STUDENT) {
		$testerPointers = $userObj->getTesters();
		if (!AMA_DB::isError($testerPointers) && count ($testerPointers)>0) {
			foreach ($testerPointers as $testerPointer) {
				$provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($testerPointer));
				if (!AMA_DB::isError($provider_dh)) {					
					$instances = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
					if (!AMA_DB::isError($instances) && is_array($instances) && count($instances)>0) {
						foreach ($instances as $instance) {
							if (!AMA_DB::isError($courseID)) $searchCoursesIDs[] = $instance['id_corso'];
						}
					}										
				}
			}
		}
	} else {
		/**
         * If it's NOT a tutor nor a student, it should be okay to search in
         * all courses of all the providers where the user is listed
		 */
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
	}
	
	/**
	 * Remove non searchable courses from $searchCoursesIDs
	 * by intersecting it with the $searchableCourseIDs array
	 */
	$searchableCourseIDs = $dh->get_searchable_courses_id();
	
	if (!is_null($searchableCourseIDs) && is_array($searchableCourseIDs)) {
		$searchCoursesIDs = array_values(array_intersect($searchableCourseIDs, $searchCoursesIDs));
	}
		
	/**
	 * if searchCoursesIDs is not empty, cast all its values to integers
	 */
	if (count($searchCoursesIDs)>0)
		array_walk($searchCoursesIDs, function (&$value) { $value = intval($value); });
	
	$data = $holisSearch->runSearch (trim($searchtext), count($searchCoursesIDs));
		
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
array_push($layout_dataAr['CSS_filename'], MODULES_HOLISSEARCH_PATH.'/layout/tooltips.css');

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
