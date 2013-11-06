<?php
/**
 * list_messages.php
 *
 * @package		comunica
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2001-2011, Lynx s.r.l.
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

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT      => array('layout'),
  AMA_TYPE_TUTOR        => array('layout'),
  AMA_TYPE_SWITCHER     => array('layout')
);


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami();

include_once 'include/comunica_functions.inc.php';

/*
 * YOUR CODE HERE
 */

$user_id = $userObj->getId();

if (!isset($op)) {
  $op = 'default';
}

if(isset($status)) {
  $status = urldecode($status);
}

$title = translateFN('ADA - Lista messaggi');


// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

$online_users_listing_mode = 2;
if (isset($sess_id_course_instance) && !empty($sess_id_course_instance)) {
  $online_users = ADALoggableUser::get_online_usersFN($sess_id_course_instance,$online_users_listing_mode);
}
else {
  $online_users = '';
}
// CHAT, BANNER etc

$banner = include ROOT_DIR.'/include/banner.inc.php';

// Has the form been posted?

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // build array of messages ids to be set as read
  if (count($form['read'])) {
    $to_set_as_read_ar = $form['read'];
  }
  else {
    $to_set_as_read_ar = array();
  }

  // set all read messages
  //$res = $mh->set_messages($user_id, $to_set_as_read_ar, 'R');
  $res = MultiPort::markUserMessagesAsRead($userObj,$to_set_as_read_ar);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res, translateFN('Errore'));
  }

  // set all unread messages
  // first, get all the messages in the user's spool
  //$msgs_ha = $mh->get_messages($user_id, ADA_MSG_SIMPLE, array('read_timestamp'));
  $msgs_ha = MultiPort::getUserMessages($userObj);
  if (AMA_DataHandler::isError($msgs_ha)){
    $errObj = new ADA_Error($msgs_ha, translateFN('Errore in lettura messaggi utente'));
  }

  // then fill the array of ids to set as unread

  $to_set_as_unread_ar = array();
  foreach ($msgs_ha as $pointer => $msgs_tester_Ar) {

      $id_tester_Ar = $common_dh->get_tester_info_from_pointer($pointer);
      if (AMA_DataHandler::isError($id_tester_Ar)){
          $errObj = new ADA_Error($id_tester_Ar, translateFN('Errore'));
      } else {

          foreach ($msgs_tester_Ar as $msg_id => $msg_ar){
            $msg_id_tester = $id_tester_Ar[0] . '_' . $msg_id;
            if (!in_array($msg_id_tester, $to_set_as_read_ar)) {
              $to_set_as_unread_ar[] = $msg_id_tester;
            }
          }
      }
  }

  // last, invoke, the set_messages method
  //$res = $mh->set_messages($user_id, $to_set_as_unread_ar, 'N');
  $res =MultiPort::markUserMessagesAsUnread($userObj, $to_set_as_unread_ar);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res, translateFN('Errore'));
  }

  // build array of messages ids to be removed
  if (count($form['del'])) {
    $to_remove_ar = $form['del'];
  }
  else {
    $to_remove_ar = array();
  }

  // manage messages removal
  //$res = $mh->remove_messages($user_id, $to_remove_ar);
  $res = MultiPort::removeUserMessages($userObj, $to_remove_ar);
  if (AMA_DataHandler::isError($res)){
    $errObj = new ADA_Error($res, translateFN('Errore durante la cancellazione dei messaggi'));
  }
}


// remove single message if requested
if (isset($del_msg_id) and !empty($del_msg_id)) {
  //$res = $mh->remove_messages($user_id, array($del_msg_id));
  $res = MultiPort::removeUserMessages($userObj, array($del_msg_id));
  if (AMA_DataHandler::isError($res)) {
    $errObj = new ADA_Error($res, translateFN('Errore durante la cancellazione del messaggio'));
  }
}

// analyze the sorting info
if (!isset($sort_field)) {
  $sort_field = "data_ora desc";
}
elseif($sort_field == "data_ora") {
  $sort_field .= " desc";
}
elseif($sort_field == "titolo") {
  $sort_field .= " asc";
}
else {
  $sort_field .= " asc, data_ora desc";
}

$testers_dataAr = MultiPort::getTestersPointersAndIds();
if(isset($_GET['messages']) && $_GET['messages'] == 'sent') {
  $dataAr   = MultiPort::getUserSentMessages($userObj);
  $messages = CommunicationModuleHtmlLib::getSentMessagesAsForm($dataAr, $testers_dataAr);
  $label   = translateFN('Messaggi inviati');

  $menu_02 = CDOMElement::create('li', 'class:received');
  $link = CDOMElement::create('a', 'href:list_messages.php?messages=received');
  $link->addChild(new CText(translateFN('Messaggi ricevuti')));
  $menu_02->addChild($link);

}
else {
  $dataAr   = MultiPort::getUserMessages($userObj);
  // print_r($messages);
  $messages = CommunicationModuleHtmlLib::getReceivedMessagesAsForm($dataAr, $testers_dataAr);
  $label   = translateFN('Messaggi ricevuti');

  $menu_02 = CDOMElement::create('li', 'class:sent');
  $link = CDOMElement::create('a', 'href:list_messages.php?messages=sent');
  $link->addChild(new CText(translateFN('Messaggi inviati')));
  $menu_02->addChild($link);
}

$node_title = ""; // empty
$menu_01 = "<a href=\"send_message.php\">" . translateFN("Scrivi") . "</a>";
$menu_03 = "";

// FIXME: verificare se ha senso in ADA
if (!isset($course_title)) {
  $course_title = "";
}
else {
  $course_title = '<a href="../browsing/main_index.php">'.$course_title.'</a>';
}

if (!isset($status)) {
  $status = "";
}

if (!isset($chat_link)) {
  $chat_link = "";
}
$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
  'chat_link'    => $chat_link,
  'banner'       => $banner,
  'course_title' => $course_title,
  'go_back'      => $go_back,
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'user_avatar'  => $avatar->getHtml(),
  'messages'     => $messages->getHtml(),
  'status'       => $status,
  'chat_users'   => $online_users,
  'label'        => $label,
//  'label'        => $label->getHtml(),
  'menu_01'      => $menu_01,
  'menu_02'      => $menu_02->getHtml(),
  'menu_03'      => $menu_03
);

ARE::render($layout_dataAr, $content_dataAr);