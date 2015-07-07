<?php
/**
 * Preassign users - this module lets the swithcer preassign a tutor to students
 *
 *
 * @package
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
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
$self = whoami();

include_once 'include/switcher_functions.inc.php';
/*
 * YOUR CODE HERE
 */
if (isset($_GET['practitioner_id']) && intval($_GET['practitioner_id'])>0) $op = 'edit';
else $op = 'preassign';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'  &&
	isset($_POST['student_ids']) && is_array($_POST['student_ids']) &&
	isset($_POST['selTutor']) && intval($_POST['selTutor'])>0) {
		
	if ($op==='preassign') {
	 	$res = $GLOBALS['dh']->preassign_students_to_tutor($_POST['student_ids'],intval($_POST['selTutor']));
	 	$url = HTTP_ROOT_DIR . $_SERVER['SCRIPT_NAME'];		
	} else if ($op==='edit') {
		$res = $GLOBALS['dh']->remove_preassign_students_to_tutor($_POST['student_ids'],intval($_POST['selTutor']));
		$url = HTTP_ROOT_DIR . $_SERVER['SCRIPT_NAME'].'?practitioner_id='.intval($_POST['selTutor']);
	}
	
	// add error handling on $res if needed
	redirect($url);
// http://macerata.localwisp.com/switcher/preassign_practitioner.php?practitioner_id=11 	
}

// get all tutors
$field_list_ar = array('nome', 'cognome');
$tutors_ar = $dh->get_tutors_list($field_list_ar);

// translated error messages to be passed to javascript
$selectATutorMSG = translateFN('Selezionare un orientatore');
$selectAStudentMSG = translateFN('Seleziona almeno uno studente');				

if (!AMA_DB::isError($tutors_ar) && is_array($tutors_ar) && count($tutors_ar)>0) {

	// add a dummy select option if preassign
	if ($op==='preassign') array_unshift($tutors_ar, array ('0', translateFN('Seleziona un orientatore'),''));
	
	// generate array for BaseHtmlLib::selectElement2 method
	foreach ($tutors_ar as $tutor_el) $tutorSelect[$tutor_el[0]] = $tutor_el[1].' '.$tutor_el[2];
		
	// get selected tutor id, or select first element
	reset($tutorSelect);
	if (isset($_GET['practitioner_id']) && intval($_GET['practitioner_id'])>0) {
		$selectedTutorID = intval($_GET['practitioner_id']);
	} else {
		$selectedTutorID = key($tutorSelect);
	}
	
	// set options for each supported $op	
	if ($op==='preassign') {
		$listStudentIds = $GLOBALS['dh']->get_non_preassigned_student_ids();
		$onsubmit = 'return checkPreassignForm(\''.
					addslashes($selectATutorMSG).'\',\''.addslashes($selectAStudentMSG).'\');';
		$tutorLabelTxt = translateFN('Seleziona un orientatore a cui preassegnare gli studenti');
		$tableCaption = translateFN('Studenti da preassegnare');
		$noStudentsError = translateFN('Tutti gli studenti sono già preassegnati a qualche orientatore');
		$help = translateFN('Da qui il provider admin può preassegnare gli studenti a un orientatore');
		$addChildOnError = false;
	} else if ($op==='edit') {
		$listStudentIds = $GLOBALS['dh']->get_preassigned_students_for_tutor($selectedTutorID);
		$question = translateFN('Confermi l\'operazione?');		
		$onsubmit = 'return checkEditPreassignForm(\''.
				addslashes($selectATutorMSG).'\',\''.
				addslashes($selectAStudentMSG).'\',\''.
				addslashes($question).'\');';		
		$tutorLabelTxt = translateFN('Seleziona un orientatore a cui rimuovere la preassegnazione degli studenti');
		$tableCaption = translateFN('Studenti preassegnati');
		$noStudentsError = translateFN('Nessuno studente trovato');
		$help = translateFN('Da qui il provider admin può rimuovere la preassegnazione degli studenti a un orientatore');
		$addChildOnError = true;
	}
	
	// main container div
	$data = CDOMElement::create('div','id:preassign_students_container');
	// main form object
	$theForm = CDOMElement::create('form','name:preassing_students,method:post');
	// select tutor div container
	$selTutorDIV = CDOMElement::create('div','id:selTutor_container');
	$theForm->addChild($selTutorDIV);		
	$data->addChild($theForm);
	// select tutor label
	$selTutorLabel = CDOMElement::create('label','for:selTutor');
	$selTutorLabel->addChild(new CText($tutorLabelTxt.': '));		
	$selTutorDIV->addChild($selTutorLabel);
	// select tutor select object
	$selTutorDIV->addChild(BaseHtmlLib::selectElement2('id:selTutor,name:selTutor',$tutorSelect,$selectedTutorID));
	
	if ($op==='preassign') {
		$editButtonDIV = CDOMElement::create('div','id:editButton_container');
		$editButton = CDOMElement::create('button','type:button,class:editButton');
		$editButton->setAttribute('onclick', 'goToEdit(\''.$selectATutorMSG.'\');');
		$editButton->addChild(new CText(translateFN('Modifica preassegnazioni')));
		$editButtonDIV->addChild($editButton);
		$theForm->addChild($editButtonDIV);
	}
	
	if (!AMA_DB::isError($listStudentIds) && is_array($listStudentIds) && count($listStudentIds)>0) {
		// onsubmit check form with javascript
		$theForm->setAttribute('onsubmit', $onsubmit);
		// table header
		$tableHead = array (null,'id','nome','cognome','email');
		// invert selection button for footer
		$checkAllBtn = CDOMElement::create('button','type:button,name:selectAll');
		$checkAllBtn->addChild(new CText('Inverti Selezione'));
		$tableFoot = array ($checkAllBtn->getHtml());
		// make footer same size of header
		foreach ($tableHead as $key=>$val) if ($key>0) $tableFoot[$key] = null;
		// add submit button to footer
		$submitBtn = CDOMElement::create('button','type:submit');
		$submitBtn->addChild(new CText('Salva'));
		$tableFoot[count($tableHead)-1] = $submitBtn->getHtml();
		// table body array
		$tableBody = array();
		
		foreach ($listStudentIds as $student_id) {
			// load the user from the db		
			$userObj = MultiPort::findUser($student_id);
			if (is_object($userObj) && $userObj instanceof ADAUser) {
				// checkbox for first cell
				$checkBox = CDOMElement::create('checkbox','name:student_ids[],value:'.$userObj->getId());
				// table body row, according to header
				$tableBody[] = array ( $checkBox->getHtml(), $userObj->getId(), $userObj->getFirstName(),
						$userObj->getLastName(), $userObj->getEmail());
			}
		}
		// add table to main form
		$theForm->addChild(BaseHtmlLib::tableElement('id:table_preassignment',
				$tableHead, $tableBody, $tableFoot, $tableCaption));
		
	} else {
		if (isset($addChildOnError) && $addChildOnError) $data->addChild(new CText($noStudentsError));
		else $data = new CText($noStudentsError);
	}
} else $data = new CText(translateFN('Nessun orientatore trovato'));

$label = translateFN('Preassegna studenti');

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => isset($help) ? $help : '',
    'data' => $data->getHtml(),
    'edit_profile'=>$userObj->getEditProfilePage(),
    'module' => isset($module) ? $module : '',
    'messages' => $user_messages->getHtml(),
    'user_avatar'=>$avatar->getHtml(),
	'user_modprofilelink' => $userObj->getEditProfilePage()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY_UI,
		JQUERY_DATATABLE,
		JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename']= array(
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS
);
$optionsAr['onload_func'] = 'initDoc(\''.$op.'\');';
ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);