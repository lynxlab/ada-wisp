<?php
/**
 * e-guidance tutor form.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';

$sess_navigationHistory = $_SESSION['sess_navigation_history'];
if($sess_navigationHistory->callerModuleWas('quitChatroom')
   || $sess_navigationHistory->callerModuleWas('close_videochat')
   || $sess_navigationHistory->callerModuleWas('list_events')
   || isset($_GET['popup'])
   ) {
  $self = whoami();
  $is_popup = TRUE;
}
else {
  // $self =  'tutor';
  $self = 'default';
  $is_popup = FALSE;
}

include_once 'include/tutor_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
TutorHelper::init($neededObjAr);

include_once 'include/eguidance_tutor_form_functions.inc.php';

/*
 * YOUR CODE HERE
 */

include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';
include_once ROOT_DIR.'/comunica/include/ADAEventProposal.inc.php';

  $status_opened     = 0;
  $status_closed     = 1;

  if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  // Genera CSV a partire da contenuto $_POST
  // e crea CSV forzando il download

  if(isset($_POST['is_popup'])) {
    $href_suffix = '&popup=1';
    unset($_POST['is_popup']);
  }
  else {
    $href_suffix = '';
  }
  $eguidance_dataAr = $_POST;
  $eguidance_dataAr['id_tutor'] = $userObj->getId();

  if(isset($eguidance_dataAr['id_eguidance_session'])) {
    /*
     * Update an existing eguidance session evaluation
     */
    $result = $dh->update_eguidance_session_data($eguidance_dataAr);
    if(AMA_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
    }
  }
  else {
    /*
     * Save a new eguidance session evaluation
     */
    $result = $dh->add_eguidance_session_data($eguidance_dataAr);
    if(AMA_DataHandler::isError($result)) {
      $errObj = new ADA_Error($result);
    }
  }
  $id_course_instance = $eguidance_dataAr['id_istanza_corso'];
  if ($eguidance_dataAr['status_service'] != $eguidance_dataAr['previous_instance_status']) {
      $instanceInfoAr = $dh->course_instance_get($id_course_instance);
      if(!AMA_DataHandler::isError($instanceInfoAr)) {
          if ($eguidance_dataAr['status_service'] == $status_closed) {
              $instanceInfoAr['data_fine'] = time();
              /**
               * @author giorgio 15/apr/2014
               * 
               * If session has not been started by assigning a tutor,
               * let's start it by setting data_inizio = data_fine
               */
              if ($instanceInfoAr['data_inizio']==0) $instanceInfoAr['data_inizio'] = $instanceInfoAr['data_fine']; 
          }
          elseif ($eguidance_dataAr['status_service'] == $status_opened) {
              $instanceInfoAr['data_fine'] = NULL;
          }
      }
      $updateInstance = $dh->course_instance_set($id_course_instance,$instanceInfoAr);
      
      if($updateInstance && ($eguidance_dataAr['status_service'] == $status_closed)){
       /*
        *  change user status 
        */
        $result = $dh->course_instance_students_presubscribe_get_list($id_course_instance);
        if(AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore in ottenimento stato iscrizione utente'));
        }
        // In WISP we have only one user subscribed to a course instance
        $id_student = $result[0]['id_utente_studente'];
        $result = $dh->course_instance_student_subscribe($id_course_instance, $id_student, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED);
        if(AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore in aggiornamento stato iscrizione utente'));
        }
      }
      elseif($updateInstance && ($eguidance_dataAr['status_service'] == $status_opened)){
       /*
        *  change user status 
        */
        $result = $dh->course_instance_students_presubscribe_get_list($id_course_instance);
        if(AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore in ottenimento stato iscrizione utente'));
        }
        // In WISP we have only one user subscribed to a course instance
        $id_student = $result[0]['id_utente_studente'];
        $tutorAssigned= $dh->course_instance_tutor_get($id_course_instance,1); 
        if($tutorAssigned){
            $result = $dh->course_instance_student_subscribe($id_course_instance, $id_student, ADA_STATUS_SUBSCRIBED);
            if(AMA_DataHandler::isError($result)) {
                $errObj = new ADA_Error($result, translateFN('Errore in aggiornamento stato iscrizione utente'));
            }
        }else{
            $result = $dh->course_instance_student_subscribe($id_course_instance, $id_student, ADA_STATUS_PRESUBSCRIBED);
            if(AMA_DataHandler::isError($result)) {
                $errObj = new ADA_Error($result, translateFN('Errore in aggiornamento stato iscrizione utente'));
            }
          }
      }
}
 
  //createCSVFileToDownload($_POST);

  //$text = translateFN('The eguidance session data were correctly saved.');
  //$form = CommunicationModuleHtmlLib::getOperationWasSuccessfullView($text);
  /*
   * Redirect the practitioner to user service detail
   */
  $tutored_user_id    = $eguidance_dataAr['id_utente'];
  header('Location: user_service_detail.php?id_user='.$tutored_user_id.'&id_course_instance='.$id_course_instance.$href_suffix);
  exit();
}
else {

  /*
   * Obtain event_token from $_GET.
   */

  if(isset($_GET['event_token'])) {
    $event_token = DataValidator::validate_event_token($_GET['event_token']);
    if($event_token === FALSE) {
      $errObj = new ADA_Error(NULL,
                         translateFN("Dati in input per il modulo eguidance_tutor_form non corretti"),
                         NULL, NULL, NULL, $userObj->getHomePage());
    }
    $id_course_instance = ADAEventProposal::extractCourseInstanceIdFromThisToken($event_token);
  }
  else if (isset($_GET['id_course_instance']))
  {
  	$id_course_instance = intval($_GET['id_course_instance']);
  }
  else {
    $errObj = new ADA_Error(NULL,
                         translateFN("Dati in input per il modulo eguidance_tutor_form non corretti"),
                         NULL, NULL, NULL, $userObj->getHomePage());
  }

  /*
   * Get service info
   */
  /*
  $id_course = $dh->get_course_id_for_course_instance($id_course_instance);
  if(AMA_DataHandler::isError($id_course)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento dell'id del servzio"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }
   * 
   */
  $instanceInfoAr = $dh->course_instance_get($id_course_instance);
  if(AMA_DataHandler::isError($instanceInfoAr)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento dell'id del servzio"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }
  $id_course = $instanceInfoAr['id_corso'];

  $service_infoAr = $common_dh->get_service_info_from_course($id_course);
  if(AMA_Common_DataHandler::isError($service_infoAr)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento delle informazioni sul servizio"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }

  $users_infoAr = $dh->course_instance_students_presubscribe_get_list($id_course_instance);
  if(AMA_DataHandler::isError($users_infoAr)) {
    $errObj = new ADA_Error($users_infoAr,translateFN("Errore nell'ottenimento dei dati dello studente"),
                             NULL,NULL,NULL,$userObj->getHomePage());
  }


  /*
   * Get tutored user info
   */
  /*
   * In ADA only a student can be subscribed to a specific course instance
   * if the service has level < 4.
   * TODO: handle form generation for service with level = 4 and multiple users
   * subscribed.
   */
  $user_infoAr = $users_infoAr[0];
  $id_user = $user_infoAr['id_utente_studente'];
  $tutoredUserObj = MultiPort::findUser($id_user);

  $service_infoAr['id_istanza_corso'] = $id_course_instance;
  $service_infoAr['event_token']      = $event_token;
  
  /*
   * data chiusura e apertura istanza
   */
  $status_opened_label     = translateFN('In corso');
  $status_closed_label     = translateFN('Terminato');
  $status_instance = $status_closed_label;  
  $status_instance_value = 1;
  $current_timestamp = time();
  
  if($instanceInfoAr['data_inizio'] > 0 && $instanceInfoAr['data_fine'] > 0
   && $current_timestamp > $instanceInfoAr['data_inizio']
   && $current_timestamp < $instanceInfoAr['data_fine']) {
      $status_instance = $status_opened_label;
      $status_instance_value = 0;
  } else if ($instanceInfoAr['data_inizio']==0) {
  	  $status_instance_value = 0;
  }

  $service_infoAr['instance_status']      = $status_instance;
  $service_infoAr['instance_status_previous'] = $status_instance;
  $service_infoAr['instance_status_value']      = $status_instance_value;
  
  /*
  $service_infoAr['status_opened_label']      = $status_opened_label;
  $service_infoAr['status_closed_label']      = $status_closed_label;
   * 
   */
  $service_infoAr['avalaible_status']      = array($status_opened_label,$status_closed_label);
  $service_infoAr['instance'] = $instanceInfoAr;

  /*
   * Check if an eguidance session with this event_token exists. In this case,
   * use this data to fill the form.
   */
  $eguidance_session_dataAr = $dh->get_eguidance_session_with_event_token($event_token);
  if(!AMA_DataHandler::isError($eguidance_session_dataAr)) {
    if($is_popup) {
      $eguidance_session_dataAr['is_popup'] = true;
    }
    $form = TutorModuleHtmlLib::getEditEguidanceDataForm($tutoredUserObj, $service_infoAr, $eguidance_session_dataAr);
  }
  else {
    $last_eguidance_session_dataAr = $dh->get_last_eguidance_session($id_course_instance);
    if(AMA_DataHandler::isError($last_eguidance_session_dataAr)) {
      $errObj = new ADA_Error($users_infoAr,translateFN("Errore nell'ottenimento dei dati della precedente sessione di eguidance"),
                               NULL,NULL,NULL,$userObj->getHomePage());
    }

    if($is_popup) {
      $last_eguidance_session_dataAr['is_popup'] = true;
    }
    $form = TutorModuleHtmlLib::getEguidanceTutorForm($tutoredUserObj, $service_infoAr, $last_eguidance_session_dataAr);
  }
}

$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'status'    => $status,
  'user_modprofilelink' => $userObj->getEditProfilePage(),
  'dati'      => $form->getHtml()
);
// if it's default.tpl the template field is data and NOT dati
$content_dataAr['data'] = $content_dataAr['dati'];
/**
 * @author giorgio 06/nov/2013
 *
 * form is not built using an FForm object, must attach jquery uniform by hand
 *
 */
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UNIFORM,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename'][] = JQUERY_UNIFORM_CSS;

$options_Ar = array('onload_func' => "initDoc();");

ARE::render($layout_dataAr, $content_dataAr, NULL, $options_Ar);
?>