<?php
/**
 * ADMIN.
 *
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
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_ADMIN);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_ADMIN => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = admin!

include_once 'include/admin_functions.inc.php';

$log_dataAr = array();
$log_dataAr =  Multiport::log_report();


  $label = translateFN("Log report");
  
$data = CDOMElement::create('div');


$head_provider = translateFN("provider");
$head_desc_user = translateFN("utenti registrati");
$head_desc_sessions = translateFN("sessioni iniziate");
$head_desc_sessions_assigned = translateFN("utenti presi in carico");
//$head_desc_user_sessions = translateFN("utenti iscritti (comprese aree comuni)");
$head_desc_sessions_closed = translateFN("sessioni chiuse");
$head_desc_messages = translateFN("messaggi");
$head_desc_events = translateFN("appuntamenti");
$head_desc_visits = translateFN("pagine visitate");
$head_desc_chatrooms = translateFN("chat");
$head_desc_video_chatrooms = translateFN("video chat");


$thead_data = array($head_provider,$head_desc_user,$head_desc_sessions,$head_desc_sessions_assigned,$head_desc_sessions_closed,$head_desc_messages,$head_desc_events,$head_desc_visits,$head_desc_chatrooms,$head_desc_video_chatrooms);
  
$table = BaseHtmlLib::tableElement('id:table_log_report',$thead_data, $log_dataAr);  
  
/*
  $tObj = new Table();
  $tObj->initTable('1','center','0','1','100%','','','','','1','1');
  // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
  $caption = translateFN("Log report").
  $summary = "";
  $tObj->setTable($log_dataAr,$caption,$summary);
  $data = $tObj->getTable();
 * 
 */

$home_link = CDOMElement::create('a','href:admin.php');
$home_link->addChild(new CText(translateFN("Home dell'Amministratore")));
$module = $home_link->getHtml() . ' > ' . $label;

$help  = translateFN("Riepilogo attività dei provider");

$menu_dataAr = array(
);
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
//  'data'         => $data,
  'data'         => $table->getHtml(), //$data,
  'module'       => $module,
  'messages'     => $user_messages->getHtml()
);

    $layout_dataAr['JS_filename'] = array(
                    JQUERY,
                    JQUERY_DATATABLE,
                    JQUERY_DATATABLE_DATE,
                    JQUERY_NO_CONFLICT
            );

    $layout_dataAr['CSS_filename']= array(
                    JQUERY_DATATABLE_CSS
            );
  $render = null;
  $options['onload_func'] = 'initDoc()';
  /**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, $render, $options);
//ARE::render($layout_dataAr, $content_dataAr);
