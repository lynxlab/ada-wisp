<?php

/**
 * List local services - this module provides list of the provider's services functionality
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
$coursesAr = $dh->get_courses_list(array('nome', 'titolo', 'descrizione'));
if(is_array($coursesAr) && count($coursesAr) > 0) {
    $thead_data = array(
       translateFN('id'),
       translateFN('codice'),
       translateFN('titolo'),
       translateFN('descrizione'),
       translateFN('azioni')
    );
    $tbody_data = array();

    $edit_img = CDOMElement::create('img', 'src:img/edit.png,alt:edit');
    $view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');
    $instances_img = CDOMElement::create('img', 'src:img/student.png,alt:view');

    foreach($coursesAr as $course) {
        $courseId = $course[0];

//        $edit_link = BaseHtmlLib::link("edit_lservice.php?id_course=$courseId", $edit_img->getHtml());
        $edit_link = BaseHtmlLib::link("edit_lservice.php?id_course=$courseId", translateFN('Edit'));
        //$view_link = BaseHtmlLib::link("view_course.php?id_course=$courseId", $view_img->getHtml());
        $instances_link = BaseHtmlLib::link("list_users_service.php?id_course=$courseId", translateFN('Users'));
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

        $tbody_data[] = array($courseId, $course[1],  $course[2], $course[3], $actions);
    }
    $data = BaseHtmlLib::tableElement('class:sortable', $thead_data, $tbody_data);
} else {
    $data = new CText(translateFN('Non sono stati trovati corsi'));
}

$label = translateFN('Lista servizi');
$help = translateFN('Da qui il provider admin può vedere la lista dei servizi erogati dal provider');
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