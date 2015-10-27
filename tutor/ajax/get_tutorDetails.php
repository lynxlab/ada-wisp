<?php

/**
 * get_tutorDetails.php - return table with user details
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
require_once ROOT_DIR . '/comunica/include/ChatRoom.inc.php';
// require_once ROOT_DIR . '/comunica/include/ChatDataHandler.inc.php';
$retArray=array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
	isset($_GET['id_tutor']) && intval($_GET['id_tutor'])>0) {
	$id_tutor = intval($_GET['id_tutor']);
	$caption = translateFN('Dettagli tutor');
	$thead_data = array(
		translateFN('Servizio'),
		translateFN('Studente'),
		translateFN('Rich. Aiuto'),
		translateFN('Proposte'),
		translateFN('Confermati'),
		translateFN('Effettuati'),
		translateFN('No. Login'),
		translateFN('Ultimo Login'),
		translateFN('Patto Form.'),
		translateFN('Person.'),
		translateFN('Note Scri'),
		translateFN('Note Let'),
		translateFN('File Inviati'),
		translateFN('Chat')
	);
	
	$DetailsAr=$dh->get_tutors_assigned_course_instance($id_tutor);
	if(!AMA_DB::isError($DetailsAr) && is_array($DetailsAr) && count($DetailsAr)>0) {
		$DetailsAr = $DetailsAr[$id_tutor];
	}
	
	$detailsResults=array();
	
	if(!AMA_DB::isError($DetailsAr) && is_array($DetailsAr) && count($DetailsAr)>0) {
		
		$helpInstances = array();
		
		foreach($DetailsAr as $course){
			
			if (!is_null($course['tipo_servizio']) && $course['tipo_servizio']==ADA_SERVICE_HELP) {
				// all instances of service level ADA_SERVICE_HELP will be processed afterwards
				$helpInstances[] = $course;
				// must keep calculation of nodes, notes, etc. that will be aggregated afterwards
			}
			
			$added_nodes_count = 0;
			$read_notes_count = 0;
			$chatlines_count = 0;			
			if (isset($course['id_corso']) && isset($course['id_istanza_corso'])) {
				
				$out_fields_ar = array();
				// count written (aka added) forum notes
				$clause =  "tipo = '".ADA_NOTE_TYPE."' AND id_utente = ".$id_tutor.
						   " AND id_nodo LIKE '".$course['id_corso']."\_%'".
						   " AND id_istanza=".$course['id_istanza_corso'];
				$nodes = $dh->find_course_nodes_list($out_fields_ar, $clause,$course['id_corso']);
				$added_nodes_count = count($nodes);
				
				/**
				 * get tutor visit for course instance (to count read notes)
				 * the method name refers to student, but works ok for a tutor as well
				 */
				$visits = $GLOBALS['dh']->get_student_visits_for_course_instance($id_tutor, $course['id_corso'], $course['id_istanza_corso']);				
				if (!AMA_DB::isError($visits) && is_array($visits) && count($visits)>0) {
					foreach ($visits as $visit) {
 						if ($visit['tipo']==ADA_NOTE_TYPE && 
 							$visit['id_utente']!=$id_tutor && 
 							intval($visit['numero_visite'])>0) $read_notes_count++;
					}
				}
				
				/**
				 * count class chat messages written by the tutor
				 */
				$class_chatrooms = ChatRoom::get_all_class_chatroomsFN($course['id_istanza_corso']);
				if (!AMA_DB::isError($class_chatrooms) && is_array($class_chatrooms) && count($class_chatrooms)>0) {
					foreach ($class_chatrooms as $aChatRoom) {
						$mh = MessageHandler::instance($_SESSION['sess_selected_tester_dsn']);
						$chat_data = $mh->find_chat_messages($id_tutor, ADA_MSG_CHAT, $aChatRoom[0], '', 'id_mittente='.$id_tutor);
						if (!AMA_DB::isError($chat_data) && is_array($chat_data) && count($chat_data)>0) {
							$chatlines_count = count($chat_data);
						}
					}
				}
				
				/**
				 * count files uploaded, for each course
				 */
				$courseObj = new Course($course['id_corso']);
				$uploadedFiles = 0;
				if ($courseObj->isFull()) {
					// 01. find the course media path
					if($courseObj->media_path != "") {
						$media_path = $courseObj->media_path;
					} else {
						$media_path = MEDIA_PATH_DEFAULT . $courseObj->id_autore;
					}
					$download_path = $root_dir . $media_path;
					$elencofile = leggidir($download_path);
					// 02. loop the $media_path dir looking for files
					// uploaded by $id_tutor in the current course and course instance
					if (!is_null($elencofile)) {
						foreach ($elencofile as $singleFile) {
							$complete_file_name = $singleFile['file'];
							$filenameAr = explode('_',$complete_file_name);
							$course_instance = isset($filenameAr[0]) ? $filenameAr[0] : null;
							$id_sender  = isset($filenameAr[1]) ? $filenameAr[1] : null;
							$id_course = isset($filenameAr[2]) ? $filenameAr[2] : null;
							if ($id_course==$course['id_corso'] &&
								$course_instance==$course['id_istanza_corso'] &&
								$id_sender==$id_tutor) $uploadedFiles++;
						}
					}
				}
					
			}
			
			$detailsResults[$course['id_istanza_corso']] = array( 
				'title'=>$course['titolo'],
				'student'=>'&nbsp;',
				'helprequests'=>0,
				'appproposals'=>0,
				'appconfirmed'=>0,
				'apprealized'=>0,
				'logincount'=>0,
				'lastlogin'=>'&nbsp;',
				'patto'=>'&nbsp;',
				'personalpatto'=>'&nbsp;',
				'addednodes'=>$added_nodes_count,
				'readnodes'=>$read_notes_count,
				'uploadedfiles'=>$uploadedFiles,
				'chatlines'=>$chatlines_count);
		}
		
		/**
		 * now processing instances having a service level of ADA_SERVICE_HELP
		 */
		$processedCourseIDs = array();
		$studentSummary = array();
		$loginCounts = array();
		
		$mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
		$clause = '`titolo` LIKE \'%d\_%d\_%d%%\' AND `id_mittente`=%d AND (`flags` & %d)';
		
		$totStandard = 0;
		$totPerson = 0;
		foreach ($helpInstances as $helpInstance) {
			/**
			 * must aggregate by student and course:
			 * every row is the sum of all instances of the
			 * current course, each row representing a student
			 */
			if (!in_array($helpInstance['id_corso'], $processedCourseIDs)) {
				$processedCourseIDs[] = $helpInstance['id_corso'];
				$instancesToLoop = $dh->get_tutors_assigned_course_instance($id_tutor, $helpInstance['id_corso']);
				if (!AMA_DB::isError($instancesToLoop) && is_array($instancesToLoop[$id_tutor]) && count($instancesToLoop[$id_tutor])>0) {
					// now instancesToLoop contains all instances of the course to which tutor is assigned
					foreach ($instancesToLoop[$id_tutor] as $anInstance) {
						$students = $dh->get_students_for_course_instance($anInstance['id_istanza_corso']);
						if (!AMA_DB::isError($students) && is_array($students) && count($students)>0) {
							foreach ($students as $aStudent) {
								// msgs having ADA_EVENT_PROPOSED
								$msgsProp = $mh->find_messages($aStudent['id_utente'],ADA_MSG_AGENDA,array('titolo'),
										sprintf($clause, $aStudent['id_utente'], $id_tutor,
												$anInstance['id_istanza_corso'], $id_tutor, ADA_EVENT_PROPOSED),
									'data_ora desc');
								$appProposals = 0;
								if (!AMA_DB::isError($msgsProp)) $appProposals = count($msgsProp);
								else $msgsProp = array();
								
								// msgs having ADA_EVENT_CONFIRMED
								$msgsConf = $mh->find_messages($aStudent['id_utente'],ADA_MSG_AGENDA,array('titolo'),
										sprintf($clause, $aStudent['id_utente'], $id_tutor,
												$anInstance['id_istanza_corso'], $aStudent['id_utente'], ADA_EVENT_CONFIRMED),
									'data_ora desc');
								$appConfirmed = 0;
								if (!AMA_DB::isError($msgsConf)) $appConfirmed = count($msgsConf);
								else $msgsConf = array();
								
								// event having an event_token=message_event_token in sessione_eguidance table are realized								
								$appRealized = 0;
								foreach (array_merge($msgsProp, $msgsConf) as $aMessage) {
									$token = ADAEventProposal::extractEventToken($aMessage);
									$eguidRES = $dh->get_eguidance_session_with_event_token($token);
									if (!AMA_DB::isError($eguidRES) && is_array($eguidRES) && count($eguidRES)>0) {
										if (!isset($tipoPatto) && isset($eguidRES['tipo_patto_formativo']) && 
										    !isset($studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['patto'])) {
										    $tipoPatto = $pattoFormativoAr[$eguidRES['tipo_patto_formativo']];
										    if ($eguidRES['tipo_patto_formativo']==0) $totStandard++; 
										    else if ($eguidRES['tipo_patto_formativo'] > 0) $totPerson++;
										}
										if ($eguidRES['tipo_patto_formativo'] > 0 && !isset($personalPatto) && isset($eguidRES['tipo_personalizzazione'])) $personalPatto = $tipoPersonalPattoAr[$eguidRES['tipo_personalizzazione']];					
										$appRealized++;
									}
								}
								
								// login counts
								$loginInfo = abstractLogin::getUserLoginInfo($aStudent['id_utente']);
								$loginCounts[$aStudent['id_utente']] = (AMA_DB::isError($loginInfo)) ? '0' : $loginInfo['loginCount'];
								
								if (!isset($studentSummary[$aStudent['id_utente']][$anInstance['id_corso']])) {
									// prepare student summary row
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']] = array(
											'title'=>$anInstance['titolo'],
											'student'=>$aStudent['cognome'].' '.$aStudent['nome'],
											'helprequests'=>1,
											'appproposals'=>$appProposals,
											'appconfirmed'=>$appConfirmed,
											'apprealized'=>$appRealized,
											'logincount'=>$loginCounts[$aStudent['id_utente']],
											'lastlogin'=>(AMA_DB::isError($loginInfo)) ? '&nbsp;' : AMA_DataHandler::ts_to_date($loginInfo['date']),
											'patto'=>isset($tipoPatto) ? $tipoPatto : '&nbsp;',
											'personalpatto'=>isset($personalPatto) ? $personalPatto : '&nbsp;',
											'addednodes'=>0,
											'readnodes'=>0,
											'uploadedfiles'=>0,
											'chatlines'=>0
									);
									unset($tipoPatto);
									unset($personalPatto);
								} else {
									// update student summary row
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['helprequests']++;
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['appproposals'] += $appProposals;
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['appconfirmed'] += $appConfirmed;
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['apprealized']  += $appRealized;
								}
								
								// PREVIOUSLY LOADED DATA AGGREATION: add results to current student and course
								if (isset($detailsResults[$anInstance['id_istanza_corso']])) {
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['addednodes'] += $detailsResults[$anInstance['id_istanza_corso']]['addednodes'];
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['readnodes'] += $detailsResults[$anInstance['id_istanza_corso']]['readnodes'];
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['uploadedfiles'] += $detailsResults[$anInstance['id_istanza_corso']]['uploadedfiles'];
									$studentSummary[$aStudent['id_utente']][$anInstance['id_corso']]['chatlines'] += $detailsResults[$anInstance['id_istanza_corso']]['chatlines'];
									unset ($detailsResults[$anInstance['id_istanza_corso']]);
								}								
							} // end foreach $students
						} // if !isError($students)
					} // end foreach $instancesToLoop
				} // if !isError $instancesToLoop
			} // if !in_array $helpInstance['id_corso']
		} // end foreach $helpInstances
		
		// done! add studentsummary results to output table
		if (count($studentSummary)>0 && is_array($studentSummary)) {
			foreach ($studentSummary as $aSummary) {
				if (count($aSummary)>0 && is_array($aSummary)) {
					foreach ($aSummary as $k=>$row) array_push($detailsResults, $row); 
				}
			}
		}
		
		
		$tfoot_data = array (
				count($detailsResults).' '.translateFN('Servizi totali'),
				'&nbsp;',
				array_sum(array_map(function($element) { return $element['helprequests']; }, $detailsResults)),
				array_sum(array_map(function($element) { return $element['appproposals']; }, $detailsResults)),
				array_sum(array_map(function($element) { return $element['appconfirmed']; }, $detailsResults)),
				array_sum(array_map(function($element) { return $element['apprealized']; }, $detailsResults)),
				array_sum($loginCounts),
				'&nbsp;',
				$totStandard,
				$totPerson,
				array_sum(array_map(function($element) { return $element['addednodes']; }, $detailsResults)),
				array_sum(array_map(function($element) { return $element['readnodes']; }, $detailsResults)),
				array_sum(array_map(function($element) { return $element['uploadedfiles']; }, $detailsResults)),
				array_sum(array_map(function($element) { return $element['chatlines']; }, $detailsResults))
		);
		
		$result_table = BaseHtmlLib::tableElement('class:tutor_table', $thead_data, $detailsResults,$tfoot_data,$caption);
		$result=$result_table->getHtml();
		$retArray['columnDefs'][] = array(
				'sClass'=>'centerAlign',
				'aTargets'=>[2,3,4,5,6,7,8,9,10,11,12,13]
		);
		$retArray['columnDefs'][] = array(
				'sType'=>'date-eu',
				'aTargets'=>[7]
		);
		$retArray['status']='OK';
		$retArray['html']=$result;
	} else {
		$span_error = CDOMElement::create('span');
		$span_error->setAttribute('class', 'ErrorSpan');
		$span_error->addChild(new CText(translateFN('Nessun dato trovato')));
		$retArray['status']='ERROR';
		$retArray['html']=$span_error->getHtml();
	}
	echo json_encode($retArray);
}