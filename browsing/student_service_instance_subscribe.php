<?php
/**
 *
 * @package		Subscription 
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2009-2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.2
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'course', 'course_instance');
/**
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/include/CourseInstance.inc.php';

$courseId = DataValidator::is_uinteger($_REQUEST['id_course']);
$instanceId = DataValidator::is_uinteger($_REQUEST['id_course_instance']);
$studentId = DataValidator::is_uinteger($_REQUEST['userId']);

/*
 * Instance Object
 */
$instanceObj = new course_instance($instanceId);
$price = $instanceObj->getPrice();
$user_level = $instanceObj->getStartLevelStudent();
$service = $dh->get_course($courseId);
$service_name = $course['titolo'];

$firstname = $userObj->getFirstName();
$lastname = $userObj->getLastName();
$username = $userObj->getUserName();


// Service subscription
$status = ADA_STATUS_SUBSCRIBED;
$res = $dh->course_instance_student_subscribe_add($instanceId,$studentId,$status, $user_level);
if (!AMA_DataHandler::isError($res)){
    // Send mail to the user with his/her data.
    $switcherTypeAr = array(AMA_TYPE_SWITCHER);
    $extended_data = TRUE;
    $switcherList = $dh->get_users_by_type($switcherTypeAr, $extended_data);
    if (!AMA_DataHandler::isError($switcherList)){
        $switcher_email = $switcherList[0]['e_mail'];
    } else {
        $switcher_email = ADA_ADMIN_MAIL_ADDRESS;
    }
    $notice_mail = sprintf(translateFN('Questa è una risposta automatica. Si prega di non rispondere a questa mail. Per informazioni scrivere a %s'),$switcher_email);
    $message_ha["testo"] = $notice_mail . "\n\r\n\r";

    $message_ha["testo"] .= translateFN('Gentile') . " " . $firstname .",\r\n" . translateFN("ti sei iscritto al servizio") . " " . $service_name . "\n\r\n\r";
    $message_ha["testo"] .=  $body_mail;
    $message_ha["testo"] .= "\n\r\n\r". translateFN("Questo è l'indirizzo per accedere ai servizi: ") . "\n\r" . $http_root_dir . "\n\r";
    $message_ha["testo"] .= "\n\r". translateFN("Una volta fatto il login, potrai accedere ai servizi ai quali ti sei iscritto");
    $message_ha["testo"] .= "\n\r". translateFN("Buono studio!");
    $message_ha["testo"] .= "\n\r". translateFN("La segreteria di"). PORTAL_NAME;
    $message_ha["testo"] .= "\r\n --------\r\n";
    $mailer = new Mailer();
    $res = $mailer->send_mail($message_ha, $sender_email, $recipients_emails_ar);
}

$nodeId = $courseId.'_0';
$URL = HTTP_ROOT_DIR .'/browsing/sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$instanceId;
header ('Location: '.$URL);
