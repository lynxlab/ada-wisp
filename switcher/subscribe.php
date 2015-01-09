<?php

/**
 * subscriptions file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
/**
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
$variableToClearAR = array('layout', 'user', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout', 'user', 'course', 'course_instance')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();

require_once 'include/switcher_functions.inc.php';
require_once 'include/Subscription.inc.php';

require_once ROOT_DIR . '/include/Forms/UserFindForm.inc.php';
/*
 * YOUR CODE HERE
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $canSubscribeUser = false;
    if($courseInstanceObj instanceof  Course_instance && $courseInstanceObj->isFull()) {
        $startStudentLevel = $courseInstanceObj->start_level_student;
    }

    if(isset($_POST['findByUsername'])) {
        $form = new UserFindForm();
        $form->fillWithPostData();
        if($form->isValid()) {
            $courseInstanceId = $_POST['id_course_instance'];
            $subscriberObj = MultiPort::findUserByUsername($_POST['username']);
            if ($subscriberObj instanceof ADAUser) {
                $result = $dh->student_can_subscribe_to_course_instance($subscriberObj->getId(), $courseInstanceId);
                if (!AMA_DataHandler::isError($result) && $result !== false) {
                    $canSubscribeUser = true;
                }                
            }
            
            if ($canSubscribeUser && $courseObj instanceof Course && $courseObj->isFull()) {
            	$courseProviderAr = $common_dh->get_tester_info_from_id_course($courseObj->getId());
            	if (!AMA_DB::isError($courseProviderAr) && is_array($courseProviderAr) && isset($courseProviderAr['puntatore'])) {            		
            		if (!in_array($courseProviderAr['puntatore'], $subscriberObj->getTesters())) {
            			// subscribe user to course provider
            			$canSubscribeUser = Multiport::setUser($subscriberObj, array($courseProviderAr['puntatore']));
            			if (!$canSubscribeUser) {
            				$data = new CText(translateFN('Problemi nell\'iscrizione utente al provider del corso.').' '.translateFN('Utente non iscritto'));
            			}
            		}
            		if ($canSubscribeUser) {
            			$subscriptionDate=0;
            			$s = new Subscription($subscriberObj->getId(), $courseInstanceId,$subscriptionDate,$startStudentLevel);
            			$s->setSubscriptionStatus(ADA_STATUS_SUBSCRIBED);
            			Subscription::addSubscription($s);
            			$data = new CText(translateFN('Utente iscritto'));
            		} else {
		                $data = new CText(translateFN('Problemi').' '.translateFN('Utente non iscritto'));
		            }
            	} else {
            		$data = new CText(translateFN('Problemi nel recuperare il provider del corso.').' '.translateFN('Utente non iscritto'));
            	}
            } else {
                $data = new CText(translateFN('Problemi').' '.translateFN('Utente non iscritto'));
            }
        } else {
            $data = new CText(translateFN('Dati inseriti non validi'));
        }
    } else {
        $data = new CText('');
    }
} else {
    if($courseInstanceObj instanceof  Course_instance && $courseInstanceObj->isFull()) {
        $formData = array(
            'id_course_instance' => $courseInstanceObj->getId()
        );
        $data = new UserFindForm();
        $data->fillWithArrayData($formData);
    } else {
        $data = new CText(translateFN('Classe non trovata'));
    }
}
$help = translateFN('Da qui il provider admin può iscrivere uno studente già registrato alla classe selezionata');
/*
 * OUTPUT
 */

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
    'banner' => isset($banner) ? $banner : '',
    'path' => isset($path) ? $path : '',
    'label' => isset($label) ? $label : '',
    'status' => isset($status) ? $status : '',
    'user_name' => $user_name,
    'user_type' => $user_type,
    'menu' => isset($menu) ? $menu : '',
    'help' => isset($help) ? $help : '',
    'data' => $data->getHtml(),
    'messages' => $user_messages->getHtml(),
    'agenda ' => $user_agenda->getHtml(),
    'user_avatar'=>$avatar->getHtml(),
	'user_modprofilelink' => $userObj->getEditProfilePage()
);

ARE::render($layout_dataAr, $content_dataAr);