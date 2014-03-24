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
       translateFN('livello servizio'),
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
        if ($courseId == PUBLIC_COURSE_ID_FOR_NEWS) continue;
        
        $serviceInfo = $common_dh->get_service_info_from_course($courseId);
		$isServiceCommonLevel = false;
        if (!AMA_DB::isError($serviceInfo))
        {
        		switch ($serviceInfo[3])
        		{
        			case ADA_SERVICE_HELP:
        				$serviceLevelTxt = translateFN('Help per studente');
        				break;
        			case ADA_SERVICE_COMMON:
        				$serviceLevelTxt = translateFN('Area di interazione per utenti registrati');
        				$isServiceCommonLevel = true;
        				break;
        			case ADA_SERVICE_COMMON_STUDENT:
        				$serviceLevelTxt = translateFN('Area comune per studenti');
        				$isServiceCommonLevel = true;
        				break;
        			case ADA_SERVICE_COMMON_TUTOR:
        				$serviceLevelTxt = translateFN('Area riservata ai tutor');
        				$isServiceCommonLevel = true;
        				break;
        			default:
        				$serviceLevelTxt = translateFN("N/A");
        				break;
        		}
        }
        else $serviceLevelTxt = translateFN("N/A");
        
        if ($isServiceCommonLevel && $dh->course_has_instances($courseId))
        {
        	$instancesList = $dh->course_instance_get_list(null, $courseId);
        	// get the first instance id as we're dealing with single instances courses
        	$instanceId = intval($instancesList[0][0]);
        	unset ($instancesList);
        } else $instanceId = -1;

//        $edit_link = BaseHtmlLib::link("edit_lservice.php?id_course=$courseId", $edit_img->getHtml());
        $edit_link = BaseHtmlLib::link("edit_lservice.php?id_course=$courseId", translateFN('Edit'));
        //$view_link = BaseHtmlLib::link("view_course.php?id_course=$courseId", $view_img->getHtml());
        
        // if service is common and has the instance, the 'users' link will be different
        if($isServiceCommonLevel && $instanceId!==-1)
        {
        	$queryString = "?id_course=$courseId&id_course_instance=$instanceId";
        	$instances_href = "course_instance.php".$queryString;
        	$assign_practitioner_link = BaseHtmlLib::link("assign_tutor.php".$queryString, translateFN('Orientatore'));
        }
        else
        {
        	$instances_href = "list_users_service.php?id_course=$courseId";
        	$assign_practitioner_link = null;
        }
        $instances_link = BaseHtmlLib::link($instances_href, translateFN('Users'));        
//        $instances_link = BaseHtmlLib::link("list_users.php?id_course=$courseId", $instances_img->getHtml());
        //$add_instance_link = BaseHtmlLib::link("add_instance.php?id_course=$courseId", translateFN('Add instance'));
        $delete_course_link = BaseHtmlLib::link("delete_lservice.php?id_course=$courseId", translateFN('Delete'));
        
        // build up the actions array
        $actionsArr = array ($edit_link,$instances_link);
        if (!is_null($assign_practitioner_link)) $actionsArr[] = $assign_practitioner_link;
        $actionsArr[] = $delete_course_link;
        
        $actions = BaseHtmlLib::plainListElement('class:inline_menu', $actionsArr);

        $tbody_data[] = array($courseId, $course[1], $serviceLevelTxt, $course[2], $course[3], $actions);
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

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

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
    'messages' => $user_messages->getHtml(),
    'user_avatar'=>$avatar->getHtml(),
	'user_modprofilelink' => $userObj->getEditProfilePage()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename']= array(
		JQUERY_DATATABLE_CSS
);

$render = null;
$options['onload_func'] = 'initListLservices();';

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, $render, $options);