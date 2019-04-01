<?php

/**
 * View user - this module shows the profile of an existing user
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
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
	AMA_TYPE_STUDENT => array('layout'),
	AMA_TYPE_TUTOR => array('layout'),
	AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();

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

include_once ROOT_DIR . '/admin/include/AdminUtils.inc.php';
/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';
$userId = false;
if ($_SESSION['sess_userObj']->getType() == AMA_TYPE_SWITCHER) {
	$userId = DataValidator::is_uinteger($_GET['id_user']);
}

if ($userId === false && isset($_SESSION['sess_userObj']) && $_SESSION['sess_userObj'] instanceof ADALoggableUser) {
	$userId = $_SESSION['sess_userObj']->getId();
}

if($userId === false) {
    $data = new CText('Utente non trovato');
}
else {

    $user_info = $dh->_get_user_info($userId);
    if(AMA_DataHandler::isError($userId)) {
        $data = new CText('Utente non trovato');
    } else {
        $viewedUserObj = MultiPort::findUser($userId);
        $viewedUserObj->toArray();
        $user_dataAr = array(
            'id' => $viewedUserObj->getId(),
            'tipo' => $viewedUserObj->getTypeAsString(),
            'nome e cognome' => $viewedUserObj->getFullName(),
            'data di nascita' => $viewedUserObj->getBirthDate(),
        	'Comune o stato estero di nascita' => $viewedUserObj->getBirthCity(),
        	'Provincia di nascita' => $viewedUserObj->getBirthProvince(),
            'genere' => $viewedUserObj->getGender(),
            'email' => $viewedUserObj->getEmail(),
            'telefono' => $viewedUserObj->getPhoneNumber(),
            'indirizzo' => $viewedUserObj->getAddress(),
            'citta' => $viewedUserObj->getCity(),
            'provincia' => $viewedUserObj->getProvince(),
            'nazione' => $viewedUserObj->getCountry(),
        	'confermato' => ($viewedUserObj->getStatus()==ADA_STATUS_REGISTERED) ? translateFN("Si") : translateFN("No")
        );

        $data = BaseHtmlLib::labeledListElement('class:view_info', $user_dataAr);
    }
}

$label = translateFN('Profilo utente');
$help = translateFN('Da qui il provider admin può visualizzare il profilo di un utente esistente');

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
$options = null;
if (isset($_GET['pdfExport']) && intval($_GET['pdfExport'])===1) {
	$options['outputfile'] = $viewedUserObj->getFullName().'-'.date("d m Y");
	$options['forcedownload'] = true;
}

ARE::render($layout_dataAr, $content_dataAr, NULL, $options);

