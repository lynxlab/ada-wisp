<?php
/**
 * updateSubscription.php - update user status in th DB
 *
 * @package
 * @author		sara <sara@lynxlab.com>
 * @copyright           Copyright (c) 2009-2013, Lynx s.r.l.
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER,AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_TUTOR => array('layout')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
//require_once 'include/switcher_functions.inc.php';
include_once '../include/Subscription.inc.php';

$status_opened     = ADA_INSTANCE_OPENED; //0;
$status_closed     = ADA_INSTANCE_CLOSED; //1;
$status_more_date = ADA_INSTANCE_MORE_DATE; // 2


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $userStatus=$_POST['status'];
    $id_user=$_POST['id_user'];
    $id_instance=$_POST['id_instance'];
    

    $instanceInfoAr = $dh->course_instance_get($id_instance);
    $previousStatus = (!is_null($instanceInfoAr['data_fine'])) ? $status_opened : $status_closed;
    $instanceInfoAr['status']=$userStatus;
    
    if(!AMA_DataHandler::isError($instanceInfoAr)) {
	if ($userStatus == $status_closed) {
	    $instanceInfoAr['data_fine'] = time();
	    /**
	    * @author giorgio 15/apr/2014
	    * 
	    * If session has not been started by assigning a tutor,
	    * let's start it by setting data_inizio = data_fine
	    */
	    if ($instanceInfoAr['data_inizio']==0) $instanceInfoAr['data_inizio'] = $instanceInfoAr['data_fine']; 
	}
	elseif ($userStatus == $status_opened || $userStatus == $status_more_date) {
	    $instanceInfoAr['data_fine'] = NULL;
	}
	$updateInstance = $dh->course_instance_set($id_instance,$instanceInfoAr);
	if(AMA_DataHandler::isError($updateInstance)) {
	    $retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato del servizio"),"title"=>  translateFN('Notifica'));
	}
	else {
	    $retArray=array("status"=>"OK","msg"=>  translateFN("Hai aggiornato correttamente lo stato del servizio"),"title"=>  translateFN('Notifica'));
	}
	
	if($updateInstance && ($userStatus == $status_closed)){
	    /*
	    *  change user status 
	    */
	    $result = $dh->course_instance_students_presubscribe_get_list($id_instance);
	    if(AMA_DataHandler::isError($result)) {
		$retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
	    } else {
		// In WISP we have only one user subscribed to a course instance
		$id_student = $result[0]['id_utente_studente'];
		$result = $dh->course_instance_student_subscribe($id_instance, $id_student, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED);
		if(AMA_DataHandler::isError($result)) {
		    $retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
		}
	    }
	}
	elseif($updateInstance && ($userStatus == $status_opened || $userStatus == $status_opened)){
	    /*
	    *  change user status 
	    */
	    $result = $dh->course_instance_students_presubscribe_get_list($id_instance);
	    if(AMA_DataHandler::isError($result)) {
		$retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
	    }else {
	    // In WISP we have only one user subscribed to a course instance
		$id_student = $result[0]['id_utente_studente'];
		$tutorAssigned= $dh->course_instance_tutor_get($id_instance,1); 
		if($tutorAssigned){
		    $result = $dh->course_instance_student_subscribe($id_instance, $id_student, ADA_STATUS_SUBSCRIBED);
		    if(AMA_DataHandler::isError($result)) {
			$retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
		    }
		}else{
		    $result = $dh->course_instance_student_subscribe($id_instance, $id_student, ADA_STATUS_PRESUBSCRIBED);
		    if(AMA_DataHandler::isError($result)) {
			$retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
		    }
		}
	    }
	}
    }
    else {
	$retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento dello stato dell'iscrizione"),"title"=>  translateFN('Notifica'));
    }
    echo json_encode($retArray);
}
