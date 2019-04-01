<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
*/

/**
 * CLASS INFO
 *
 * @package		class_info
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		user
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
$allowedUsersAr = array(AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'default_tester')
);
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();

require_once 'include/browsing_functions.inc.php';

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
BrowsingHelper::init($neededObjAr);

// ini_set ("display_errors","1"); error_reporting(E_ALL);

//print_r($_SESSION);
/*
$courseInstances = array();
$serviceProviders = $userObj->getTesters();

if (count($serviceProviders) == 1) {
    $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($serviceProviders[0]));
    $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
} else {
    foreach ($serviceProviders as $Provider) {
        $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
        $GLOBALS['dh'] = $provider_dh;
        $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
    }
}
 *
 */
$id_course = (isset ($_GET['id_course']) && intval ($_GET['id_course'])>=0) ? intval ($_GET['id_course']) : -1;
$providerAr = $common_dh->get_tester_info_from_id_course($id_course);
$client = $providerAr['puntatore'];
$provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($client));
$GLOBALS['dh'] = $provider_dh;
$courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());

if(!AMA_DataHandler::isError($courseInstances)) {
    $found = count($courseInstances);
    $data = "";
    if (isset($id_course_instance) and isset($id_course)){
             $stud_status = ADA_STATUS_SUBSCRIBED; //only subscribed students
             $students =  $provider_dh->course_instance_students_presubscribe_get_list($id_course_instance,$stud_status);
             $student_listHa = array();
             foreach ($students as $one_student){
                         $id_stud = $one_student['id_utente_studente'];
                         if ($provider_dh->get_user_type($id_stud)==AMA_TYPE_STUDENT) {
                                 $studn = $provider_dh->get_student($id_stud); // var_dump($studn);
                                 $row = array(
                                        // $studn['username'], è uguale all'email
					$studn['nome'],
					$studn['cognome'],
                                        $studn['email']
                                 );
                                 array_push($student_listHa,$row);
                        }
             }
             $tObj = new Table();
             $tObj->initTable('1','center','0','1','100%','','','','','0','1');
             // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
             $caption = "<strong>".translateFN("Elenco degli iscritt* al corso ")."</strong>";
             $summary = translateFN("Elenco degli iscritt* al corso ");
             $tObj->setTable($student_listHa,$caption,$summary);

             $data = $tObj->getTable();
             $data= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $data, 1); // replace first occurence of class
     } else {
            $data =   translateFN("Errore nei dati");
     }
} else {
        $data = translateFN('Non sei iscritto a nessuna classe');
}


/*
 * Last access link
 */

if(isset($_SESSION['sess_id_course_instance'])){
    $last_access=$userObj->get_last_accessFN(($_SESSION['sess_id_course_instance']),"UT",null);
    $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
else {
    $last_access=$userObj->get_last_accessFN(null,"UT",null);
    $last_access=AMA_DataHandler::ts_to_date($last_access);
  }
if($last_access=='' || is_null($last_access)){
   $last_access='-';
}
/*
 * Output
 */
$content_dataAr = array(
    'banner' => $banner,
    'today' => $ymdhms,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'last_visit' => $last_access,
    'edit_profile'=>$userObj->getEditProfilePage(),
    'message' => $message,
//    'iscritto' => $sub_course_data,
//    'iscrivibili' => $to_sub_course_data,
    'course_title' => translateFN("Home dell'utente"),
//    'corsi' => $corsi,
//    'profilo' => $profilo,

    // 'data' => $data->getHtml(),

    'data' => $data,

    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'events' => $user_events->getHtml(),
    'status' => $status
);

ARE::render($layout_dataAr,$content_dataAr);