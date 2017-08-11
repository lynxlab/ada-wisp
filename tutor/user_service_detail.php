<?php
/**
 * Displays information about
 *
 * @package
 * @author    Stefano Penge <steve@lynxlab.com>
 * @author    Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author    Vito Modena <vito@lynxlab.com>
 * @copyright Copyright (c) 2009, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
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
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER, AMA_TYPE_SUPERTUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_SWITCHER => array('layout'),
  AMA_TYPE_SUPERTUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
if(isset($_GET['popup'])) {
  $self = 'eguidance_tutor_form';
  $href_suffix='&popup=1';
}
else {
  $self =  whoami(); //'default';//whoami();
  $href_suffix='';
}
$href_suffix='&popup=1';
include_once 'include/tutor_functions.inc.php';
include_once 'include/eguidance_tutor_form_functions.inc.php';
/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';

if(isset($_GET['op']) && $_GET['op'] == 'csv') {
  $event_token = DataValidator::validate_event_token($_GET['event_token']);
  if($event_token === FALSE) {
    $errObj = new ADA_Error(NULL,
                             translateFN("Dati in input per il modulo user_service_detail non corretti"),
                             NULL, NULL, NULL, $userObj->getHomePage());
  }
/*
 * type_of_guidance
 * user_fullname
 * user_country
 * service_duration
 */

  $eguidance_session_dataAr = $dh->get_eguidance_session_with_event_token($event_token);
  if(AMA_DataHandler::isError($eguidance_session_dataAr)) {
    $errObj = new ADA_Error($eguidance_session_dataAr);
  }
  else {
    $tutoredUserObj = MultiPort::findUser($eguidance_session_dataAr['id_utente']);
    $eguidance_session_dataAr['user_fullname'] = $tutoredUserObj->getFullName();
    $eguidance_session_dataAr['user_country']  = $tutoredUserObj->getCountry();
    $eguidance_session_dataAr['service_duration'] = '';

    createCSVFileToDownload($eguidance_session_dataAr);
    /*
     * exits here.
     */
  }
}
else {
  $id_user = DataValidator::is_uinteger($_GET['id_user']);
  $id_course_instance = DataValidator::is_uinteger($_GET['id_course_instance']);
  if($id_user === FALSE || $id_course_instance === FALSE) {
    $errObj = new ADA_Error(NULL,
                             translateFN("Dati in input per il modulo user_servide_detail non corretti"),
                             NULL, NULL, NULL, $userObj->getHomePage());
  }

  /*
   * User data to display
   */
  $tutoredUserObj = MultiPort::findUser($id_user);
  $user_data = TutorModuleHtmlLib::getEguidanceSessionUserDataTable($tutoredUserObj);

  /*
   * Service data to display
   */

  /**
   * Prepare form to allow tutor/switcher to change service status
   */
    $instanceInfoAr = $dh->course_instance_get($id_course_instance);
//    var_dump($instanceInfoAr);
    if(AMA_DataHandler::isError($instanceInfoAr)) {
    $errObj = new ADA_Error(NULL,translateFN("Errore nell'ottenimento dell'id del servzio"),
			     NULL,NULL,NULL,$userObj->getHomePage());
    }
    $id_course = $instanceInfoAr['id_corso'];


    $service_infoAr = $common_dh->get_service_info_from_course($id_course);
//    var_dump($service_infoAr);
    if(!AMA_Common_DataHandler::isError($service_infoAr)) {
	$service_infoAr['id_istanza_corso'] = $id_course_instance;
	$service_infoAr['event_token']      = $event_token;
	$service_level = $service_infoAr[3];
	$service_infoAr['level_name'] = $_SESSION['service_level'][$service_level];

      /*
       * data chiusura e apertura istanza
       */
	$status_opened_label     = translateFN($instanceStatusDescription[ADA_INSTANCE_OPENED]); //translateFN('In corso');
	$status_closed_label     = translateFN($instanceStatusDescription[ADA_INSTANCE_CLOSED]); // translateFN('Terminato');
	$status_more_date	= translateFN($instanceStatusDescription[ADA_INSTANCE_MORE_DATE]);
	$status_instance = $status_closed_label;

	$status_instance_value = ADA_INSTANCE_CLOSED; // 1; 1 = instance Close 0 = instance open
	$current_timestamp = time();

	if($instanceInfoAr['data_inizio'] > 0 && $instanceInfoAr['data_fine'] >= 0
	&& $current_timestamp > $instanceInfoAr['data_inizio']
	/*&& $current_timestamp < $instanceInfoAr['data_fine']*/) {
	  $status_instance = translateFN($instanceStatusDescription[$instanceInfoAr['status']]); //$status_opened_label;
	  $status_instance_value = $instanceInfoAr['status']; //ADA_INSTANCE_OPENED;
	}
	else if ($instanceInfoAr['data_inizio']==0) {
	      $status_instance_value = ADA_INSTANCE_OPENED;
	}

	$service_infoAr['instance_status']      = $status_instance;
	$service_infoAr['instance_status_previous'] = $status_instance;
	$service_infoAr['instance_status_value']      = $status_instance_value;

	$service_infoAr['avalaible_status']      = $instanceStatusDescription; //array($status_opened_label,$status_closed_label);
	$service_infoAr['instance'] = $instanceInfoAr;
	$statusServiceForm = TutorModuleHtmlLib::getServiceStatusForm($tutoredUserObj, $service_infoAr);
	$service_infoAr['status'] = $statusServiceForm->getHtml();
	$service_data = TutorModuleHtmlLib::getServiceDataTableForTutor($service_infoAr);
    }
    else {
      $service_data = new CText('');
    }

    $isProposedAppointment = false;
    $isFutureProposedAppointment = false;
    $isConfirmedAppointement = false;
    $isFutureConfirmedAppointment = false;
    $tbody_data_proposed_appointment = array();
    $tbody_data_confirmed_appointment = array();

    if ($status_instance_value != ADA_INSTANCE_CLOSED) {
	/**
	 * check if there are any proposed appointment for this instance
	 */

    /**
     * @author giorgio 08/feb/2017
     *
     * On WISP/UNIMC only:
     * If session user is a switcher, $user_events_proposedAr will hold its own proposal
     * BUT we need to know the instance's tutor proposals instead
     */
    	if (in_array($userObj->getType(), array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR))) {
    	$tutorObj = MultiPort::findUser($dh->course_instance_tutor_get($id_course_instance));
    	if ($tutorObj instanceof ADAPractitioner) {
    		$user_events_proposedAr = MultiPort::getTutorEventsProposed($tutorObj);
    		$user_agendaAr = MultiPort::getUserAgenda($tutorObj);
    	}
    }

	foreach ($user_events_proposedAr as $user_events_for_provider => $event_list_for_provider) {
	    if (!$isFutureAppointment) {
		if ($user_events_for_provider == $_SESSION['sess_selected_tester']) {
		    $event_listAr = $event_list_for_provider;
		    foreach ($event_listAr as $singleEventAr) {
//			if (!$isFutureAppointment) {
			    $event_time_send_tmp = $singleEventAr[1];
			    $event_text_tmp = $singleEventAr[9];
			    $event_title_tmp = $singleEventAr[2];
			    $event_flag_tmp = $singleEventAr[5];
			    $event_token_tmp = ADAEventProposal::extractEventToken($event_title_tmp);
			    $event_time_tmpAr = ADAEventProposal::extractDateTimesFromEventProposalText($event_text_tmp);
			    $event_instance_tmp = ADAEventProposal::extractCourseInstanceIdFromThisToken($event_token_tmp);
			    if ($event_instance_tmp == $id_course_instance) {
				if (($event_flag_tmp & ADA_EVENT_PROPOSED) ) {
				    $isFutureProposedAppointment = true;
				    for ($i = 0; $i < count($event_time_tmpAr); $i++) {
//					if (!$isFutureProposedAppointment) {
						if (count(explode(':', $event_time_tmpAr[$i]['time']))!=3) $event_time_tmpAr[$i]['time'].=':00';
					    $event_time_tmpAr[$i]['timestamp'] = Abstract_AMA_DataHandler::date_to_ts($event_time_tmpAr[$i]['date'],$event_time_tmpAr[$i]['time']);
					    /**
					     * @author giorgio 08/feb/2017
					     *
					     * On WISP/UNIMC only:
					     *
					     * keep showing expired proposals for DAYS_TO_SHOW_EXPIRED_PROPOSALS
					     * number of days (86400 is the number of seconds in a day).
					     * DAYS_TO_SHOW_EXPIRED_PROPOSALS is a define in the client config.
					     */
					    $timeToShowProposal = 0;
					    if  (defined('DAYS_TO_SHOW_EXPIRED_PROPOSALS') && intval(DAYS_TO_SHOW_EXPIRED_PROPOSALS)>0) {
					    	$timeToShowProposal += DAYS_TO_SHOW_EXPIRED_PROPOSALS * 86400;
					    }
					    if ($event_time_tmpAr[$i]['timestamp']+$timeToShowProposal >= time()) {
					    	/**
					    	 * @author giorgio 08/feb/2017
					    	 *
					    	 * On WISP/UNIMC only:
					    	 *
					    	 * generate create report link
					    	 */
					    	$href = HTTP_ROOT_DIR . '/tutor/eguidance_tutor_form.php?event_token=' . $event_token_tmp .
					    							'&ts='.$event_time_tmpAr[$i]['timestamp'];
					    	if (isset($tutorObj)) $href .= '&tutorID='.$tutorObj->getId();
				    		if (time() >= $event_time_tmpAr[$i]['timestamp'] && ($userObj->getType() == AMA_TYPE_SWITCHER ||
				    		    ($userObj->getType() == AMA_TYPE_TUTOR && !$userObj->isSuper()  &&
				    			$dh->is_tutor_of_instance($userObj->getId(), $id_course_instance)))) {
						    	$report_link = CDOMElement::create('a', "href:$href");
						    	$report_link->addChild(new CText(translateFN('crea report')));
				    		} else {
				    			$report_link = CDOMElement::create('span');
				    			$report_link->addChild(new CText('-'));
				    		}

					    	$isFutureProposedAppointment = true;

					    	if ($event_flag_tmp & ADA_VIDEOCHAT_EVENT)
					    		$propType = translateFN ( 'Appuntamento in videochat' );
					    	else if ($event_flag_tmp & ADA_CHAT_EVENT)
					    		$propType = translateFN ( 'Appuntamento in chat' );
					   		else if ($event_flag_tmp & ADA_PHONE_EVENT)
				 				$propType = translateFN ( 'Appuntamento telefonico' );
		    				else if ($event_flag_tmp & ADA_IN_PLACE_EVENT)
		    					$propType = translateFN ( 'Appuntamento in presenza' );

// 					    	$tbody_data_proposed_appointment[] = array(Abstract_AMA_DataHandler::ts_to_date($event_time_tmpAr[$i]['timestamp']), ADAEventProposal::removeEventToken($event_title_tmp));
//							$tbody_data_proposed_appointment[] = array($event_time_tmpAr[$i]['date'].' - '.$event_time_tmpAr[$i]['time'], ADAEventProposal::removeEventToken($event_title_tmp));

		    				/**
	    					 * @author giorgio 16/mar/2017
	    					 *
	    					 * On WISP/UNIMC only:
	    					 *
	    					 * add to $tbody_data_proposed_appointment only if no eguidance session has been found
	    					 */
	    					$tmpEguidanceSession = $dh->get_eguidance_session($id_course_instance);
	    					if (is_array($tmpEguidanceSession)) {
	    						$filteredTmp = array_filter($tmpEguidanceSession, function($element) use ($event_token_tmp) {
	    							return is_array($element) && array_key_exists('event_token', $element) && $element['event_token'] == $event_token_tmp;
	    						});
	    					} else {
	    						$filteredTmp = array();
	    					}
	    					if (!is_array($tmpEguidanceSession) || count($filteredTmp)<=0) {
	    	    				$tbody_data_proposed_appointment[] = array($event_time_tmpAr[$i]['date'].' - '.$event_time_tmpAr[$i]['time'], $propType, $report_link->getHtml());
	    					}
					    }
//					}
				    }
				}
			    }
//			}
		    }
		}
	    }
	}
	/**
	 * End check proposed appointement
	 */

	/**
	 * check if there are any confirmed appointment for this instance
	 */
	foreach ($user_agendaAr as $user_agenda_for_provider => $agenda_list_for_provider) {
	    if (!$isFutureConfirmedAppointment) {
		if ($user_agenda_for_provider == $_SESSION['sess_selected_tester']) {
		    $agenda_listAr = $agenda_list_for_provider;
		    foreach ($agenda_listAr as $singleagendaAr) {
			if (!$isFutureConfirmedAppointment) {
			    $agenda_time_send_tmp = $singleagendaAr[1];
			    $agenda_text_tmp = $singleagendaAr[9];
			    $agenda_title_tmp = $singleagendaAr[2];
			    $agenda_flag_tmp = $singleagendaAr[5];
			    $agenda_token_tmp = ADAEventProposal::extractEventToken($agenda_title_tmp);
			    $agenda_time_tmpAr = ADAEventProposal::extractDateTimesFromEventProposalText($agenda_text_tmp);
			    $agenda_instance_tmp = ADAEventProposal::extractCourseInstanceIdFromThisToken($agenda_token_tmp);
//			    var_dump(array($agenda_flag_tmp,$agenda_instance_tmp,$agenda_token_tmp,$agenda_title_tmp, $agenda_text_tmp, $agenda_time_send_tmp));
			    if ($agenda_instance_tmp == $id_course_instance) {
				if (($agenda_flag_tmp & ADA_EVENT_CONFIRMED) ) {
				    $isConfirmedAppointment = true;
					$isFutureConfirmedAppointment = true;


					/**
					 * @author giorgio 21/feb/2017
					 *
					 * On WISP/UNIMC only:
					 *
					 * generate create report link
					 */
					$href = HTTP_ROOT_DIR . '/tutor/eguidance_tutor_form.php?event_token=' . $agenda_token_tmp.
					'&ts='.$agenda_time_send_tmp;
					if (isset($tutorObj)) $href .= '&tutorID='.$tutorObj->getId();
					if (time() >= $agenda_time_send_tmp && ($userObj->getType() == AMA_TYPE_SWITCHER ||
					    ($userObj->getType() == AMA_TYPE_TUTOR && !$userObj->isSuper()  &&
						 $dh->is_tutor_of_instance($userObj->getId(), $id_course_instance)))) {
						 $report_link = CDOMElement::create('a', "href:$href");
						 $report_link->addChild(new CText(translateFN('crea report')));
					} else {
						$report_link = CDOMElement::create('span');
						$report_link->addChild(new CText('-'));
					}

					if ($agenda_flag_tmp & ADA_VIDEOCHAT_EVENT)
						$propType = translateFN ( 'Appuntamento in videochat' );
					else if ($agenda_flag_tmp & ADA_CHAT_EVENT)
						$propType = translateFN ( 'Appuntamento in chat' );
					else if ($agenda_flag_tmp & ADA_PHONE_EVENT)
						$propType = translateFN ( 'Appuntamento telefonico' );
					else if ($agenda_flag_tmp & ADA_IN_PLACE_EVENT)
						$propType = translateFN ( 'Appuntamento in presenza' );


// 			        $tbody_data_confirmed_appointment[] = array(Abstract_AMA_DataHandler::ts_to_date($agenda_time_send_tmp, ADA_DATE_FORMAT.' - %R'), ADAEventProposal::removeEventToken($agenda_title_tmp));

					/**
					 * @author giorgio 16/mar/2017
					 *
					 * On WISP/UNIMC only:
					 *
					 * add to $tbody_data_confirmed_appointment only if no eguidance session has been found
					 */
					if (!is_array($dh->get_last_eguidance_session($id_course_instance))) {
						if ($agenda_time_send_tmp >= time()) {
							$tbody_data_confirmed_appointment[] = array(Abstract_AMA_DataHandler::ts_to_date($agenda_time_send_tmp, ADA_DATE_FORMAT.' - %R'), $propType, $report_link->getHtml());
						} else {
							$isFutureProposedAppointment = true;
							$tbody_data_proposed_appointment[] = array(Abstract_AMA_DataHandler::ts_to_date($agenda_time_send_tmp, ADA_DATE_FORMAT.' - %R'), $propType, $report_link->getHtml());
						}
					}

//				    for ($i = 0; $i < count($event_time_tmpAr); $i++) {
//					if (!$isFutureConfirmedAppointment) {
//					    $agenda_time_tmpAr[$i]['timestamp'] = Abstract_AMA_DataHandler::date_to_ts($agenda_time_tmpAr[$i]['date'],$agenda_time_tmpAr[$i]['time']);
//					    if ($agenda_time_tmpAr[$i]['timestamp'] >= time()) $isFutureConfirmedAppointment = true;
//					}
//				    }
//				    var_dump($isFutureConfirmedAppointment);
//				    echo "porcoilcletro Confermato";
				}
			    }
			}
		    }
		}
	    }
	}
	/**
	 * End check confirmed appointement
	 */


    }
//    $user_events_proposedAr

  /*
   * Eguidance sessions data to display
   */

  $eguidance_sessionsAr = $dh->get_eguidance_sessions($id_course_instance);
  $thead_data = array(translateFN('Eguidance sessions conducted'), translateFN('Appointment type'), translateFN('Azioni'), translateFN('CSV'));
  $tbody_data = array();
  if(AMA_DataHandler::isError($eguidance_sessionsAr) || count($eguidance_sessionsAr) == 0) {
      // $tbody_data[] = array(new CText(translateFN('Nessuna Sessione effettuata')));
     $eguidance_data = new CText('');
  }
  else {
  	/**
  	 * @author giorgio 16/mar/2017
  	 *
  	 * On WISP/UNIMC only: get a message handler
  	 *
  	 * @var MessageHandler $mh
  	 */
  	$mh = MessageHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
    foreach($eguidance_sessionsAr as $eguidance_sessionAr) {
	if ($eguidance_sessionAr['event_token'] != '') {

	    $eguidance_date = Abstract_AMA_DataHandler::ts_to_date($eguidance_sessionAr['data_ora'], ADA_DATE_FORMAT.' - %R');

//	    $message_handler = MessageHandler::instance();
//	    $confirmedAppointment = $mh->

//	    $eguidance_date = Abstract_AMA_DataHandler::ts_to_date(ADAEventProposal::extractTimeFromThisToken($eguidance_sessionAr['event_token']), ADA_DATE_FORMAT.' - %R');
// 	    $eguidance_type = EguidanceSession::textForEguidanceType($eguidance_sessionAr['tipo_eguidance']);

	    /**
	     * @author giorgio 16/mar/2017
	     *
	     * On WISP/UNIMC only: get the message flags to know the appointment type
	     *
	     * @var MessageHandler $mh
	     */
	    $fields_list_Ar = array('flags');
	    $clause         = 'titolo LIKE \''.$eguidance_sessionAr['event_token'].'#%\'';
	    $msgs_ha = $mh->find_messages($eguidance_sessionAr['id_utente'],ADA_MSG_AGENDA,$fields_list_Ar,$clause);

	    $eguidance_type = translateFN('Sconosciuto');
	    if (!AMA_DB::isError($msgs_ha) && is_array($msgs_ha) && count($msgs_ha)>0) {
	    	$flag = intval(reset($msgs_ha));
	    	if ($flag & ADA_VIDEOCHAT_EVENT)
	    		$eguidance_type= translateFN ( 'Appuntamento in videochat' );
    		else if ($flag & ADA_CHAT_EVENT)
    			$eguidance_type= translateFN ( 'Appuntamento in chat' );
    		else if ($flag & ADA_PHONE_EVENT)
    			$eguidance_type= translateFN ( 'Appuntamento telefonico' );
    		else if ($flag & ADA_IN_PLACE_EVENT)
	    		$eguidance_type= translateFN ( 'Appuntamento in presenza' );
	    }

	    $href = 'eguidance_tutor_form.php?event_token=' . $eguidance_sessionAr['event_token'].$href_suffix;
	    if (in_array($userObj->getType(), array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR)) && isset($tutorObj)) $href .= '&tutorID='.$tutorObj->getId();
	    $eguidance_form = CDOMElement::create('a', "href:$href");
	    $eguidance_form->setAttribute('target', '_blank');
	    $eguidance_form_lbl = translateFN('vedi');
	    if ($userObj->getType() == AMA_TYPE_SWITCHER ||
	    	($userObj->getType() == AMA_TYPE_TUTOR && !$userObj->isSuper()  &&
	    	 $dh->is_tutor_of_instance($userObj->getId(), $id_course_instance))) {
    		$eguidance_form_lbl = translateFN('edit');
    		$extraPath = ' In questa sezione Il docente tutor può modificare lo status del servizio (in corso, terminato, fissare appuntamento).';
    	}
	    $eguidance_form->addChild(new CText($eguidance_form_lbl));
	    $href = 'user_service_detail.php?op=csv&event_token=' . $eguidance_sessionAr['event_token'];
	    $download_csv = CDOMElement::create('a', "href:$href");
	    $download_csv->addChild(new CText('download csv'));

	    $tbody_data[] = array($eguidance_date, $eguidance_type, $eguidance_form, $download_csv);
	}
    }
//    $eguidance_data = BaseHtmlLib::tableElement('',$thead_data,$tbody_data);
  }
    if (count($tbody_data) == 0) {
    	$eguidance_data = new CText('');
		// $tbody_data[] = array(new CText(translateFN('Nessuna Sessione effettuata (o non è stato compilato il modulo di valutazione, o la sessione è stata fatta senza un appuntamento)')));
    } else {
	    $eguidance_data = BaseHtmlLib::tableElement('',$thead_data,$tbody_data);
    }

  /*
   * Future appointments with this user
   *
   *
   * Potremmo avere una classe
   * $agenda = new ADAAgenda($userObj);
   * $appointments = $agenda->futureAppointmentsWithUser($tutoredUserObj->getId());
   *
  $fields_list_Ar = array('data_ora', 'titolo');
  $clause         = ' data_ora > ' . time()
                  . ' AND id_mittente='.$tutoredUserObj->getId()
                  . ' AND (flags & ' . ADA_EVENT_CONFIRMED .')';

  $sort_field     = ' data_ora desc';

  $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
  $msgs_ha = $mh->find_messages($userObj->getId(),
                                ADA_MSG_AGENDA,
                                $fields_list_Ar,
                                $clause,
                                $sort_field);
  if(AMA_DataHandler::isError($msgs_ha) || count($msgs_ha) == 0) {
   *
   *
   * NON SI PUÒ USARE PERCHÉ L'APPUNTAMENTO È SU UTENTE E ISTANZA SERVIZIO
   * La query eventuale andrebbe costruita sul titolo del messaggio che contiene il token
   *
   * Token:
   * first match: tutored user id
   * second match: tutor id
   * third match: course instance id
   * fourth match: timestamp
   *

   */

  if (!$isFutureConfirmedAppointment || count($tbody_data_confirmed_appointment)<=0) {
    $appointments_data = new CText('');
  }
  else {
    $thead_data = array(translateFN('Prossime sessioni di orientamento'), translateFN('Appointment type'), translateFN('Azioni'));
    $appointments_data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data_confirmed_appointment);
    $appointments_data->setAttribute('class', $appointments_data->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
  }

  if (!$isFutureProposedAppointment || count($tbody_data_proposed_appointment)<=0) {
    $appointments_proposed_data = new CText('');
  }
  else {
    $thead_data = array(translateFN('Appuntamenti'), translateFN('Appointment type'), translateFN('Azioni'));
    $appointments_proposed_data = BaseHtmlLib::tableElement('', $thead_data, $tbody_data_proposed_appointment);
  }

  $data = $user_data->getHtml()
        . $service_data->getHtml()
        . $eguidance_data->getHtml()
	. $appointments_data->getHtml()
	. $appointments_proposed_data->getHtml()
	;
}

$label = translateFN('user service details');
// set a default extraPath here if needed
if (!isset($extraPath)) $extraPath = '';
$label .= translateFN($extraPath);
$help  = translateFN("Details");

$home_link = CDOMElement::create('a','href:tutor.php');
$home_link->addChild(new CText(translateFN("Practitioner's home")));
$module = $home_link->getHtml() . ' > ' . $label;

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');


$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'status'    => $status,
  'path'      => $module,
  'label'     => $label,
  'data'      => $data,
  'user_avatar'=>$avatar->getHtml(),
  'user_modprofilelink' => $userObj->getEditProfilePage(),
);

/**
 * @author giorgio 11/apr/2014 16:27:21
 *
 * Force a reload of parent window, to
 * reflect saved changes right away.
 */
//$optionsAr['onload_func'] = 'reloadParent();';
$layout_dataAr['JS_filename'] = array(
            JQUERY,
            JQUERY_UI,
            JQUERY_NO_CONFLICT,
	    HTTP_ROOT_DIR.'/js/tutor/user_service_detail.js'
            );


ARE::render($layout_dataAr, $content_dataAr, NULL, (isset($optionsAr) ? $optionsAr : NULL) );
?>
