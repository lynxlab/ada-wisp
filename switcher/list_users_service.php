<?php

/**
 * List users for service - this module provides list of users for service functionality
 * 
 * 
 * @package		
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2010, Lynx s.r.l.
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
    AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
//$self = whoami();
$self = 'switcher';  // = admin!

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */
$course_id = $_GET['id_course'];
$service_infoAR = $dh->get_course($course_id);
$service_name = $service_infoAR['nome'];
$service_title = $service_infoAR['titolo'];

$usersAR = $dh->course_users_instance_get($course_id);
if(is_array($usersAR) && count($usersAR) > 0) {
    $thead_data = array(
       translateFN('id service requested'),
       translateFN('id user'),
       translateFN('name'),
       translateFN('surname'),
       translateFN('username'),
       translateFN('request date'),
       translateFN('status'),
       translateFN('practitioner')
    );
    $tbody_data = array();
//print_r($usersAR);
    $num_users = 0;
    foreach($usersAR as $user) {
        $num_users ++;
        $id_course_instance = $user['id_istanza_corso'];
        $id_user = $user['id_utente'];
        $name = $user['nome'];
        $surname = $user['cognome'];
        $username = $user['username'];
        $request_date = AMA_DataHandler::ts_to_date($user['data_inizio']);
        switch ($user['status']) {
            case ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED:
                $status = translateFN('undefined');
                break;
            case ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED:
                $status = translateFN('requested');
                break;
            case ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED:
                $status = translateFN('started');
                break;
            case ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED:
                $status = translateFN('completed');
                break;
        }
        if ($status != ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED) {
            $tutor = $dh->course_instance_tutor_info_get($id_course_instance);
            //print_r($tutor);
        }

        $href = 'zoom_user.php?id='.$id_user;
        $user_link = CDOMElement::create('a', "href:$href");
        $user_link->addChild(new CText($username));

        $href = HTTP_ROOT_DIR.'/info.php?id_course='.$user_registration['id_corso'];
        $service_link = CDOMElement::create('a',"href:$href");
        $service_link->addChild(new CText(translateFN($user_registration['titolo'])));

        $href = 'assign_practitioner.php?id_corso='.$course_id.'&id_course_instance='.$id_course_instance.'&id_user='.$id_user;
        $epractitioner_link = CDOMElement::create('a', "href:$href");
        if (isset($tutor[0]['id_utente_tutor']) AND $tutor[0]['id_utente_tutor'] != '') {
            $epractitioner_link->addChild(new CText($tutor[0]['username'].' ('.$tutor[0]['nome'] .' '.$turor[0]['cognome_t'].')'));
        } else {
            $epractitioner_link->addChild(new CText(translateFN('Assegna')));
        }


//        $edit_link = BaseHtmlLib::link("edit_lservice.php?id_course=$courseId", $edit_img->getHtml());
        $edit_link = BaseHtmlLib::link("edit_lservice.php?id_course=$courseId", translateFN('Edit'));
        //$view_link = BaseHtmlLib::link("view_course.php?id_course=$courseId", $view_img->getHtml());
        $instances_link = BaseHtmlLib::link("list_users.php?id_course=$courseId", translateFN('Users'));
//        $instances_link = BaseHtmlLib::link("list_users.php?id_course=$courseId", $instances_img->getHtml());
        //$add_instance_link = BaseHtmlLib::link("add_instance.php?id_course=$courseId", translateFN('Add instance'));
        $delete_course_link = BaseHtmlLib::link("delete_lservice.php?id_course=$courseId", translateFN('Delete'));
        $actions = BaseHtmlLib::plainListElement('class:inline_menu',
                array(
                    $edit_link,
                    //$view_link,
                    $instances_link,
                    //$add_instance_link,
                    $delete_course_link
                )
        );

        $tbody_data[] = array($id_course_instance, $id_user,  $name, $surname, $user_link, $request_date, $status, $epractitioner_link);
    }
    $data = BaseHtmlLib::tableElement('class:sortable', $thead_data, $tbody_data);
} else {
    $data = new CText(translateFN('Non sono stati trovati utenti iscritti al servizio'));
}

$label = translateFN('service') . ': '. $service_name . ' - '. $service_title;
$help = translateFN('Da qui il provider admin può vedere la lista degli utenti che hanno rechiesto il servizio') .' - '.
        translateFN('Total users') . ': '. $num_users;
//$chatrooms_link = '<a href="'.HTTP_ROOT_DIR . '/comunica/list_chatrooms.php">'. translateFN('Lista chatrooms');
$menu_01 = '<a href="list_lservices.php">'.translateFN('Vedi servizi').'</a>';
$menu_02 = '<a href="add_lservice.php">'.translateFN('Aggiungi servizio').'</a>';

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'course_title' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => $module,
    'menu_01' => $menu_01,
    'menu_02' => $menu_02,//    'ajax_chat_link' => $chatrooms_link,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);