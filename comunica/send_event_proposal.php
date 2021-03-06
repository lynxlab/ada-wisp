<?php
/**
 * SEND EVENT PROPOSAL.
 *
 * @package		comunica
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
// ini_set('display_errors', '0'); error_reporting(E_ALL);
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_STUDENT => array('layout')
);


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';

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
ComunicaHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */
//include_once ROOT_DIR.'/include/HtmlLibrary/CommunicationModuleHtmlLib.inc.php'; //incluso da comunica_functions

//$success    = HTTP_ROOT_DIR.'/comunica/list_events.php';
//$error_page = HTTP_ROOT_DIR.'/comunica/send_event.php';

$includeJS = false;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  /*
   * Controllo validita' sui dati in arrivo dal form
   */

  if(isset($_SESSION['event_msg_id'])) {
    $previous_proposal_msg_id = $_SESSION['event_msg_id'];
    $event_token = '';
  }
  else {
    /*
     * Costruiamo qui l'identificatore della catena di proposte che portano a
     * fissare un appuntamento.
     */
    $event_token = ADAEventProposal::generateEventToken($id_user, $userObj->getId(), $id_course_instance);
  }

  /*
   * Validazione dei dati: le date proposte devono essere valide e non devono essere antecedenti
   * a quella odierna (come timestamp)
   */
  $errors = array();

  if(DataValidator::validate_not_empty_string($subject) === FALSE) {
    $errors['subject'] = ADA_EVENT_PROPOSAL_ERROR_SUBJECT;
  }
  
  for ($i=0;$i<MAX_PROPOSAL_COUNT;$i++)
  {
  	if(($value = ADAEventProposal::canProposeThisDateTime($userObj, $date[$i], $time[$i], $sess_selected_tester)) !== TRUE) {
  		$errors['date'.($i+1)] = $value;
  	}
  	$datetimesAr[$i] = array( 'date'=>$date[$i], 'time'=>$time[$i]  );
  }

  $message_content = ADAEventProposal::generateEventProposalMessageContent($datetimesAr, $id_course_instance, $notes);

  if(count($errors) > 0) {
    $data = array(
      'testo'  => $message_content,
      'titolo' => $subject,
      'flags'  => $type
    );
    $includeJS = true;
    $form = CommunicationModuleHtmlLib::getEventProposalForm($id_user, $data, $errors,$sess_selected_tester);
  }
  else {
    /*
	 * If we are ready to send the message, we can safely unset $_SESSION['event_msg_id'])
     */
    unset($_SESSION['event_msg_id']);

    $mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));

    $addresseeObj = MultiPort::findUser($id_user);



    $message_ha = array(
      'tipo'        => ADA_MSG_AGENDA,
      'flags'       => ADA_EVENT_PROPOSED|$type,
      'mittente'    => $user_uname,
      'destinatari' => array($addresseeObj->username),
      'data_ora'    => 'now',
      'titolo'      => ADAEventProposal::addEventToken($event_token, $subject),
      'testo'       => $message_content
    );

    $res = $mh->send_message($message_ha);

    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
    }

    /*
     * If there aren't errors, redirect the user to his agenda
     */
      /*
     * SE ABBIAMO INVIATO UNA MODIFICA AD UNA PROPOSTA DI APPUNTAMENTO,
     * LA PROPOSTA PRECEDENTE DEVE ESSERE MARCATA COME CANCELLATA IN
     * DESTINATARI MESSAGGI PER L'UTENTE PRACTITIONER
     */
    if(isset($previous_proposal_msg_id)) {
      MultiPort::removeUserAppointments($userObj, array($previous_proposal_msg_id));
    }

    /*
     * Inviamo una mail all'utente in cui lo informiamo del fatto che il
     * practitioner ha inviato delle nuove proposte
     */
    $admtypeAr = array(AMA_TYPE_ADMIN);
    $admList = $common_dh->get_users_by_type($admtypeAr);
    if (!AMA_DataHandler::isError($admList)){
      $adm_uname = $admList[0]['username'];
    } else {
      $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
    }
    $clean_subject = ADAEventProposal::removeEventToken($subject);
    $message_content = sprintf(translateFN('Gentile %s, il tuo orientatore %s ti ha mandato una proposta di appuntamento %s.'),
    		$addresseeObj->getFullName(),  $userObj->getFullName(), $clean_subject);
    $message_ha = array(
      'tipo'        => ADA_MSG_MAIL,
      'mittente'    => $adm_uname,
      'destinatari' => array($addresseeObj->username),
      'data_ora'    => 'now',
      'titolo'      => PORTAL_NAME . ': '. translateFN('new event proposal dates'),
      'testo'       => $message_content
    );
    $res = $mh->send_message($message_ha);
    if (AMA_DataHandler::isError($res)){
      $errObj = new ADA_Error($res,translateFN('Impossibile spedire il messaggio'),
      NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
    }

    $text = translateFN("La proposta di appuntamento è stata inviata con successo all'utente")." ". $addresseeObj->getFullName() . ".";
    $form = CommunicationModuleHtmlLib::getOperationWasSuccessfullView($text);
    //header('Location: '.HTTP_ROOT_DIR.'/comunica/list_events.php');
    //exit();
  }
}
else {

		
  if(isset($msg_id)) {
    $data = MultiPort::getUserAppointment($userObj, $msg_id);
    

        
    if($data['flags'] & ADA_EVENT_PROPOSAL_OK) {
      /*
       * The user accepted one of the three proposed dates for the appointment.
       * E' UN CASO CHE NON SI PUO' VERIFICARE, visto che vogliamo che l'appuntamento
       * venga inserito non appena l'utente accetta una data porposta dal practitioner
       */
      $form = CommunicationModuleHtmlLib::getConfirmedEventProposalForm($data);
    }
    else {
      /*
       * The user did not accept the proposed dates for the appointment
       */
      $_SESSION['event_msg_id'] = $msg_id;
      $id_user = $data['id_mittente'];
      $errors = array();
      $includeJS = true;
      $form = CommunicationModuleHtmlLib::getEventProposalForm($id_user, $data, $errors, $sess_selected_tester);
    }
  }
  else {
    /*
     * Build the form used to propose an event. Da modificare in modo da passare
     * eventualmente il contenuto dei campi del form nel caso si stia inviando
     * una modifica ad una proposta di appuntamento.
     */
    $errors = array();
    $data = array();
    $includeJS = true;
  	$form = CommunicationModuleHtmlLib::getEventProposalForm($id_user, $data, $errors, $sess_selected_tester);
  }     
}

$title = translateFN('Invia proposta di appuntamento');

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'user_avatar'    => $avatar->getHtml(),
  'titolo'         => $titolo,
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'status'         => $err_msg,
  'data'	   => $form->getHtml() . $dateObj,
  'label'	   => $title
);

$layout_dataAr ['JS_filename'] = array (
		JQUERY,
		JQUERY_UI,
		JQUERY_UNIFORM );

/**
 * if the jqueru-ui theme directory is there in the template family,
 * import it.
 * Else get the standard one
 */
$jqueryLayoutCSS = ROOT_DIR . '/layout/' . $userObj->template_family . '/css/jquery-ui/jquery-ui-1.10.3.custom.min.css';
$layout_dataAr ['CSS_filename'] = array (
		((is_file ( $jqueryLayoutCSS )) ? $jqueryLayoutCSS : JQUERY_UI_CSS),		
		JQUERY_UNIFORM_CSS
		// ROOT_DIR . '/js/include/jquery/fullcalendar/fullcalendar.print.css'
);

if ($includeJS)
{	
	// NOTE: if i18n file is not found it'll be discarded by the rendering engine
	array_push($layout_dataAr['JS_filename'], ROOT_DIR . '/js/include/jquery/fullcalendar/fullcalendar.js');
	array_push($layout_dataAr['JS_filename'], ROOT_DIR . '/js/include/jquery/fullcalendar/i18n/fullcalendar.' . $_SESSION ['sess_user_language'] . '.js');
	array_push($layout_dataAr['JS_filename'], ROOT_DIR . '/js/include/jquery/fullcalendar/gcal.js');			

	if (isset($data))
		$datetimesAr = ADAEventProposal::extractDateTimesFromEventProposalText($data['testo']);
	else $datetimesAr = '';
	
	for ($i=0; $i<MAX_PROPOSAL_COUNT; $i++) $inputProposalNames[$i] = translateFN('Proposta').' #'.($i+1);

	array_push($layout_dataAr['CSS_filename'], ROOT_DIR . '/js/include/jquery/fullcalendar/fullcalendar.css' );
	
	$optionsAr ['onload_func'] = 'initDoc(\''.htmlentities(json_encode($datetimesAr)).'\',\''.htmlentities(json_encode($inputProposalNames)).'\','.MAX_PROPOSAL_COUNT.');';	
}
else
{
	$optionsAr ['onload_func'] = 'initDoc();';
}

array_push($layout_dataAr['JS_filename'], JQUERY_NO_CONFLICT);

ARE::render($layout_dataAr, $content_dataAr, NULL, (isset($optionsAr) ? $optionsAr : NULL));
?>