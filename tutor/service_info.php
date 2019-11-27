<?php
/**
 * Displays information about
 *
 * @package
 * @author    Stefano Penge <steve@lynxlab.com>
 * @author    Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author    Vito Modena <vito@lynxlab.com>
 * @copyright Copyright (c) 2009, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
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
if(isset($_GET['popup'])) {
  $self = 'eguidance_tutor_form';
  $href_suffix='&popup=1';
}
else {
  $self =  'default';//whoami();
  $href_suffix='';
}
include_once 'include/tutor_functions.inc.php';
TutorHelper::init($neededObjAr);
include_once 'include/eguidance_tutor_form_functions.inc.php';

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/TutorModuleHtmlLib.inc.php';

  $id_user = DataValidator::is_uinteger($_GET['id_user']);
  $id_course_instance = DataValidator::is_uinteger($_GET['id_course_instance']);

  if($id_user === FALSE || $id_course_instance === FALSE || $id_course === FALSE) {
    $errObj = new ADA_Error(NULL,
                             translateFN("Dati in input per il modulo user_servide_detail non corretti"),
                             NULL, NULL, NULL, $userObj->getHomePage());
  }


  /*
   * Service data to display
   */
    $service_infoAr = $common_dh->get_service_info_from_course($id_course);
    if(!AMA_Common_DataHandler::isError($service_infoAr)) {
      $service_data = TutorModuleHtmlLib::getServiceDataTable($service_infoAr);
    }
    else {
      $service_data = new CText('');
    }



$label = translateFN('user service details');
$help  = translateFN("Details");

$home_link = CDOMElement::create('a','href:tutor.php');
$home_link->addChild(new CText(translateFN("Practitioner's home")));
$module = $home_link->getHtml() . ' > ' . $label;

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'status'    => $status,
  'path'      => $module,
  'label'     => $label,
  'data'      => $service_data->getHtml(),
  'user_avatar'=>$avatar->getHtml(),
  'user_modprofilelink' => $userObj->getEditProfilePage(),
);

ARE::render($layout_dataAr, $content_dataAr);
