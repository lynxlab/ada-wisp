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
		$saveButtonText = translateFN('Salva');
		$addChildOnError = true;
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
		$saveButtonText = translateFN('Rimuovi');
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
		$tableHead = array (null,'id',translateFN('cognome'), translateFN('nome'),
				translateFN('provincia'), translateFN('corso di studi'),
				translateFN('tipo iscrizione'),translateFN('stato iscrizione'),
				translateFN('disabilit&agrave;'), translateFN('voto maturità'));
		// invert selection button for footer
		$checkAllBtn = CDOMElement::create('button','type:button,name:selectAll');
		$checkAllBtn->addChild(new CText('Inverti Selezione'));
		// add submit button to footer
		$submitBtn = CDOMElement::create('button','type:submit');
		$submitBtn->addChild(new CText($saveButtonText));
		$tableFoot = array ($checkAllBtn->getHtml().$submitBtn->getHtml());
		// table body array
		$tableBody = array();

		foreach ($listStudentIds as $student_id) {
			// load the user from the db
			$userObj = MultiPort::findUser($student_id);
			if (is_object($userObj) && $userObj instanceof ADAUser) {
				// checkbox for first cell
				$checkBox = CDOMElement::create('checkbox','name:student_ids[],value:'.$userObj->getId());

				// CDS_DESC field, with tooltips
				$cds_desc = CDOMElement::create('span','class:tooltip');
				if (strlen($userObj->CDS_DESC)>0) $spanTitle[] = $userObj->CDSORD_DESC;
				if (strlen($userObj->PDSORD_DESC)>0) $spanTitle[] = $userObj->PDSORD_DESC;
				if (isset($spanTitle)) {
					$cds_desc->setAttribute('title', implode('<br/>', $spanTitle));
					unset($spanTitle);
				}
				$cds_desc->addChild(new CText($userObj->CDS_DESC));

				// Hack for dataTable filter: add an hidden span containing all of the titles text
				$hiddenHackSpan = CDOMElement::create('span');
				$hiddenHackSpan->setAttribute('style', 'display:none');
				$hiddenContents = array();
				// PT_DESC field
				$pt_desc = CDOMElement::create('span','class:tooltip');
				if (!is_null($userObj->PT_DESC)) {
					$pt_desc->setAttribute('title', $userObj->PT_DESC);
					$pt_desc->addChild(new CText(ADAUser::getShortCodeForField('PT_DESC', $userObj->PT_DESC)));
					if (!in_array($userObj->PT_DESC, $hiddenContents)) $hiddenContents[] = $userObj->PT_DESC;
				}

				// TIPO_DID_DECODE field
				$tipo_did_decode = CDOMElement::create('span','class:tooltip');
				if (!is_null($userObj->TIPO_DID_DECODE)) {
					$tipo_did_decode->setAttribute('title', $userObj->TIPO_DID_DECODE);
					$tipo_did_decode->addChild(new CText(ADAUser::getShortCodeForField('TIPO_DID_DECODE', $userObj->TIPO_DID_DECODE)));
					if (!in_array($userObj->TIPO_DID_DECODE, $hiddenContents)) $hiddenContents[] = $userObj->TIPO_DID_DECODE;
				}

				// STA_OCCUP_DECODE field
				$sta_occup_decode = CDOMElement::create('span','class:tooltip');
				if (!is_null($userObj->STA_OCCUP_DECODE)) {
					// Change 'Non lavoratore' to 'Inoccupato'
					if (strcasecmp($userObj->STA_OCCUP_DECODE, 'Non lavoratore')===0) {
						$userObj->STA_OCCUP_DECODE = 'Inoccupato';
					}
					$sta_occup_decode->setAttribute('title', $userObj->STA_OCCUP_DECODE);
					$sta_occup_decode->addChild(new CText(ADAUser::getShortCodeForField('STA_OCCUP_DECODE', $userObj->STA_OCCUP_DECODE)));

					if (!in_array($userObj->STA_OCCUP_DECODE, $hiddenContents)) $hiddenContents[] = $userObj->STA_OCCUP_DECODE;
				}
				$hiddenHackSpan->addChild(new CText(implode(' ', $hiddenContents)));

				// 'stato iscrizione field
				$stato_iscr_des = CDOMElement::create('span');
				$stato_iscr_str = '';
				if (!is_null($userObj->AA_ISCR_DESC)) $stato_iscr_str .= $userObj->AA_ISCR_DESC;
				if (!is_null($userObj->ANNO_CORSO)) $stato_iscr_str .= '('.$userObj->ANNO_CORSO.')';
				if (!is_null($userObj->TASSE_IN_REGOLA_OGGI)) {
					if (strcasecmp($userObj->TASSE_IN_REGOLA_OGGI, 'no')===0) {
						$tasseText = 'Non in regola';
					} else {
						$tasseText = 'In regola';
					}
					$tasse = CDOMElement::create('span','class:tooltip, title:'.$tasseText);
					$tasse->addChild(new CText(ADAUser::getShortCodeForField('TASSE_IN_REGOLA_OGGI', $userObj->TASSE_IN_REGOLA_OGGI)));
					$stato_iscr_str .= ', '.$tasse->getHtml();
				}
				if (strlen($stato_iscr_str)>0) $stato_iscr_des->addChild(new CText($stato_iscr_str));

				// TIPO_HAND_DES field
				$tipo_hand_des = CDOMElement::create('span','class:tooltip');
				if (strlen($userObj->TIPO_HAND_DES)>0) {
					$tipo_hand_des->addChild(new CText($userObj->TIPO_HAND_DES));
					if (strlen($userObj->PERC_HAND)>0) {
						$tipo_hand_des->setAttribute('title', $userObj->PERC_HAND);
					}
				} else {
					$tipo_hand_des->addChild(new CText('N/A'));
				}
				// VOTO field
				if (strlen($userObj->VOTO)>0) {
					$voto = $userObj->VOTO;
					if (strlen($userObj->VOTO_MAX)>0) $voto .= '/'.$userObj->VOTO_MAX;
				} else $voto = null;

				$voto_des = CDOMElement::create('span','class:tooltip');
				if (!is_null($voto)) {
					$voto_tooltip = array();
					if (!is_null($userObj->TIPO_TITOLO_DESC)) $voto_tooltip[] = htmlentities($userObj->TIPO_TITOLO_DESC,ENT_QUOTES,ADA_CHARSET);
					if (!is_null($userObj->PROVINCIA_SCUOLA_DESC)) $voto_tooltip[] = htmlentities($userObj->PROVINCIA_SCUOLA_DESC,ENT_QUOTES,ADA_CHARSET);
					if (count($voto_tooltip)>0) {
						$voto_des->setAttribute('title', implode('<br/>', $voto_tooltip));
					}
					$voto_des->addChild(new CText($voto));
				}

				// table body row, according to header
				$tableBody[] = array ( $checkBox->getHtml(), $userObj->getId(), $userObj->getLastName(),
						$userObj->getFirstName(), $userObj->getProvincia(),$cds_desc->getHtml(),
						$pt_desc->getHtml().'&nbsp'.$tipo_did_decode->getHtml().'&nbsp;'.
						$sta_occup_decode->getHtml().$hiddenHackSpan->getHtml(),
						$stato_iscr_des->getHtml(), $tipo_hand_des->getHtml(), $voto_des->getHtml()
				);
			}
		}
		// add table to main form
		$preAssignTable = BaseHtmlLib::tableElement('id:table_preassignment', $tableHead, $tableBody, $tableFoot, $tableCaption);
		$preAssignTable->setAttribute('class', $preAssignTable->getAttribute('class').' '.ADA_SEMANTICUI_TABLECLASS);
		$theForm->addChild($preAssignTable);

	} else {
		if (isset($addChildOnError) && $addChildOnError) {
			$errorSPAN = CDOMElement::create('span','class:preassign_error');
			$errorSPAN->addChild(new CText($noStudentsError));
			$data->addChild($errorSPAN);
		}
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
		SEMANTICUI_DATATABLE,
		JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename']= array(
		JQUERY_UI_CSS,
		SEMANTICUI_DATATABLE_CSS
);
$optionsAr['onload_func'] = 'initDoc(\''.$op.'\');';
ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);