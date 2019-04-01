<?php
/**
 * File add_course.php
 *
 * The switcher can use this module to create a new course.
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2012, Lynx s.r.l.
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
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
SwitcherHelper::init($neededObjAr);

include_once ROOT_DIR . '/services/include/NodeEditing.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/CourseModelForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $providerAuthors = $dh->find_authors_list(array('username'), '');
    $authors = array();
    foreach($providerAuthors as $author) {
        $authors[$author[0]] = $author[1];
    }

    $availableLanguages = Translator::getSupportedLanguages();
    $languages = array();
    foreach($availableLanguages as $language) {
        $languages[$language['id_lingua']] = $language['nome_lingua'];
    }

    $form = new CourseModelForm($authors,$languages);
    $form->fillWithPostData();
    if ($form->isValid()) {
        $course = array(
            'nome' => $_POST['nome'],
            'titolo' => $_POST['titolo'],
            'descr' => $_POST['descrizione'],
            'd_create' => ts2dFN(time()),//$_POST['data_creazione'],
            'd_publish' => isset($_POST['data_pubblicazione']) ? $_POST['data_pubblicazione'] : null,
            'id_autore' => $_POST['id_utente_autore'],
            'id_nodo_toc' => $_POST['id_nodo_toc'],
            'id_nodo_iniziale' => $_POST['id_nodo_iniziale'],
            'media_path' => $_POST['media_path'],
            'id_lingua' => $_POST['id_lingua'],
            'static_mode' => $_POST['static_mode'],
            'crediti' => $_POST['crediti'],
            'duration_hours' => $_POST['duration_hours'],
            'service_level' => $_POST['service_level']
        );

        $id_course = $dh->add_course($course);
        if(!AMA_DataHandler::isError($id_course)) {
          $node_data = array(
            'id' => $id_course .'_'.$_POST['id_nodo_iniziale'],
            'name' => $_POST['titolo'],
            'type' => ADA_GROUP_TYPE,
            'id_node_author' => $_POST['id_utente_autore'],
            'id_nodo_parent' => null,
            'parent_id' => null,
            'text' => $_POST['descrizione'],
            'id_course'=> $id_course
          );
          $result = NodeEditing::createNode($node_data);
          if(AMA_DataHandler::isError($result)) {
              //
          }

          // add a row in common.servizio
          $service_dataAr = array(
            'service_name' => $_POST['titolo'],
            'service_description' => $_POST['descrizione'],
            'service_level' => $_POST['service_level'],
            'service_duration'=> 0,
            'service_min_meetings' => 0,
            'service_max_meetings' => 0,
            'service_meeting_duration' => 0
          );
          $id_service = $common_dh->add_service($service_dataAr);
          if(!AMA_DataHandler::isError($id_service)) {
            $tester_infoAr = $common_dh->get_tester_info_from_pointer($sess_selected_tester);
            if(!AMA_DataHandler::isError($tester_infoAr)) {
                $id_tester = $tester_infoAr[0];
                $result = $common_dh->link_service_to_course($id_tester, $id_service, $id_course);
                if(AMA_DataHandler::isError($result)) {
                    $errObj = new ADA_Error($result);
                }
                else {
                    header('Location: list_courses.php');
                    exit();
                }
            } else {
              $errObj = new ADA_Error($result);
              $form = new CText(translateFN('Si è verificato un errore durante la creazione del corso. (1)'));
            }
          }
          else {
              $errObj = new ADA_Error($result);
              $form = new CText(translateFN('Si è verificato un errore durante la creazione del corso. (2)'));
          }
        } else {

//          $errObj = new ADA_Error($id_course);
            $help = translateFN('Si è verificato un errore durante la creazione del corso: codice servizio duplicato ');
        }
    } else {
        $form = new CText(translateFN('I dati inseriti nel form non sono validi'));
    }
} else {
    $providerAuthors = $dh->find_authors_list(array('username'), '');
    $authors = array();
    foreach($providerAuthors as $author) {
        $authors[$author[0]] = $author[1];
    }

    $availableLanguages = Translator::getSupportedLanguages();
    $languages = array();
    foreach($availableLanguages as $language) {
        $languages[$language['id_lingua']] = $language['nome_lingua'];
    }

    $form = new CourseModelForm($authors,$languages);
}

$label = translateFN('Aggiunta corso');
if(!isset($help)){$help = translateFN('Da qui il provider admin può creare un nuovo corso');}

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $form->getHtml(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml(),
    'user_avatar'=>$avatar->getHtml(),
	'user_modprofilelink' => $userObj->getEditProfilePage()
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_MASKEDINPUT,
		JQUERY_NO_CONFLICT,
		ROOT_DIR .'/js/switcher/edit_content.js'
);

$optionsAr['onload_func'] = 'initDateField(); includeFCKeditor(\'descrizione\');';

ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);
