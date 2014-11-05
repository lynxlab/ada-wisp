<?php

/**
 * mylog - this module provides print of a personal log
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright		Copyright (c) 2009-2014, Lynx s.r.l.
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


$debug = 0; 
$mylog_mode = 0; // default: only one file for user

$log_extension = ".htm";	


$self =  whoami();  // = mylog_print

//$classi_dichiarate = get_declared_classes();
//mydebug(__LINE__,__FILE__,$classi_dichiarate);

$ymdhms = today_dateFN();

// ******************************************************
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
$module_title = translateFN("Diario");

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
}

if (!file_exists($logfile))
	$fp = fopen($logfile,'w');

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
    header("Content-Type: text/html");
    //header("Content-Length: ".filesize($name));
    header("Content-Disposition: attachment; filename=$name_tmp");
    echo $log_text;
    exit;
} else {
    
   $date = today_dateFN()." ".today_timeFN()."\n";

   $log_data = $log_text;
}


$banner = include ("$root_dir/include/banner.inc.php");


/*
 $body_onload = "includeFCKeditor('log_today'); \$j('input, a.button, button').uniform();";
 $options = array('onload_func' => $body_onload);
 * 
 */

 $imgAvatar = $userObj->getAvatar();
 $avatar = CDOMElement::create('img','src:'.$imgAvatar);
 $avatar->setAttribute('class', 'img_user_avatar');
 $course_title = translateFN('Diario personale');

 $layout_family = $_SESSION['sess_userObj']->template_family;
 
$node_data = array(
       'banner'=>$banner,
       'course_title'=>$course_title,
       'date'=>$ymdhms,
       'user_name'=>$userObj->nome,
       'user_type'=>$userObj->convertUserTypeFN($id_profile),
       'data'=>$log_data,
       'help'=>$help,
       'status'=>$status,
       'logo'=>'<img src="'.HTTP_ROOT_DIR.'/layout/'.$layout_family.'/img/header-logo.png" alt="logo">',
      'user_avatar'=>$avatar->getHtml()
    );

 if(isset($msg))
{
    $help=CDOMElement::create('label');
    $help->addChild(new CText(translateFN(ltrim($msg))));
    $node_data['help']=$help->getHtml();
}


$layout_dataAr['JS_filename'] = array(
        JQUERY,
        JQUERY_UI,
        JQUERY_NO_CONFLICT
);
	
$layout_dataAr['CSS_filename'] = array (
                JQUERY_UI_CSS,
);	

//ARE::render($layout_dataAr,$node_data, NULL, $options);

$PRINT_optionsAr = array(
		'id'=>$id_node,
		'url'=>$_SERVER['URI'],
		'course_title' => strip_tags($content_dataAr['course_title']),
		'portal' => $eportal,
		'onload_func' => 'window.print();window.close()'
);
ARE::render($layout_dataAR,$node_data, ARE_PRINT_RENDER, $PRINT_optionsAr);

/* Versione XML:

 $xmlObj = new XML($layout_template,$layout_CSS,$imgpath);
 $xmlObj->fillin_templateFN($node_data);
 $xmlObj->outputFN('page','XML');

*/
