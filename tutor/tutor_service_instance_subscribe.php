<?php
/**
 *
 * @package		Subscription 
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2009-2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.2
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
// require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
// require_once ROOT_DIR . '/include/CourseInstance.inc.php';

$instanceId = DataValidator::is_uinteger($_REQUEST['id_course_instance']);
$courseId = DataValidator::is_uinteger($_REQUEST['id_course']);
$res = $dh->course_instance_tutor_subscribe($instanceId,$_SESSION['sess_userObj']->getId());

if ($courseId && $instanceId && !AMA_DataHandler::isError($res)){
	$nodeId = $courseId.'_0';
	$URL = HTTP_ROOT_DIR .'/browsing/sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$instanceId.'#'.$nodeId;
	header ('Location: '.$URL);
}
else header('Loaction: '.$_SESSION['sess_userObj']->getHomePage());


