<?php
/**
 * EDIT USER.
 *
 * @package
 * @author      Marco Benini
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/tutor_functions.inc.php';

/*
 * YOUR CODE HERE
 */
/**
 * Check if the tutor is editing an allowed student profile
 */
$student_id = DataValidator::is_uinteger(isset($_GET['id']) ? $_GET['id'] : null);
$studensList = $GLOBALS['dh']->get_list_of_tutored_unique_users($userObj->getId());
$preassigned = $GLOBALS['dh']->get_preassigned_students_for_tutor($userObj->getId());

if (!AMA_DB::isError($studensList) && is_array($studensList) && count($studensList)>=0 && 
	!AMA_DB::isError($preassigned) && is_array($preassigned) && count($preassigned)>0) {
		foreach ($preassigned as $userID) $studensList[]['id_utente'] = $userID;	
} else if (AMA_DB::isError($studensList) && 
		   !AMA_DB::isError($preassigned) && is_array($preassigned) && count($preassigned)>=0) {
		   	$studensList = $preassigned;			
} else if (AMA_DB::isError($studensList) && AMA_DB::isError($preassigned)) {
	$studensList = array();
}


$isAllowed = false; // is $userObj a tutor of the student with the passed id?
if (!AMA_DB::isError($studensList) && is_array($studensList) && count($studensList)>0) {
	foreach ($studensList as $aStudent) {
		if ($aStudent['id_utente'] == $student_id) {
			$isAllowed = true;
			break;
		}
	}
}

if ($isAllowed) {
	/**
	 * UNIMC Only:
	 * If the tutor is viewing a student, use browsing/edit_user.php
	 */
	$_GET['id_user'] = $student_id;
	include realpath(dirname(__FILE__)).'/../browsing/edit_user.php';
	$label = translateFN('Scheda utente');
	$help = translateFN('Da qui l\'orientatore può vedere il profilo di un suo utente');
	if (!is_null($editUserObj)) {
		$label .= ': '.$editUserObj->getUserName().' ('.$editUserObj->getFullName().')';
	}
	$content_dataAr['label'] = $label;
	$content_dataAr['help'] = $help;
	ARE::render($layout_dataAr, $content_dataAr,NULL, $optionsAr);
} else {
	header('Location: edit_tutor.php');
	exit();
}
?>