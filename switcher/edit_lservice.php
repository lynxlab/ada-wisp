<?php

/**
 * File edit_course.php
 *
 * The switcher can use this module to update the informations about an existing
 * course.
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
    AMA_TYPE_SWITCHER => array('layout', 'course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';
include_once ROOT_DIR . '/services/include/NodeEditing.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/ServiceModelForm.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $providerAuthors = $dh->find_authors_list(array('username'), '');
    $authors = array();
    foreach ($providerAuthors as $author) {
        $authors[$author[0]] = $author[1];
    }

    $availableLanguages = Translator::getSupportedLanguages();
    $languages = array();
    foreach ($availableLanguages as $language) {
        $languages[$language['id_lingua']] = $language['nome_lingua'];
    }

    $form = new ServiceModelForm($authors, $languages);
    $form->fillWithPostData();
    if ($form->isValid()) {
        $course = array(
            'nome' => $_POST['nome'],
            'titolo' => $_POST['titolo'],
            'descr' => $_POST['descrizione'],
            'd_create' => $_POST['data_creazione'],
            'd_publish' => $_POST['data_pubblicazione'],
            'id_autore' => $_POST['id_utente_autore'],
            'id_nodo_toc' => $_POST['id_nodo_toc'],
            'id_nodo_iniziale' => $_POST['id_nodo_iniziale'],
            'media_path' => $_POST['media_path'],
            'id_lingua' => $_POST['id_lingua'],
            'static_mode' => $_POST['static_mode'],
            'crediti' => $_POST['crediti'],
            'common_area' => $_POST['common_area']
        );
        $result = $dh->set_course($_POST['id_corso'], $course);

        if (!AMA_DataHandler::isError($result)) {
            $service_dataAr = $common_dh->get_service_info_from_course($_POST['id_corso']);
            if (!AMA_Common_DataHandler::isError($service_dataAr)) {
                $update_serviceDataAr = array(
                    'service_name' => $_POST['titolo'],
                    'service_description' => $_POST['descrizione'],
                    'service_level' => $service_dataAr[3],
                    'service_duration' => $service_dataAr[4],
                    'service_min_meetings' => $service_dataAr[5],
                    'service_max_meetings' => $service_dataAr[6],
                    'service_meeting_duration' => $service_dataAr[7]
                );                
                $result = $common_dh->set_service($service_dataAr[0], $update_serviceDataAr);
                if (AMA_Common_DataHandler::isError($result)) {
                     $form = new CText("Si è verificato un errore durante l'aggiornamento dei dati del corso");
                } else {
                    /* *
                     * if needed it creates the instance and chat...
                     */
                    $fieldsAr = array('data_inizio', 'data_inizio_previsto', 'durata', 'data_fine', 'title');
                    $id_course = $_POST['id_corso'];
                    $instancesAr = $dh->course_instance_get_list($fieldsAr, $id_course);
                    if ($_POST['common_area'] && !AMA_DataHandler::isError($instancesAr) && count($instancesAr==0)) {
                        $course_instanceAr = array(
                            'data_inizio_previsto' => time(), // dt2tsFN($_POST['data_inizio_previsto']),
                            'durata' => '730', /* two years*/ // $_POST['durata'],
                            'price' => '0',
                            'self_instruction' => '0',
                            'self_registration' => '1',
                            'title' => $_POST['titolo'],
                            'duration_subscription' => '730', //$_POST['duration_subscription'],
                            'start_level_student' => '99', //$_POST['start_level_student'],
                            'open_subscription' => '1' // $_POST['open_subscription']
                        );
                        $id_instance_course = Course_instance::add_instance($id_course, $course_instanceAr);
                        if(!AMA_DataHandler::isError($result)) {
                            $id_chatroom = Course_instance::add_chatRoom($id_course, $course_instanceAr);
                        }
                    }
                    
                    header('Location: list_lservices.php');
                    exit();
                }
            }            
        } else {
             $form = new CText("Si è verificato un errore durante l'aggiornamento dei dati del corso");
        }
    } else {
        $form = new CText('Form non valido');
    }
} else {
    if (!($courseObj instanceof Course) || !$courseObj->isFull()) {
        $form = new CText(translateFN('Servizio non trovato'));
    } else {
        $providerAuthors = $dh->find_authors_list(array('username'), '');
        $authors = array();
        foreach ($providerAuthors as $author) {
            $authors[$author[0]] = $author[1];
        }

        $availableLanguages = Translator::getSupportedLanguages();
        $languages = array();
        foreach ($availableLanguages as $language) {
            $languages[$language['id_lingua']] = $language['nome_lingua'];
        }
        $fieldsAr = array('data_inizio', 'data_inizio_previsto', 'durata', 'data_fine', 'title');
        $instancesAr = $dh->course_instance_get_list($fieldsAr, $courseObj->getId());
        $common_area = 0;
        if (!AMA_DataHandler::isError($instancesAr) && count($instancesAr>0)) {
            $common_area = 1;
        }


        $form = new ServiceModelForm($authors, $languages);

        if (!AMA_DataHandler::isError($course_data)) {
            $formData = array(
                'id_corso' => $courseObj->getId(),
                'id_utente_autore' => $courseObj->getAuthorId(),
                'id_lingua' => $courseObj->getLanguageId(),
                'id_layout' => $courseObj->getLayoutId(),
                'nome' => $courseObj->getCode(),
                'titolo' => $courseObj->getTitle(),
                'descrizione' => $courseObj->getDescription(),
                'id_nodo_iniziale' => $courseObj->getRootNodeId(),
                'id_nodo_toc' => $courseObj->getTableOfContentsNodeId(),
                'media_path' => $courseObj->getMediaPath(),
                'static_mode' => $courseObj->getStaticMode(),
                'data_creazione' => $courseObj->getCreationDate(),
                'data_pubblicazione' => $courseObj->getPublicationDate(),
                'common_area' => $common_area,
                'crediti' =>  $courseObj->getCredits() // modifica in Course
            );
            $form->fillWithArrayData($formData);
        } else {
            $form = new CText(translateFN('Servizio non trovato'));
        }
    }
}

$label = translateFN('Modifica dei dati del servizio');
$help = translateFN('Da qui il provider admin può modificare un servizio esistente');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $form->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);