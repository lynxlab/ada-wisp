<?php

/**
 * ASSIGN Tutor.
 *
 * @package
 * @author      Marco Benini
 * @author		Stefano Penge <steve@lynxlab.com>
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout', 'course_instance')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
//$self =  'switcher';  // = switcher!
include_once 'include/switcher_functions.inc.php';

require_once ROOT_DIR .'/comunica/include/ChatRoom.inc.php';
require_once ROOT_DIR .'/comunica/include/ChatDataHandler.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/TutorAssignmentForm.inc.php';
/*
 * Handle practitioner assignment
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'
        && isset($id_tutor_new) && !empty($id_tutor_new)) {

    $courseInstanceId = $_POST['id_course_instance'];
    $courseId = $_POST['id_course'];

    if ($id_tutor_old != 'no') {
        $result = $dh->course_instance_tutor_unsubscribe($courseInstanceId, $id_tutor_old);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore nel disassociare il practitioner dal client'));
        }
    }
    if ($id_tutor_new != "del") {
        $result = $dh->course_instance_tutor_subscribe($courseInstanceId, $id_tutor_new);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result, translateFN('Errore durante assegnazione del practitioner al client'));
        } else {
            
              /*
               * The practitioner was correctly assigned to this user. Check if service
               * is not started yet, and if it is so, start the service.
               */
              $tutorObj = MultiPort::findUser($id_tutor_new);
              // FIXME: gestire errore

              $errorPhase = null;
                $tutoredUserObj = MultiPort::findUser($id_student);
                // FIXME: gestire errore

                $result = $dh->course_instance_student_subscribe($id_course_instance, $id_student, ADA_STATUS_SUBSCRIBED);
                if(AMA_DataHandler::isError($result)) {
                  $errObj = new ADA_Error($result, translateFN('Errore in aggiornamento stato iscrizione utente'));
                  $errorPhase = 'reading user';
                }
              
              $ci_info = $dh->course_instance_get($id_course_instance,true);
              if($ci_info['data_inizio'] == 0) {
                /*
                 * First set correct start and end timestamps based on service duration
                 */
                $start_ts = $dh->date_to_ts('now');
                $ci_info['data_inizio'] = $start_ts;
                // End date is automatically set by method course_instance_set
                $result = $dh->course_instance_set($id_course_instance, $ci_info);
                if(AMA_DataHandler::isError($result)) {
                  $errObj = new ADA_Error($result, translateFN('Errore in aggiornamento dati assegnamento practitioner client'));
                  $errorPhase = 'phase 1';
                  
                }

                /*
                 * Second, change user status from pre-subscribed to subscribed
                 */
                $result = $dh->course_instance_students_presubscribe_get_list($id_course_instance);
                if(AMA_DataHandler::isError($result)) {
                  $errObj = new ADA_Error($result, translateFN('Errore in ottenimento stato iscrizione utente'));
                  $errorPhase = 'phase 2';
                }
                // In WISP we have only one user subscribed to a course instance
                $id_student = $result[0]['id_utente_studente'];

//                $tutoredUserObj = MultiPort::findUser($id_student);
                // FIXME: gestire errore

                $result = $dh->course_instance_student_subscribe($id_course_instance, $id_student, ADA_STATUS_SUBSCRIBED);
                if(AMA_DataHandler::isError($result)) {
                  $errObj = new ADA_Error($result, translateFN('Errore in aggiornamento stato iscrizione utente'));
                  $errorPhase = 'phase 3';
                }

                /*
                 * Send a message to the selected practitioner informing him of the assignment
                 */
                $message_text = sprintf(translateFN('Dear practitioner, you have a new user (username: %s) assigned.'), $tutoredUserObj->getUserName());

                $message_ha = array(
                  'tipo'        => ADA_MSG_MAIL,
                  'data_ora'    => 'now',
                  'mittente'    => $userObj->getUserName(),
                  'destinatari' => array($tutorObj->getUserName()),
                  'titolo'      => PORTAL_NAME . ': ' . translateFN('a new user has been assigned to you.'),
                  'testo'       => $message_text
                );

                $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
                $result = $mh->send_message($message_ha);
                if(AMA_DataHandler::isError($result)) {
                  // FIXME: gestire errore
                }

                /*
                 * Send a message to the switcher reminding him of the assignment he has done
                 */

                /* We send the message to all switchers (???) */

                $destinatari = array();
                $switcherList = $dh->get_users_by_type(array(AMA_TYPE_SWITCHER));
                if (!AMA_DataHandler::isError($switcherList)){
                   foreach($switcherList as $switcher){
                     $switcher_uname = $switcher['username']; 
                     $destinatari[] = $switcher_uname;    
                   }

                }    
                $message_text = sprintf(translateFN('Dear switcher, you have assigned the user with username: %s to the following practitioner: %s.'), $tutoredUserObj->getUserName(), $tutorObj->getUserName());

                $message_ha = array(
                  'tipo'        => ADA_MSG_MAIL,
                  'data_ora'    => 'now',
                  'mittente'    => $userObj->getUserName(),
                  //'destinatari' => array($userObj->getUserName()),
                  'destinatari' =>   $destinatari,
                  'titolo'      => PORTAL_NAME .': '. translateFN('a new user has been assigned by you.'),
                  'testo'       => $message_text
                );

                $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
                $result = $mh->send_message($message_ha);
                if(AMA_DataHandler::isError($result)) {
                  // FIXME: gestire errore
                }
              }

            
               /*
                * For each course instance, a class chatroom with the same duration
                * is made available. Every time there is an update in the course instance
                * duration, this chatroom needs to be updated too.
                */
               $id_instance = $courseInstanceId;
/*
 *                $start_time = $start_date;
               $end_time = $dh->add_number_of_days($_POST['durata'],$start_time);
//               $end_time   = $course_instance_data_before_update['data_fine'];
*/

               $id_chatroom = ChatRoom::get_class_chatroom_for_instance($id_instance,'C');

               if(!AMA_DataHandler::isError($id_chatroom)) {
                 /*
                  * An existing chatroom with id class and type = C (chat classroom)
                  * already exists, so update this chatroom owner (= tutor id).
                  */
                 $chatroomObj = new Chatroom($id_chatroom);
                 $chatroom_data['id_chat_owner'] = $id_tutor_new;

                 $result = $chatroomObj->set_chatroomFN($chatroomObj->id_chatroom, $chatroom_data);

                 if (AMA_DataHandler::isError($result)){
                    // gestire l'errore
                  }
               }
        }
    }
    
        $dialog_div = CDOMElement::create('DIV', 'id:dialog-message');
        $dialog_div->setAttribute('style', 'text-align:center');
        if ($errorPhase != null) {
            $dialogMessage = translateFN('Qualcosa è andato storto in') . ' ' .$errorPhase;
            
        } else {
//            $dialogMessage = translateFN('Utente') .' '. $tutoredUserObj->getFullName() .' ' . translateFN('assegnato a') . ' '. $tutorObj->getFullName();
            $dialogMessage = $tutorObj->getFullName() .' ' . translateFN('assegnato a') . ' '. translateFN('Utente') .' '. $tutoredUserObj->getFullName();
        } 
            
        $dialog_div->addChild(new CText($dialogMessage));
        $optionsAr['onload_func'] = 'initDoc();';
        $dataDiv = $dialog_div;
//        $optionsAr['onload_func'] = 'initDoc(\''. urlencode(translateFN('Prima seleziona un corso...')) .'\', \''. urlencode( translateFN('Nessuna Istanza trovata') ) .'\');';

//    header('Location: switcher.php?id_course=' . $courseId);
//    header('Location: list_instances.php?id_course=' . $courseId);
//    exit();
} else {
    if ($courseInstanceObj instanceof Course_instance && $courseInstanceObj->isFull()) {

        $courseId = DataValidator::is_uinteger($id_corso);
        $serviceAr = $dh->get_course($courseId);
        if (AMA_DataHandler::isError($serviceAr)) {
            // FIXME: verificare che si venga redirezionati alla home page del'utente
            $errObj = new ADA_Error($serviceAr, translateFN('Errore in lettura servizio'));
        }
        $serviceName = $serviceAr['titolo'];
        
        $result = $dh->course_instance_tutor_get($courseInstanceObj->getId());
        if (AMA_DataHandler::isError($result)) {
            // FIXME: verificare che si venga redirezionati alla home page del'utente
            $errObj = new ADA_Error($result, translateFN('Errore in lettura tutor'));
        }

        if ($result === false) {
            $id_tutor_old = 'no';
        } else {
            $id_tutor_old = $result;
        }

        // array dei tutor
        $field_list_ar = array('nome', 'cognome');
        $tutors_ar = $dh->get_tutors_list($field_list_ar);
        if (AMA_DataHandler::isError($tutors_ar)) {
            $errObj = new ADA_Error($tutors_ar, translate('Errore in lettura dei tutor'));
        }


        $tutors = array();
        $ids_tutor = array();

        if ($id_tutor_old == 'no') {
            $tutors['no'] = translateFN('Nessun tutor');
        }
        
        $js = '<script type="text/javascript">';
	$tooltips = '';
        foreach ($tutors_ar as $tutor) {
                $ids_tutor[] = $tutor[0];
                $idSingleTutor = $tutor[0];
                $nome = $tutor[1] . ' ' . $tutor[2];
                $link = CDOMElement::create('a');
                $link->setAttribute('id','tooltip'.$tutor[0]);
                $link->setAttribute('href','javascript:void(0);');
                $link->addChild(new CText($nome));
                $tutors[$tutor[0]] = $link->getHtml();

//		$tutor_monitoring = $dh->get_tutors_assigned_course_instance($ids_tutor);
		$tutor_monitoring = $dh->get_list_of_tutored_users($idSingleTutor);

                
		//create tooltips with tutor's assignments (html + javascript)
		$ul = CDOMElement::create('ul');
                $studentsId = array();
		foreach($tutor_monitoring as $k=>$v) {
			if (!empty($v)) {
                            if (!in_array($v['id_utente'], $studentsId)) {
                                array_push($studentsId, $v['id_utente']);
                                $nome_corso = $v['nome'] .' ' . $v['cognome'].' - '. $v['titolo']; //.(!empty($l['title'])?' - '.$l['title']:'');
                                $li = CDOMElement::create('li');
                                $li->addChild(new CText($nome_corso));
                                $ul->addChild($li);

                                /*
                                foreach($v as $i=>$l) {
                                        print_r($l);
                                        echo '<br />';
                                        $nome_corso = $l[2].$l[3]; //.(!empty($l['title'])?' - '.$l['title']:'');
                                        $li = CDOMElement::create('li');
                                        $li->addChild(new CText($nome_corso));
                                        $ul->addChild($li);
                                }
                                 * 
                                 */
                            }
			}
			else {
				$nome_corso = translateFN('Nessun utente');
				$li = CDOMElement::create('li');
				$li->addChild(new CText($nome_corso));
			}

		}
//                $ul->addChild($li);
                $tip = CDOMElement::create('div','id:tooltipContent'.$idSingleTutor);
//                $tip->setAttribute('class', 'toolTip');
                $tip->addChild(new CText(translateFN('Consulente assegnato ai seguenti utenti').':<br />'));
                $tip->addChild($ul);
                $tooltips.=$tip->getHtml();
                $js.= 'new Tooltip("tooltip'.$idSingleTutor.'", "tooltipContent'.$idSingleTutor.'", {DOM_uery: {parentId: "header"}, className: "tooltip", offset: {x:+15, y:0}, hook: {target:"rightMid", tip:"leftMid"}});'."\n";
             }

		$js.= '</script>';
		$tooltips.=$js;
		//end

        $data = new TutorAssignmentForm($tutors, $id_tutor_old);
        $data->fillWithArrayData(
                array(
                    'id_tutor_old' => $id_tutor_old,
                    'id_course_instance' => $courseInstanceObj->getId(),
                    'id_course' => $id_corso,
                    'id_student' => $id_user
                )
        );

        /*
         * Read User data
         */
        $tutoredUserObj = MultiPort::findUser($id_user);
        
        
          /*
           * Future appointments with this user
           *
           *
           * Potremmo avere una classe
           * $agenda = new ADAAgenda($userObj);
           * $appointments = $agenda->futureAppointmentsWithUser($tutoredUserObj->getId());
           *
           */
          $id_switcher = $userObj->getId();
          $messageTokenPart =  $id_user    . '_'
                 . $id_course_instance . '_'                  
                 . $id_switcher . '_';
                  
          $fields_list_Ar = array('data_ora', 'titolo', 'testo');
          $clause         = ' titolo like \'%' . $messageTokenPart . '%\'';
//                          . ' AND id_mittente='.$userObj->getId()
//                          . ' AND (flags & ' . ADA_EVENT_CONFIRMED .')';

          $sort_field     = ' data_ora desc';
          
          $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
          $msgs_ha = $mh->find_messages($id_switcher,
                                        ADA_MSG_SIMPLE,
                                        $fields_list_Ar,
                                        $clause,
                                        $sort_field);
          if(AMA_DataHandler::isError($msgs_ha) || count($msgs_ha) == 0) {
            $helpRequiredMessage = '';
            //$helpRequiredMessage = new CText('');
          } else { 
//              print_r();
            foreach ($msgs_ha as $singleMsg) {
                  $helpRequiredMessage = nl2br($singleMsg[2]);
              }    
          }

        $info_div = CDOMElement::create('DIV', 'id:info_div');
        $info_div->setAttribute('class', 'info_div');
        $headerSpanText = CDOMElement::create('Div','id_header_info');
        $headerSpanText->setAttribute('class', 'header_info_div');
        $serviceNameSpan = CDOMElement::create('span','class:strong_text'); 
        $serviceNameSpan->addchild(new CText($serviceName));
        $ServiceHTML = $serviceNameSpan->getHtml();
        $headerText = $tutoredUserObj->nome . ' ' . $tutoredUserObj->cognome .' '. translateFN('ha chiesto aiuto per il servizio'). ' '. $ServiceHTML. ' ';
        $headerSpanText->addChild(new CText($headerText));
        $info_div->addChild($headerSpanText);
        $SpanQuestionText = CDOMElement::create('DIV','id:question_info');
        $SpanQuestionText->addChild(new CText($helpRequiredMessage));
        $info_div->addChild($SpanQuestionText);
        
        
        /*
         * Read User help required question
         */
        
        
        
    } else {
        $data = new CText(translateFN('Classe non trovata'));
    }
    $dataDiv = CDOMElement::create('DIV', 'id:data_div');
    $formDiv = CDOMElement::create('DIV', 'id:form_div');
    $formDiv->addChild(new CText($data->getHtml()));
    $dataDiv->addChild($formDiv);
    $dataDiv->addChild($info_div);

}

$title = translateFN('Assegna il consulente alla richiesta dell\'utente');
$help = translateFN('Qui puoi assegnare un Consulente al servizio richiesto dall\'utente');
$status = translateFN('Assegnazione epractitioner');


$banner = include ROOT_DIR . '/include/banner.inc.php';

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT,
                HTTP_ROOT_DIR.'/js/switcher/assign_practitioner.js'
);

/**
 * if the jqueru-ui theme directory is there in the template family,
 * do not include the default jquery-ui theme but use the one imported
 * in the edit_user.css file instead
 */
if (!is_dir(ROOT_DIR.'/layout/'.$userObj->template_family.'/css/jquery-ui'))
{
	$layout_dataAr['CSS_filename'] = array(
			JQUERY_UI_CSS
	);
}


$content_dataAr = array(
    'data' => $dataDiv->getHtml() . $tooltips,
    'menu' => $menu,
    'banner' => $banner,
    'help' => $help,
    'status' => $status,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'course_title' => $status,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml()
);


ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);

//ARE::render($layout_dataAr, $content_dataAr);
