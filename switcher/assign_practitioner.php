<?php
/**
 * ASSIGN PRACTITIONER.
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
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

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
  AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR.'/include/wisp_module_init.inc.php';
$self =  'switcher';  // = switcher!

include_once 'include/'.$self.'_functions.inc.php';

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/form/phpOpenFormGen.inc.php';
include_once ROOT_DIR.'/admin/include/htmladmoutput.inc.php';

/*
 * Handle practitioner assignment
 */
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'
   && isset($id_tutor_new) && !empty($id_tutor_new)) {
  if($id_tutor_old != "no"){
    $result = $dh->course_instance_tutor_unsubscribe($id_instance, $id_tutor_old) ;
    if (AMA_DataHandler::isError($result)){
      $errObj = new WISP_Error($result, translateFN('Errore nel disassociare il practitioner dal client'));
    }
  }

  // If a practitioner has been assigned
  if($id_tutor_new != "del") {
    $result = $dh->course_instance_tutor_subscribe($id_instance, $id_tutor_new) ;
    if (AMA_DataHandler::isError($result)) {
      $errObj = new WISP_Error($result, translateFN('Errore durante assegnazione del practitioner al client'));
    }
    else {
      /*
       * The practitioner was correctly assigned to this user. Check if service
       * is not started yet, and if it is so, start the service.
       */
      $tutorObj = MultiPort::findUser($id_tutor_new);
      // FIXME: gestire errore

      $ci_info = $dh->course_instance_get($id_instance,true);
      if($ci_info['data_inizio'] == 0) {
        /*
         * First set correct start and end timestamps based on service duration
         */
        $start_ts = $dh->date_to_ts('now');
        $ci_info['data_inizio'] = $start_ts;
        // End date is automatically set by method course_instance_set
        $result = $dh->course_instance_set($id_instance, $ci_info);
        if(AMA_DataHandler::isError($result)) {
          $errObj = new WISP_Error($result, translateFN('Errore in aggiornamento dati assegnamento practitioner client'));
        }

        /*
         * Second, change user status from pre-subscribed to subscribed
         */
        $result = $dh->course_instance_students_presubscribe_get_list($id_instance);
        if(AMA_DataHandler::isError($result)) {
          $errObj = new WISP_Error($result, translateFN('Errore in ottenimento stato iscrizione utente'));
        }
        // In WISP we have only one user subscribed to a course instance
        $id_student = $result[0]['id_utente_studente'];

        $tutoredUserObj = MultiPort::findUser($id_student);
        // FIXME: gestire errore

        $result = $dh->course_instance_student_subscribe($id_instance, $id_student, WISP_STATUS_SUBSCRIBED);
        if(AMA_DataHandler::isError($result)) {
          $errObj = new WISP_Error($result, translateFN('Errore in aggiornamento stato iscrizione utente'));
        }

        /*
         * Send a message to the selected practitioner informing him of the assignment
         */
        $message_text = sprintf(translateFN('Dear practitioner, you have a new user (username: %s) assigned.'), $tutoredUserObj->getUserName());

        $message_ha = array(
          'tipo'        => WISP_MSG_MAIL,
          'data_ora'    => 'now',
          'mittente'    => $userObj->getUserName(),
          'destinatari' => array($tutorObj->getUserName()),
          'titolo'      => 'eGos: ' . translateFN('a new user has been assigned to you.'),
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
          'tipo'        => WISP_MSG_MAIL,
          'data_ora'    => 'now',
          'mittente'    => $userObj->getUserName(),
          //'destinatari' => array($userObj->getUserName()),
          'destinatari' =>   $destinatari,
          'titolo'      => 'eGos: ' . translateFN('a new user has been assigned by you.'),
          'testo'       => $message_text
        );

        $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
        $result = $mh->send_message($message_ha);
        if(AMA_DataHandler::isError($result)) {
          // FIXME: gestire errore
        }
      }
    }
  }
  header('Location: '. $userObj->getHomePage());
  exit();
}
/*
 * Display practitioner selection form
 */
else {
    
  $result = $dh->course_instance_tutor_get($id_instance) ;
  if (AMA_DataHandler::isError($result)){
    // FIXME: verificare che si venga redirezionati alla home page del'utente
    $errObj = new WISP_Error($result, translateFN('Errore in lettura tutor'));
  }

  if($result === false) {
    $id_tutor_old = "no" ;
  }
  else {
    $id_tutor_old = $result ;
  }

  // array dei tutor
  $field_list_ar = array('nome','cognome');
  $tutors_ar = $dh->get_tutors_list($field_list_ar);
  if (AMA_DataHandler::isError($tutors_ar)){
    $errObj = new WISP_Error($tutors_ar, translate('Errore in lettura dei tutor'));
  }

  // visualizzazione form di input
  $op = new htmladmoutput();
  $dati = $op->course_instance_tutor_form('assign_practitioner.php','switcher.php',$id_instance,$id_tutor_old,$id_corso,$tutors_ar);
}

$title = translateFN('WISP - assegna epractitioner');
$help   = translateFN('Da qui lo switcher WISP pu&ograve; assegnare un epractitioner ad un utente');

$status = translateFN('Assegnazione epractitioner');

$banner = include ROOT_DIR.'/include/banner.inc.php';

$content_dataAr = array(
  'dati'      => $dati,
  'menu'      => $menu,
  'banner'    => $banner,
  'help'      => $help,
  'status'    => $status,
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);
?>