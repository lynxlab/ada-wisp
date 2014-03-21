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
    AMA_TYPE_SWITCHER => array('layout','course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require_once 'include/switcher_functions.inc.php';
require_once ROOT_DIR . '/include/Forms/CourseRemovalForm.inc.php';
require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';

/*
 * YOUR CODE HERE
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if($courseObj instanceof Course && $courseObj->isFull()) {
        $form = new CourseRemovalForm($courseObj);
        if($form->isValid()) {
            if($_POST['deleteCourse'] == 1) {
                $courseId = $courseObj->getId();
                $serviceInfo = $common_dh->get_service_info_from_course($courseId);
                if(!AMA_Common_DataHandler::isError($serviceInfo)) {
                    $serviceId = $serviceInfo[0];
                    $deletedInstancesProcessOk = true;
                    /* ***********
                     * if instances of service exist, they are deleted before to delete service and link to general service
                     */
                    $instancesForCourse = $dh->course_instance_get_list(NULL,$courseId);
                    if (!AMA_DataHandler::isError($instancesForCourse) && count($instancesForCourse) > 0) {
                        foreach ($instancesForCourse as $instance) {
                            if ($deletedInstancesProcessOk) {
                                $deletedInstancesProcessOk = false;
                                $courseInstanceId = $instance[0];
                                if(Subscription::deleteAllSubscriptionsToClassRoom($courseInstanceId)) {               
                                    $result = $dh->course_instance_tutors_unsubscribe($courseInstanceId);
                                    if($result === true) {                
                                        $result = $dh->course_instance_remove($courseInstanceId);
                                        if(!AMA_DataHandler::isError($result)) {
                                            $deletedInstancesProcessOk = true;
                                        } else {
                                            $deletedInstancesProcessOk = false;
                                            $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione della istanza.') . '(1)');
                                        }
                                    } else {
                                        $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione della istanza'). '(2)');
                                        $deletedInstancesProcessOk = false;
                                    }
                                } else {
                                    $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione della istanza'). '(3)');
                                    $deletedInstancesProcessOk = false;
                                }
                            }
                        }
                    }
                    /* ***********
                     * ended  deleting process of instances  
                     */
                    
                    if ($deletedInstancesProcessOk) {
                        
                        $result = $common_dh->delete_service($serviceId);
                        if(!AMA_Common_DataHandler::isError($result)) {
                            $result = $common_dh->unlink_service_from_course($serviceId, $courseId);
                            if(!AMA_DataHandler::isError($result)) {
                                $result = $dh->remove_course($courseId);
                                if(AMA_DataHandler::isError($result)) {
                                    $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del servizio.') . '(1)');
                                } else {
                                    unset($_SESSION['sess_courseObj']);
                                    unset($_SESSION['sess_id_course']);
                                    $data = new CText(sprintf(translateFN('La cancellazione del servizio "%s" è riuscita.'), $courseObj->getTitle()));

                                    
                                    /*
                                    header('Location: list_courses.php');
                                    exit();
                                     * 
                                     */
                                }
                            } else {
                                $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del corso.') . '(2)');
                            }
                        } else {
                            $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del corso.') . '(3)');
                        }
                    } else {
                        $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione delle istanze del servizio.') . '(5)');
                    }
                    
                } else {
                    $data = new CText(translateFN('Si sono verificati degli errori durante la cancellazione del servizio.') . '(4)');
                }
                
            } else {
                $data = new CText(sprintf(translateFN('La cancellazione del servizio "%s" è stata annullata.'), $courseObj->getTitle()));
            }
        } else {
            $data = new CText(translateFN('I dati inseriti nel form non sono validi'));
        }
    } else {
        $data = new CText(translateFN('Servizio non trovato'));
    }
    
    
        $dialog_div = CDOMElement::create('DIV', 'id:dialog-message');
        $dialog_div->setAttribute('style', 'text-align:center');
        $dialog_div->addChild($data);
        

        $data = $dialog_div;

        $layout_dataAr['JS_filename'] = array(
                        ROOT_DIR.'/js/switcher/assign_practitioner.js',
                        JQUERY,
                        JQUERY_UI,
                        JQUERY_NO_CONFLICT
        );
//        var_dump($layout_dataAr);
        $optionsAr['onload_func'] = 'initDoc();';
     
        /**
         * if the jqueru-ui theme directory is there in the template family,
         * do not include the default jquery-ui theme but use the one imported
         * in the edit_user.css file instead
         */
        if (!is_dir(ROOT_DIR.'/layout/'.$userObj->template_family.'/css/jquery-ui'))
        {
                $layout_dataAr['CSS_filename'] = array(
                                JQUERY_UI_CSS
                );
        }


}
else {    
    if($courseObj instanceof Course && $courseObj->isFull()) {
        $result = $dh->course_has_instances($courseObj->getId());
        if(AMA_DataHandler::isError($result)) {
            $data = new CText(translateFN('Si è verificato un errore nella lettura dei dati del servizio'));
        } else if($result == true) {
            $notice = sprintf(translateFN('Il servizio "%s" ha degli utenti associati. Tutti i dati delle azioni degli utenti saranno persi.'). '<br />', $courseObj->getTitle());
            $data = new CourseRemovalForm($courseObj, $notice);
        } else {
            $data = new CourseRemovalForm($courseObj);
        }
    }
    else {
        $data = new CText(translateFN('Servizio non trovato'));
    }
}


$label = translateFN('Cancellazione di un servizio');
$help = translateFN('Da qui il provider admin può cancellare un servizio');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);
  //      var_dump($layout_dataAr);

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
//ARE::render($layout_dataAr, $content_dataAr);