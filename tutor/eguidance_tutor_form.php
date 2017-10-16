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
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER, AMA_TYPE_STUDENT, AMA_TYPE_SUPERTUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_SWITCHER => array('layout'),
  AMA_TYPE_STUDENT => array('layout'),
  AMA_TYPE_SUPERTUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';

  $self = whoami();
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
//  $self = 'default';
  $is_popup = FALSE;
}

include_once 'include/tutor_functions.inc.php';
include_once 'include/eguidance_tutor_form_functions.inc.php';
//include_once ROOT_DIR.'/include/CourseInstance.inc.php';

/*
 * YOUR CODE HERE
 */

include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';
include_once ROOT_DIR.'/comunica/include/ADAEventProposal.inc.php';

  $status_opened     = 0;
  $status_closed     = 1;

  if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

  	/**
  	 * @author giorgio 08/feb/2017
  	 *
  	 * On WISP/UNIMC only:
  	 *
  	 * If submitting for an event that was a proposal, the $_POST['message_ha'] is
  	 * an array holding message data. That message must be persisted to the DB
  	 */
  	if (array_key_exists('message_ha', $_POST) && is_array($_POST['message_ha']) && count($_POST['message_ha'])>0) {
  		$message_ha = array();
  		foreach ($_POST['message_ha'] as $key=>$val) $message_ha[$key] = urldecode($val);
  		if (count($message_ha)>0) {
	  		$mh = MessageHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
	  		// don't care about errors
	  		$mh->send_message($message_ha);
	  		foreach (array($message_ha['mittente'], $message_ha['destinatari']) as $username) {
	  			MultiPort::removeUserAppointments(MultiPort::findUserByUsername($username), array($message_ha['id_messaggio']));
	  		}
  		}
  	}

  // Genera CSV a partire da contenuto $_POST
  // e crea CSV forzando il download

  if(isset($_POST['is_popup'])) {
    $href_suffix = '&popup=1';
//     unset($_POST['is_popup']);
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

  //createCSVFileToDownload($_POST);

  /*
   * Redirect the practitioner to user service detail if it's not a popup
   */
  if (!isset($_POST['is_popup'])) {
	  $tutored_user_id    = $eguidance_dataAr['id_utente'];
	  header('Location: user_service_detail.php?id_user='.$tutored_user_id.'&id_course_instance='.$id_course_instance.$href_suffix);
	  exit();
  }
}
else {

  /*
   * Obtain event_token from $_GET.
   */
  if(isset($_GET['event_token'])) {
  	/**
  	 * @author giorgio 08/feb/2017
  	 *
  	 * On WISP/UNIMC only:
  	 *
  	 * loop agenda and event proposals to find where the event_token is.
  	 * If it's a proposal, save the 'proposals' string in $foundKey and
  	 * the message id for building hidden form fields (see below)
  	 *
     * If session user is a switcher, $user_agendaAr and  $user_events_proposed_exploded will
     * hold its own appointments proposal BUT we need to know the instance's tutor proposals instead
  	 */
  	$foundKey = null;
  	$foundMsgId = null;
  	$agendaToUse = $user_agendaAr;
  	$proposalToUse = $user_events_proposed_exploded;

  	if (in_array($userObj->getType(), array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR)) && isset($_GET['tutorID']) && intval($_GET['tutorID'])>0) {
  		$tutorObj = MultiPort::findUser(intval($_GET['tutorID']));
  		if ($tutorObj instanceof ADAPractitioner) {
  			$agendaToUse = MultiPort::getUserAgenda($tutorObj);
  			$proposalToUse = ADAEventProposal::explodeAgendaMessageFromEventProposal(MultiPort::getTutorEventsProposed($tutorObj));
  		}
  	}

	$event_token = DataValidator::validate_event_token($_GET['event_token']);
	if($event_token === FALSE) {
		$errObj = new ADA_Error(NULL,
			translateFN("Dati in input per il modulo eguidance_tutor_form non corretti"),
			NULL, NULL, NULL, $userObj->getHomePage());
    }
    $id_course_instance = ADAEventProposal::extractCourseInstanceIdFromThisToken($event_token);
    $idTutorFromToken = ADAEventProposal::extractTutorIdFromThisToken($event_token);
    $idUserFromToken = ADAEventProposal::extractTutoredIdFromThisToken($event_token);

	foreach (array('agenda' => $agendaToUse, 'proposals' => $proposalToUse) as $key => $currentAr) {
		$userAgendaForThisProvider = $currentAr[$_SESSION['sess_selected_tester']];
		if (is_array($userAgendaForThisProvider) && count($userAgendaForThisProvider)>0) {
			//var_dump($userAgendaForThisProvider);
	// 	    $appointmentTime = ADAEventProposal::extractTimeFromThisToken($event_token);
		    $appointmentTime = NULL;
		    foreach ($userAgendaForThisProvider as $appTmpAr) {
				if (strpos($appTmpAr[2],$event_token)!== false) {
				    $eguidance_session_dataAr['data_ora'] = $appTmpAr[1];
				    $appointmentTime = $appTmpAr[1];
				    $foundKey = $key;
				    $foundMsgId = $appTmpAr[$idUserFromToken][3];
				}
		    }
		    // break out of the loop if event_token has been found
		    if (!is_null($foundKey)) break;
		}
	}
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

  $service_info_statusAr = Course_instance::getInstanceStatus($instanceInfoAr);
  if ($instanceInfoAr['data_fine'] > 0 && $instanceInfoAr['data_fine'] < time()) $instanceInfoAr['status'] = ADA_INSTANCE_CLOSED;
  $service_infoAr['instance_status'] = $instanceStatusDescription[$instanceInfoAr['status']];
  $service_infoAr['instance_status_value'] = $instanceInfoAr['status'];

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

  $service_infoAr['tipo_patto_formativo'] = $pattoFormativoAr; // $pattoFormativoAr read from config_main.inc.php
  $service_infoAr['tipo_patto_personal'] = $tipoPersonalPattoAr; // $tipoPersonalPattoAr read from config_main.inc.php
  if ((int)$service_infoAr[3] === ADA_SERVICE_IN_ITINERE) {
  	$service_infoAr['in_itinere_checkboxes'] = $inItinereCheckboxAr; // $inItinereCheckboxAr read from config_main.inc.php
  }

  /*
   * Check if an eguidance session with this event_token exists. In this case,
   * use this data to fill the form.
   */
//      var_dump(array($_SESSION['sess_selected_tester'],$user_agendaAr));die();

  if (strlen($event_token)>0) {
  	$eguidance_session_dataAr = $dh->get_eguidance_session_with_event_token($event_token);
  } else {
  	$eguidance_session_dataAr = new AMA_Error();
  }
  $fill_textareas=FALSE;
  if(AMA_DataHandler::isError($eguidance_session_dataAr)) {
    $eguidance_session_dataAr = array();
    $eguidance_session_dataAr['id_utente'] = $idUserFromToken;
    $eguidance_session_dataAr['id_tutor'] = $idTutorFromToken;
    $eguidance_session_dataAr['id_istanza_corso'] = $id_course_instance;
    $eguidance_session_dataAr['event_token'] = $event_token;
    $eguidance_session_dataAr['data_ora'] = (isset($_GET['ts']) && is_numeric($_GET['ts'])) ? intval($_GET['ts']) : $appointmentTime;
    $eguidance_session_dataAr['tipo_eguidance'] = $service_infoAr[3];

  }
    if($is_popup) {
      $eguidance_session_dataAr['is_popup'] = true;
    }

    $tutorReadOnly = $userObj->getType() == AMA_TYPE_TUTOR && !$dh->is_tutor_of_instance($userObj->getId(), $id_course_instance);
    if ($userObj->getType() == AMA_TYPE_STUDENT || $userObj->getType() == AMA_TYPE_SUPERTUTOR || $tutorReadOnly) {
        $eguidanceAssessment = TutorModuleHtmlLib::getEguidanceTutorShow($tutoredUserObj, $service_infoAr,$eguidance_session_dataAr, $fill_textareas);
    } else {
        $eguidanceAssessment = TutorModuleHtmlLib::getEditEguidanceDataForm($tutoredUserObj, $service_infoAr,$eguidance_session_dataAr, $fill_textareas);
        /**
         * @author giorgio 08/feb/2017
         *
         * On WISP/UNIMC only:
         *
         * If event_token has been found in the proposal, add some hidden fields to
         * the form so that when it gets submitted the proposal becomes a confirmed appointment
         */
        if (isset($foundKey) && !is_null($foundKey) && ($foundKey==='proposals') && isset($foundMsgId) && !is_null($foundMsgId)) {
        	require_once ROOT_DIR.'/comunica/include/ADAEvent.inc.php';
        	$msg = MultiPort::getUserAppointment($tutoredUserObj,$foundMsgId);

        	$msg['titolo'] = ADAEventProposal::extractEventToken($msg['titolo']).'#';

        	if ($msg['flags'] & ADA_VIDEOCHAT_EVENT)
        		$msg['titolo'] .= translateFN ( 'Appuntamento in videochat' );
        	else if ($msg['flags'] & ADA_CHAT_EVENT)
        		$msg['titolo'] .= translateFN ( 'Appuntamento in chat' );
        	else if ($msg['flags'] & ADA_PHONE_EVENT)
        		$msg['titolo'] .= translateFN ( 'Appuntamento telefonico' );
        	else if ($msg['flags'] & ADA_IN_PLACE_EVENT)
        		$msg['titolo'] .= translateFN ( 'Appuntamento in presenza' );
        	$newDest = $msg['mittente'];
        	$newMitt = $msg['destinatari'];
        	$msg['destinatari'] = $newDest;
        	$msg['mittente'] = $newMitt;
        	// turn off propsed bit
        	$msg['flags'] = $msg['flags'] & (~ADA_EVENT_PROPOSED);
        	$msg['testo'] = ADAEvent::generateEventMessageAction($msg['flags'], $id_course, $id_course_instance);
        	$msg['flags'] = $msg['flags'] | ADA_EVENT_CONFIRMED;
        	$msg['id_mittente'] = $tutoredUserObj->getId();
        	$msg['data_ora'] = (isset($_GET['ts']) && is_numeric($_GET['ts'])) ? intval($_GET['ts']) : 'now';

        	// add $msg keys to the form as hidden fields, will become an array when posted
        	foreach ($msg as $key=>$val) {
	        	$el = CDOMElement::create('hidden','name:message_ha['.$key.']');
	        	$el->setAttribute('value', urlencode($val));
	        	$eguidanceAssessment->addChild($el);
        	}
        }

    }

/*
    if ($userObj->getType() == AMA_TYPE_STUDENT || $userObj->getType() == AMA_TYPE_SUPERTUTOR) {
        $eguidanceAssessment = TutorModuleHtmlLib::getEguidanceTutorShow($tutoredUserObj, $service_infoAr,$last_eguidance_session_dataAr, $fill_textareas);
    } else {
        $eguidanceAssessment = TutorModuleHtmlLib::getEguidanceTutorForm($tutoredUserObj, $service_infoAr,$last_eguidance_session_dataAr, $fill_textareas);
    }
 *
 */

  }
//}
if (isset($_POST['is_popup'])) {
	// if it's coming from a post and is_popup, just close the popup window
	$layout_dataAr = null;
	$content_dataAr = array();
	$options_Ar = array('onload_func' => "closeMe();");
} else {
	$content_dataAr = array(
	  'user_name' => $user_name,
	  'user_type' => $user_type,
	  'status'    => $status,
	  'user_modprofilelink' => $userObj->getEditProfilePage(),
	  'dati'      => $eguidanceAssessment->getHtml()
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
}

ARE::render($layout_dataAr, $content_dataAr, NULL, $options_Ar);
?>