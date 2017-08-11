<?php

/**
 * List users - this module provides list users functionality
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
    AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */

$type = DataValidator::validate_not_empty_string($_GET['list']);
$fieldsAr = array('nome','cognome','username','tipo');
switch($type) {
    case 'authors':
        $usersAr = $dh->get_authors_list($fieldsAr);
        $profilelist = translateFN('lista degli autori');
        break;
    case 'tutors':
        $usersAr = $dh->get_tutors_list($fieldsAr);
        if (defined('AMA_TYPE_SUPERTUTOR')) $usersAr = array_merge($usersAr,$dh->get_supertutors_list($fieldsAr));
        $profilelist = translateFN('lista dei tutors');
        break;
    case 'students':
    default:
    	/**
    	 * @author giorgio 29/mag/2013
    	 *
    	 * if we're listing students, let's add the stato field as well
    	 */
    	array_push($fieldsAr, 'stato', 'AA_ISCR_DESC','ANNO_CORSO', 'TASSE_IN_REGOLA_OGGI', 'PT_DESC', 'TIPO_DID_DECODE', 'STA_OCCUP_DECODE', 'PDSORD_DESC');
        $usersAr = $dh->get_students_list($fieldsAr);
        $profilelist = translateFN('lista degli studenti');
        break;
}

if(is_array($usersAr) && count($usersAr) > 0) {
    $UserNum = count($usersAr);
    $thead_data = array(
       null,
       translateFN('id'),
       translateFN('nome e cognome'),
       translateFN('username')
    );

    if ($type!='authors' && $type!='tutors') {
    	array_push ($thead_data, translateFN('Iscrizione'), translateFN('Corso di studi'), translateFN('Tutor preassegnato'));
    }

    array_push ($thead_data, translateFN('azioni'));
    /**
     * @author giorgio 29/mag/2013
     *
     * if we're listing students, let's add the stato field as well
     */

    if ($type!='authors' && $type!='tutors') array_push ($thead_data, translateFN('Abilitato'));

    $tbody_data = array();
    $edit_img = CDOMElement::create('img', 'src:img/edit.png,alt:edit');
    $view_img = CDOMElement::create('img', 'src:img/zoom.png,alt:view');

    foreach($usersAr as $user) {
        $userId = $user[0];
        if ($user[4]==AMA_TYPE_SUPERTUTOR) {
        	$imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/supertutoricon.png');
        	$imgDetails->setAttribute('title', translateFN('Super Tutor'));
        } else {
	        $imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/details_open.png');
	        $imgDetails->setAttribute('title', translateFN('visualizza/nasconde i dettagli dell\'utente'));
	        $imgDetails->setAttribute('onclick',"toggleDetails($userId,this);");
	        $imgDetails->setAttribute('style', 'cursor:pointer;');
        }
        $imgDetails->setAttribute('class', 'imgDetls tooltip');


//        $span_idUser = $userId;
//        $span_idUser = CDOMElement::create('span');
//        $span_idUser->setAttribute('class', 'id_user');
//        $span_idUser->addChild(new CText($user[0]));

        $User_fullname = CDOMElement::create('span');
        $User_fullname->setAttribute('class', 'fullname');
        $User_fullname->addChild(new CText($user[1].' '.$user[2]));

        $span_UserName = CDOMElement::create('span');
        $span_UserName->setAttribute('class', 'UserName');
        $span_UserName->addChild(new CText($user[3]));

        $edit_link = BaseHtmlLib::link("edit_user.php?id_user=$userId&usertype=".$user[4], $edit_img->getHtml());
        $edit_link->setAttribute('class', 'tooltip');
        $edit_link->setAttribute('title', translateFN('Modifica dati utente'));

        $view_link = BaseHtmlLib::link("view_user.php?id_user=$userId", $view_img->getHtml());
        $view_link->setAttribute('class', 'tooltip');
        $view_link->setAttribute('title', translateFN('Visualizza dati utente'));

        if ($user[5] == ADA_STATUS_REGISTERED) {
        	$title = 'Disabilita utente';
        	$delete_img = CDOMElement::create('img', 'src:img/delete.png,alt:delete');
        } else {
        	$title = 'Abilita utente';
        	$delete_img = CDOMElement::create('img', 'src:img/user.png,alt:delete');
        }

        $delete_link = BaseHtmlLib::link("delete_user.php?id_user=$userId",$delete_img->getHtml());
        $delete_link->setAttribute('class', 'tooltip');
        $delete_link->setAttribute('title', translateFN($title));

        $actions = BaseHtmlLib::plainListElement('class:inline_menu',array($edit_link, $view_link, $delete_link));
        /**
         * @author giorgio 29/mag/2013
         *
         * if we're listing students, let's add the stato field as well
         */
        if ($type!='authors' && $type!='tutors')  {

        	$studentDetailStr = $user['AA_ISCR_DESC'];
        	if (strlen($user['ANNO_CORSO'])>0) $studentDetailStr .= '&nbsp;('.$user['ANNO_CORSO'].')';
        	$short = ADAUser::getShortCodeForField('TASSE_IN_REGOLA_OGGI', $user['TASSE_IN_REGOLA_OGGI']);
        	if (strlen($short)>0) {
        		if (strcasecmp($user['TASSE_IN_REGOLA_OGGI'], 'no')===0) {
        			$tasseText = 'Non in regola';
        		} else {
        			$tasseText = 'In regola';
        		}
        		$span = CDOMElement::create('span','class:details tooltip, title:'.$tasseText);
        		$span->addChild(new CText($short));
        		$studentDetailStr .= $span->getHtml();
        	}

        	if (strlen($studentDetailStr)>0) $studentDetailStr .= '<br/>';

        	$short = ADAUser::getShortCodeForField('PT_DESC', $user['PT_DESC']);
        	if (strlen($short)>0) {
        		$span = CDOMElement::create('span','class:details tooltip,title:'.$user['PT_DESC']);
        		$span->addChild (new CText($short));
        		$studentDetailStr .= $span->getHtml();
        	}
        	$short = ADAUser::getShortCodeForField('TIPO_DID_DECODE', $user['TIPO_DID_DECODE']);
        	if (strlen($short)>0) {
        		$span = CDOMElement::create('span','class:details tooltip,title:'.$user['TIPO_DID_DECODE']);
        		$span->addChild (new CText($short));
        		$studentDetailStr .= $span->getHtml();
        	}
        	$short = ADAUser::getShortCodeForField('STA_OCCUP_DECODE', $user['STA_OCCUP_DECODE']);
        	if (strlen($short)>0) {
        		$span = CDOMElement::create('span','class:details tooltip,title:'.$user['STA_OCCUP_DECODE']);
        		$span->addChild (new CText($short));
        		$studentDetailStr .= $span->getHtml();
        	}

        	$corsoStudi = array();
        	if (strlen($user['CDS_DESC'])) $corsoStudi[] = strtoupper($user['CDS_DESC']);
        	if (strlen($user['PDSORD_DESC'])) $corsoStudi[] = strtoupper($user['PDSORD_DESC']);

        	$idTutor = $dh->get_tutor_preassigned_to_student_for_course($userId);
        	$tutorFullName = '&nbsp;';
        	if (!AMA_DB::isError($idTutor) && intval($idTutor)>0) {
        		$aTutor = MultiPort::findUser($idTutor);
        		if (!AMA_DB::isError($aTutor)) $tutorFullName = str_replace(' ', '&nbsp;', $aTutor->getFullName());
        	}
        	$isConfirmed = ($user[5] == ADA_STATUS_REGISTERED) ? translateFN("Si") : translateFN("No");
        }

        $tmpArray = array($imgDetails->getHtml(),$userId, $User_fullname->getHtml(), $span_UserName->getHtml());

        /**
         * @author giorgio 07/feb/2017
         *
         * if we're listing students, let's add more fields
         */
        if ($type!='authors' && $type!='tutors') array_push ($tmpArray, $studentDetailStr, implode('<br/>', $corsoStudi), $tutorFullName);

        array_push($tmpArray, $actions);
        /**
         * @author giorgio 29/mag/2013
         *
         * if we're listing students, let's add the stato field as well
         */
        if ($type!='authors' && $type!='tutors') array_push ($tmpArray, $isConfirmed);

        $tbody_data[] = $tmpArray;
    }
    $data = BaseHtmlLib::tableElement('id:table_users', $thead_data, $tbody_data);
    $data->setAttribute('class', $data->getAttribute('class').' '.$type.' '.ADA_SEMANTICUI_TABLECLASS);
} else {
    $data = CDOMElement::create('span');
    $data->addChild(new CText(translateFN('Non sono stati trovati utenti')));
}

$label = $profilelist;
$help = translateFN('Da qui il provider admin può vedere la '.$profilelist.' presenti sul provider');
$help .= ' ' .translateFN('Numero utenti'). ': '. $UserNum;

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
        JQUERY_DATATABLE,
		SEMANTICUI_DATATABLE,
        JQUERY_DATATABLE_DATE,
        ROOT_DIR. '/js/include/jquery/dataTables/selectSortPlugin.js',
        JQUERY_NO_CONFLICT
	);
$layout_dataAr['CSS_filename']= array(
        JQUERY_UI_CSS,
        SEMANTICUI_DATATABLE_CSS
	);
  $render = null;
  $optionsAr['onload_func'] = 'initDoc(\''.$type.'\');';
  ARE::render($layout_dataAr, $content_dataAr, $render, $optionsAr);

