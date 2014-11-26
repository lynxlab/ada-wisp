<?php
/**
 * mylog - this module provides management of a personal diary
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright		Copyright (c) 2009-2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
//    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
//    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance'),
//    AMA_TYPE_AUTHOR => array('node', 'layout')
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
include_once 'include/browsing_functions.inc.php';

require_once ROOT_DIR . '/include/Forms/LogForm.inc.php';

$debug = 0; 
$mylog_mode = 0; // default: only one file for user

$log_extension = ".htm";	


$self =  whoami();  // = mylog

//$classi_dichiarate = get_declared_classes();
//mydebug(__LINE__,__FILE__,$classi_dichiarate);

$ymdhms = today_dateFN();

//import_request_variables("gP","");

// ******************************************************
$reg_enabled = TRUE; // link to edit bookmarks
$log_enabled = TRUE; // link to history 
$mod_enabled = TRUE; // link to modify nod/tes
$com_enabled = TRUE;  // link to comunicate among users
// Get user object
$userObj = read_user_from_DB($sess_id_user);
//print_r($userObj);
if ((is_object($userObj)) && (!AMA_dataHandler::isError($userObj))) {
     $id_profile = $userObj->tipo;
       switch ($id_profile){
        case AMA_TYPE_TUTOR:
        case AMA_TYPE_STUDENT:
        case AMA_TYPE_AUTHOR:
           break;
        case AMA_TYPE_ADMIN:
            $homepage = $http_root_dir . "/browsing/student.php"; 
            $msg =   urlencode(translateFN("Ridirezionamento automatico"));
            header("Location: $homepage?err_msg=$msg");
            exit;
            break;
        }
        $user_type = $userObj->convertUserTypeFN($id_profile);
        $user_name =  $userObj->username;
        $user_family = $userObj->template_family; 
} else {
$errObj = new ADA_error(translateFN("Utente non trovato"),translateFN("Impossibile proseguire."));
}

// set the  title:	 
$module_title = translateFN("Repository");

// building file name
// rootdir  + media path + author_id + filename
$public_dir = "/services/media/";
$user_dir = '/upload_file/uploaded_files/';
// a public access directory where log files can be written
// building file name

$each_course = false; //used to make the user log indipendent from course and instance course

if (isset($sess_id_course) &&  (!($sess_id_course=="")) && $each_course) {
// finding course's author
	$course_ha = $dh->get_course($sess_id_course);
	if (AMA_DataHandler::isError($course_ha)){ // not enrolled yet?
        	$msg = $course_ha->getMessage();
        	header("Location: " . $http_root_dir . "/browsing/student.php?status=$msg");
	}
	// look for the author, starting from author's id
	$author_id = $course_ha['id_autore'];
	if ($mylog_mode == 1){
		// a log file for every instance of course in which user is enrolled in:
		// id_course_instance + user_id 
		$name_tmp = 'log_'.$sess_id_course_instance . "_" . $sess_id_user . $log_extension;	
	} else { // default
		// only 1 log file for user:
		$name_tmp = 'log_'.$sess_id_user.$log_extension; 
	}

	$logfile = $root_dir . "/services/media/" . $author_id . "/" . $name_tmp;
} else {
	$logfile = $root_dir . $user_dir .  $sess_id_user. '/'.'log'.$sess_id_user.$log_extension;
        $userUploadPath = $root_dir . $user_dir .  $sess_id_user;
        if (!is_dir($userUploadPath)) {
            if (mkdir($userUploadPath) == FALSE) {
                $makedir = false;
            }
        }
}

if (!file_exists($logfile))
	$fp = fopen($logfile,'w');

//set the  body:

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
{
    $form = new LogForm();
    $form->fillWithPostData();
    
    if (isset($_POST['log_today']))
    {
       $log = $_POST['log_today']; //."<br/>".$_POST['log_today'];
       $i = fopen($logfile,'w');
       if (get_magic_quotes_gpc()) {
	       $res = fwrite($i,stripslashes($log));
	}else{
	       $res = fwrite($i,$log);
	}
       $res = fclose($i);
   }
    $msg = translateFN("Le informazioni sono state registrate.");
}
// } else {
if ($fp = fopen($logfile,'r'))
	$log_text = fread ($fp,16000);
else
	$log_text = "";
fclose($fp);

if (isset($op) && ($op=="export")){
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    // always modified
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");                          // HTTP/1.0
    //header("Content-Type: text/plain");
//    header("Content-Type: text/html");
    header("Content-type: application/vnd.ms-word");
    //header("Content-Length: ".filesize($name));
//    $logfileToExport = $root_dir . $user_dir .  $sess_id_user. '/'.'log'.$sess_id_user.'.doc';
    $logfileToExport = 'sespius_repository.doc';
    header("Content-Disposition: attachment; filename=$logfileToExport");
    echo $log_text;
    exit;
} else {
    
    $date = today_dateFN()." ".today_timeFN()."\n";
    $arrayLogAr = array(
        'log_text' => $log_text,
        'log_today' => $date.'<br />'.$log_text
    );
    $form = new LogForm();
    $form->fillWithArrayData($arrayLogAr);   
    $log_form = $form->render();

   $log_data.= $log_text;
}


// Who's online
// $online_users_listing_mode = 0 (default) : only total numer of users online
// $online_users_listing_mode = 1  : username of users
// $online_users_listing_mode = 2  : username and email of users

$online_users_listing_mode = 2;
$online_users = ADALoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


$export_log_link = "<a href=$http_root_dir/browsing/mylog.php?op=export>".translateFN("esportare")."</a>";
$print_link = '<a href='.$http_root_dir.'/browsing/mylog_print.php target="_blank">'.translateFN("stampare")."</a>";
$help = translateFN('Nel Repository si possono inserire anche i propri commenti. E\' possibile'. ' '.$export_log_link. ' ' .'per conservarli o '). $print_link;

$banner = include ("$root_dir/include/banner.inc.php");

 $body_onload = "includeFCKeditor('log_today'); \$j('input, a.button, button').uniform();";
 $options = array('onload_func' => $body_onload);

 $imgAvatar = $userObj->getAvatar();
 $avatar = CDOMElement::create('img','src:'.$imgAvatar);
 $avatar->setAttribute('class', 'img_user_avatar');
         
$node_data = array(
       'banner'=>$banner,
       'course_title'=> translateFN('Repository'),
       'today'=>$ymdhms,
       'path'=>$node_path,
       'user_name'=>$userObj->nome,
       'user_type'=>$user_type,
       'user_level'=>$user_level,
       'last_visit'=>$last_access,
//                   'data'=>$log_data,
       'data'=>$log_form,
       'help'=>$help,
       'bookmarks'=>$user_bookmarks,
       'status'=>$status,
       'profilo'=>$profilo,
       'myforum'=>$my_forum,
       'title'=>$node_title,
       'user_avatar'=>$avatar->getHtml(),
       'user_modprofilelink' => $userObj->getEditProfilePage()		
    );

if ($com_enabled){
   $node_data['messages']=$user_messages->getHtml();
   $node_data['agenda']=$user_agenda->getHtml();
   $node_data['events']=$user_events->getHtml();
   $node_data['chat_users']=$online_users;
} else {
   $node_data['messages'] = translateFN("messaggeria non abilitata");
   $node_data['agenda']=translateFN("agenda non abilitata");
   $node_data['chat_users']="";
}
 if(isset($msg))
{
    $help=CDOMElement::create('label');
    $help->addChild(new CText(translateFN(ltrim($msg))));
    $node_data['help']=$help->getHtml();
}

	$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_UNIFORM,
		JQUERY_NO_CONFLICT
     );
	
	$layout_dataAr['CSS_filename'] = array (
			JQUERY_UI_CSS,
			JQUERY_UNIFORM_CSS
	);	

ARE::render($layout_dataAr,$node_data, NULL, $options);

/* Versione XML:

 $xmlObj = new XML($layout_template,$layout_CSS,$imgpath);
 $xmlObj->fillin_templateFN($node_data);
 $xmlObj->outputFN('page','XML');

*/
