<?php
/**
 * TUTOR.
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
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = tutor!

include_once 'include/'.$self.'_functions.inc.php';

/*
 * YOUR CODE HERE
 */

include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';

if (!isset($_GET['mode'])) {
  $mode = "load";
}
else {
  $mode = $_GET['mode'];
}

/*
 * Cosa deve esserci:
 * 1. agenda del giorno
 * 2. messaggi eventuali dello switcher del tester
 * 3. lista degli utenti assegnati all'EP
 * 4. titoli di messaggi di altri utenti
 *
 * nel menu:
 * messaggeria, agenda
 */

/*
 * 3. Lista degli utenti assegnati all'EP
 */

$clients_list = $dh->get_list_of_tutored_users($userObj->getId());

$thead_data = array(translateFN('utente'),translateFN('azioni'),translateFN('servizio'),translateFN('stato'),translateFN('data inizio'),translateFN('durata servizio'), translateFN('data fine'));
$tbody_data = array();
if (is_array($clients_list) && sizeof($clients_list) > 0) {


  $user_history_link_label = translateFN('View service status');
  $appointment_link_label  = translateFN('Proponi appuntamento');
  $status_opened_label     = translateFN('In corso');
  $status_closed_label     = translateFN('Terminato');
  $eguidance_session_summary_link_label = translateFN('Eguidance summary');

  foreach($clients_list as $user_data) {
    $id_course_instance = $user_data['id_istanza_corso'];
//	$url = HTTP_ROOT_DIR.'/comunica/send_event_proposal.php?id_user='.$user_data['id_utente'];
//	$onclick = "openMessenger('$url',800,600);";
/* mod: 28/06 zoom utente */
// dettagli utente:
    $href = 'zoom_user.php?id='.$user_data['id_utente'];
    // $user_link = CDOMElement::create('a');
     $user_link = CDOMElement::create('a', "href:$href");
/* end mod */    
//    $user_link->setAttribute('href','#');
//    $user_link->setAttribute('onclick',$onclick);
    $user_link->addChild(new CText($user_data['nome'] . ' ' . $user_data['cognome']));

    $id_course = $user_data['id_corso'];
    $id_node   = $id_course.'_'.ADA_DEFAULT_NODE;
    $href = HTTP_ROOT_DIR.'/browsing/view.php?id_course='.$id_course.'&id_node='.$id_node.'&id_course_instance='.$id_course_instance;
    $service_link = CDOMElement::create('a',"href:$href");
    $service_link->addChild(new CText(translateFN($user_data['titolo'])));
    $current_timestamp = time();

    $user_history_link = CDOMElement::create('a', 'href:user_service_detail.php?id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance);
    $user_history_link->addChild(new CText($user_history_link_label));


    if($user_data['data_inizio'] > 0 && $user_data['data_fine'] > 0
       && $current_timestamp > $user_data['data_inizio']
       && $current_timestamp < $user_data['data_fine']) {
      $status = $status_opened_label;

      $url = HTTP_ROOT_DIR.'/comunica/send_event_proposal.php?id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance;
      $onclick = "openMessenger('$url',800,600);";
      $appointment_link = CDOMElement::create('a');
      $appointment_link->setAttribute('href','#');
      $appointment_link->setAttribute('onclick',$onclick);
      $appointment_link->addChild(new CText($appointment_link_label));


      $href = 'eguidance_sessions_summary.php?id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance;
      $eguidance_session_summary_link = CDOMElement::create('a', "href:$href");
      $eguidance_session_summary_link->addChild(new CText($eguidance_session_summary_link_label));

      $actions = BaseHtmlLib::plainListElement('class:actions',array($appointment_link, $eguidance_session_summary_link, $user_history_link));

    }
    else {
      $status = $status_closed_label;

      $href = 'eguidance_sessions_summary.php?id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance;
      $eguidance_session_summary_link = CDOMElement::create('a', "href:$href");
      $eguidance_session_summary_link->addChild(new CText($eguidance_session_summary_link_label));

      $actions = BaseHtmlLib::plainListElement('class:actions', array($eguidance_session_summary_link, $user_history_link));
    }

    $tbody_data[] = array(
      $user_link,
      $actions,
      $service_link,
      $status,
      ts2dFN($user_data['data_inizio']),
      $user_data['durata'],
      ts2dFN($user_data['data_fine'])
    );
  }
  $table = BaseHtmlLib::tableElement('class:sortable',$thead_data,$tbody_data);
  $data  = $table->getHtml();
}
else {
  /*
   * errore nell'ottenimento dei dati relativi agli utenti
   */
  $data = translateFN('Non ci sono utenti assegnati');
}
//$online_users_listing_mode = 2;
//$online_users = WISPLoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


$banner = include ROOT_DIR.'/include/banner.inc.php';

//$questionaire = "<a href=http://egos.ict.bg/ESMISProject/Questionnaires/Questionnaire.aspx?Id=".$sess_id_user. "&amp;Code=Q001&amp;Cript=false";
//$questionaire .= " target=\"_blank\" title=\"".translateFN("Questionaire")."\">".translateFN("Questionaire") ."</a>";

$questionaire_url = urlencode("http://egos.ict.bg/ESMISProject/Questionnaires/Questionnaire.aspx?Id=".$sess_id_user. "&Code=Q001&Cript=false");
$questionaire = "<a href=$http_root_dir/browsing/external_link.php?url=".$questionaire_url;
$questionaire .= " target=\"_blank\" title=\"".translateFN("Questionaire")."\">".translateFN("Questionaire") ."</a>";

$content_dataAr = array(
  'banner'          => $banner,
  'user_name'       => $user_name,
  'user_type'       => $user_type,
  'level'           => $user_level,
  'messages'        => $user_messages->getHtml(),
  'agenda'          => $user_agenda->getHtml(),
  'events'          => $user_events->getHtml(),
  'events_proposed' => $user_events_proposed->getHtml(),
  'course_title'    => translateFN("Practitioner's home"),
  'dati'            => $data,
//  'menu_01'         => $questionaire,
  'menu_02'         => '',
  'menu_03'         => '',
  'menu_04'         => '',
  'menu_05'         => '',
  'menu_06'         => '',
  'menu_07'         => '',
  'menu_08'         => ''
);


/**
* Sends data to the rendering engine
*/
ARE::render($layout_dataAr,$content_dataAr);
?>