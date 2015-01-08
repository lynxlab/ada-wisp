<?php
/**
 * READ MESSAGE.
 *
 * @package		comunica
 * @author
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

$variableToClearAR = array('layout','user','course');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
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
$status = translateFN('Lettura messaggio');

if ($id_course) {
  $sess_id_course = $id_course;
}

if (isset($id_course_instance)) {
  $sess_id_course_instance = $id_course_instance;
} else $sess_id_course_instance = null;

if (isset($del_msg_id) and (!empty($del_msg_id))){
  // vito, 19 gennaio 2009, qui va in errore durante il log del messaggio
  //$res = $mh->remove_messages($sess_id_user, array($del_msg_id));
  $res = MultiPort::removeUserMessages($userObj, array($del_msg_id));
  if (AMA_DataHandler::isError($res)) {
    $errObj = new ADA_Error($msg_ha, translateFN('Errore in cancellazione messaggi'),
                             NULL, NULL, NULL,
                             'comunica/list_messages.php?status='.urlencode(translateFN('Errore in cancelllazione messaggi'))
                             );
  }
  else {
    $status = urlencode(translateFN('Cancellazione eseguita'));
  }
  header("Location: list_messages.php?status=$status");
  exit();
}

// get message content
//$msg_ha = $mh->get_message($sess_id_user, $msg_id);
$msg_ha = MultiPort::getUserMessage($userObj, $msg_id);
if (AMA_DataHandler::isError($msg_ha)){
  $errObj = new ADA_Error($msg_ha, translateFN('Errore in lettura messaggio'),
                           NULL, NULL, NULL,
                           'comunica/list_messages.php?status='.urlencode(translateFN('Errore in lettura messaggio')));
}

$mittente = $msg_ha['mittente'];
/*
 * usare $msg_ha['id_mittente'] e $sess_id_user per ottenere corso e istanza corso comuni.
 * cosa fare se entrambe gli utenti sono iscritti a due classi?
 */

$Data_messaggio = AMA_DataHandler::ts_to_date($msg_ha['data_ora'], "%d/%m/%Y - %H:%M:%S");
$oggetto        = $msg_ha['titolo'];
$destinatario   = str_replace (",", ", ", $msg_ha['destinatari']);
//$message_text   = nl2br($msg_ha['testo']);
$message_text   = $msg_ha['testo'];
$node_title = ""; // empty

$dest_encode = urlencode($mittente);
$testo       = urlencode(trim($message_text));
$oggetto_url = urlencode(trim($oggetto));

// Registrazione variabili per replay
$destinatari_replay = $mittente; //
$_SESSION['destinatari_replay'] = $destinatari_replay;

$testo_replay = trim($message_text);
$_SESSION['testo_replay'] = $testo_replay;
$titolo_replay = trim($oggetto);
$_SESSION['titolo_replay'] = $titolo_replay;

// Registrazione variabili per replay_all
$destinatari_replay_all = $mittente . "," . $destinatario; //
$_SESSION['destinatari_replay_all'] = $destinatari_replay_all;


/*
$testo_ar = explode(chr(13),  chop($message_text));
$testo = "";
foreach($testo_ar as $riga) {
  $testo .= MessageHandler::render_message_textFN($riga) ."<BR>";
}
*/

$testo = str_replace("\r\n", '<br />', $message_text);

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
  'course_title'   => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'user_name'      => $user_name,
  'user_type'      => $user_type,
  'user_avatar'    => $avatar->getHtml(),
  'level'          => $user_level,
  'mittente'       => $mittente,
  'Data_messaggio' => $Data_messaggio,
  'oggetto'        => $oggetto,
  'destinatario'   => $destinatario,
  'message_text'   => $testo,
  'status'         => $status
);
$menuOptions['del_msg_id'] = $msg_id;
ARE::render($layout_dataAr, $content_dataAr,NULL,NULL,$menuOptions);
?>