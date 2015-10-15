<?php

/**
 * generateInstance.php - generates a course instance before showing appointment page to tutor
 *
 * @package
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout');

/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
*/
$neededObjAr = array(
    AMA_TYPE_TUTOR => array('layout')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once '../include/tutor_functions.inc.php';

$retArray=array('status'=>'ERROR','msg'=>translateFN('Errore sconosciuto'));

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
	isset($_POST['courseID'])  && intval($_POST['courseID'])>0 &&
	isset($_POST['studentID']) && intval($_POST['studentID'])>0) {
		
	$courseID  = intval($_POST['courseID']);
	$studentID = intval($_POST['studentID']);
	$tutorID = $userObj->getId();
	
	$istanza_ha = array(
			'data_inizio'=>0,
			'durata'=>'365',
			'data_inizio_previsto'=>AMA_DataHandler::date_to_ts("now"),
			'id_layout'=>NULL,
			'self_instruction' => 0,
			'open_subscription' => 1,
			'self_registration' => 0
	);
	// 01. add an instance to tester db 
	$instanceID = $dh->course_instance_add($courseID, $istanza_ha);
	if (!AMA_DB::isError($instanceID)) {
		// 02. presubscribe student
		if (!AMA_DB::isError($dh->course_instance_student_presubscribe_add($instanceID,$studentID))) {
			// 03. subscribe tutor
			if (!AMA_DB::isError($dh->course_instance_tutor_subscribe($instanceID, $tutorID))) {
				// 04. subscribe student
				if (!AMA_DB::isError($dh->course_instance_student_subscribe($instanceID, $studentID, ADA_STATUS_SUBSCRIBED))) {
					// 05. set the course instance as started
					$ci_info = $dh->course_instance_get($instanceID,true);
					if ($ci_info['data_inizio'] == 0) {
						$ci_info['data_inizio'] = $dh->date_to_ts('now');
						// End date is automatically set by method course_instance_set
						$result = $dh->course_instance_set($instanceID, $ci_info);
					} else $result = true;
					if (!AMA_DB::isError($result)) {
						// 06. build a note with preset title and body 
						$helpNodeName = translateFN('Invito da parte del tutor');
						$helpText = translateFN('Discussione avviata dal tutor');
						
						$node_data = array(
								'name' => $helpNodeName,
								'type' => ADA_GROUP_TYPE,
								'id_node_author' => $studentID,
								'parent_id' => $courseID.'_0',
								'type' => ADA_NOTE_TYPE,
								'text' => $helpText,
								'id_course'=> $courseID,
								'id_instance' => $instanceID
						);
						require_once ROOT_DIR . '/services/include/NodeEditing.inc.php';
						if (!AMA_DB::isError(NodeEditing::createNode($node_data))) {
							// 07. if we've done through here, eventually return the generated instance ID
							$retArray['status'] = 'OK';
							$retArray['instanceID'] = $instanceID;
						} else {
							$retArray['msg'] = translateFN('Errore durante la generazione del primo nodo della timeline');
						}
					} else {
						$retArray['msg'] = translateFN('Errore durante l\'impostazione stato INIZIATO all\'istanza');
					}
				} else {
					$retArray['msg'] = translateFN('Errore durante l\'iscrizione dello studente all\'istanza');
				}
			} else {
				$retArray['msg'] = translateFN('Errore durante l\'iscrizione del tutor all\'istanza');
			}
		} else {
			$retArray['msg'] = translateFN('Errore durante la preiscrizione studente');
		}
	} else {
		$retArray['msg'] = translateFN('Errore durante la creazione di un\'istanza del servizio');
	}
}

header('Content-Type: application/json');
if (isset($retArray)) echo json_encode($retArray);
die();