<?php
/**
 * Import users - this module provides import users from CSV functionality
 * 
 * @package
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
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
$self = whoami();

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */
$data = CDOMElement::create('div','id:import_users_container');

require_once(ROOT_DIR.'/include/Forms/ImportUsersForm.inc.php');
$theForm = new FormImportUsers('import_users');
$theForm->doNotUniform();
$data->addChild(new CText($theForm->getHtml()));

$importDIV = CDOMElement::create('div','id:import_users_steptwo');
$importDIV->addChild(CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/js/include/jquery/ui/images/ui-anim_basic_16x16.gif'));
$importSpan = CDOMElement::create('span','class:importtext');
$importSpan->addChild(new CText('Importazione in corso...'));
$importDIV->addChild($importSpan);

$finalButtonDIV = CDOMElement::create('div','id:import_users_button');
$finalButton = CDOMElement::create('button','type:button,class:finalbutton');
$finalButton->setAttribute('onclick','document.location.href=\'preassign_practitioner.php\'');
$finalButton->addChild(new CText('Vai al modulo di preassegnazione'));
$finalButtonDIV->addChild($finalButton);
$data->addChild($finalButtonDIV);

$data->addChild($importDIV);


$label = translateFN('Importa utenti');
$help = translateFN('Da qui il provider admin può importare degli utenti da un file CSV');

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'edit_profile'=>$userObj->getEditProfilePage(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml(),
    'user_avatar'=>$avatar->getHtml(),
	'user_modprofilelink' => $userObj->getEditProfilePage()
);
$layout_dataAr['JS_filename'] = array(		
	JQUERY,
	JQUERY_UI,
	JQUERY_NO_CONFLICT,
	ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.js'		
);
$layout_dataAr['CSS_filename']= array(
	JQUERY_UI_CSS,
	ROOT_DIR.'/js/include/jquery/pekeUpload/pekeUpload.css'
);
$render = null;
$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));
$optionsAr['onload_func'] = 'initDoc('.$maxFileSize.');';
ARE::render($layout_dataAr, $content_dataAr, $render, $optionsAr);

