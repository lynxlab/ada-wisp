<?php
/**
 * TUTOR.
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
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = tutor!

include_once 'include/'.$self.'_functions.inc.php';

/*
 * YOUR CODE HERE
 */

include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
include_once 'include/tutor.inc.php';

if (!isset($_GET['mode'])) {
  $mode = "load";
}
else {
  $mode = $_GET['mode'];
}
// ini_set ('display_errors','1'); error_reporting(E_ALL);

if (!isset($op)) $op = null;

/**
 * check if it's not a supertutor asking for op='tutor'
 * then set $op to make the default action
 */
if (!$userObj->isSuper() && $op=='tutor') $op=null;

switch ($op) {
	case 'tutor':
		$self = 'supertutor';
		$help = '';
		$fieldsAr = array('nome','cognome','username');
		$tutorsAr = $dh->get_tutors_list($fieldsAr);
		if (!AMA_DB::isError($tutorsAr) && is_array($tutorsAr) && count($tutorsAr)>0) {
			$tableDataAr = array();
			$imgDetails = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/details_open.png');
			$imgDetails->setAttribute('title', translateFN('visualizza/nasconde i dettagli del tutor'));
			$imgDetails->setAttribute('style', 'cursor:pointer;');
			$imgDetails->setAttribute('class', 'tooltip');

			$mh = MessageHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

			foreach ($tutorsAr as $aTutor) {
				// open details button
				$imgDetails->setAttribute('onclick',"toggleTutorDetails(".$aTutor[0].",this);");

				// login counts
				$loginInfo = abstractLogin::getUserLoginInfo($aTutor[0]);
				if (!AMA_DB::isError($loginInfo) && is_array($loginInfo) && count($loginInfo)>0) {
					$loginInfo['date'] = AMA_DataHandler::ts_to_date($loginInfo['date']);
				} else $loginInfo = array ('loginCount'=>0,'date'=>'');

				// received messages
				$receivedMessages = 0;
				$msgs_ha = $mh->get_messages($aTutor[0],ADA_MSG_SIMPLE);
				if (!AMA_DataHandler::isError($msgs_ha)) {
					$receivedMessages = count($msgs_ha);
				}
				// sent messages
				$sentMessages = 0;
				$msgs_ha = $mh->get_sent_messages($aTutor[0], ADA_MSG_SIMPLE);
				if (!AMA_DataHandler::isError($msgs_ha)) {
					$sentMessages = count($msgs_ha);
				}
				$tableDataAr[] = array_merge(array($imgDetails->getHtml()),$aTutor,
											 array($loginInfo['loginCount'],$loginInfo['date']),
											 array($receivedMessages,$sentMessages));
			}
		}
		$thead = array(null,
				translateFN('Id'),
				translateFN('Nome'),
				translateFN('Cognome'),
				translateFN('username'),
				translateFN('No. Login'),
				translateFN('Ultimo Login'),
				translateFN('Msg Ric'),
				translateFN('Msg Inv')
		);
		$tObj = BaseHtmlLib::tableElement('id:listTutors',$thead,$tableDataAr,null,translateFN('Elenco dei tutors'));
		$tObj->setAttribute('class', 'default_table doDataTable');
		$data = $tObj->getHtml();
		break;
	case 'stats':
	case 'student':
		include_once ROOT_DIR.'/config/config_class_report.inc.php';

		$self = 'default';

		// Show the students subscribed in selected course and a report
		if(!isset($id_course)) {
			$id_course = $dh->get_course_id_for_course_instance($id_instance);
			if(AMA_DataHandler::isError($id_course)) {
				$id_course = 0;
			}
		}
		if ($mode=='update') {
			$courses_student = get_student_coursesFN($id_instance,$id_course,$order);
		} else {
			// load
			$courses_student = get_student_courses_from_dbFN($id_course, $id_instance);
		}

		$data = $courses_student;
	break;

	case 'student_notes':   // nodi inseriti dallo studente
	case 'student_notes_export':

		$self = 'default';

		$student_dataHa = $dh->_get_user_info($id_student);
		$studente_username = $student_dataHa['username'];
		//          if (isset($id_course)){    // un corso (e un'istanza...) alla volta ?
		$sub_course_dataHa = array();
		$today_date = $dh->date_to_ts("now");
		$clause = "data_inizio <= '$today_date' AND data_inizio != '0'";
		$field_ar = array('id_corso','data_inizio','data_inizio_previsto');
		$all_instance = $dh->course_instance_find_list($field_ar,$clause);
		if (is_array($all_instance)) {
			$added_nodesHa = array();
			foreach ($all_instance as $one_instance) {
				//mydebug(__LINE__,__FILE__,$one_instance);
				$id_course_instance = $one_instance[0];
				//check on tutor:
				//           $tutorId = $dh->course_instance_tutor_get($id_course_instance);
				//           if (($tutorId == $sess_id_user)  AND ($id_course_instance == $sess_id_course_instance))
				// warning: 1 tutor per class ! ELSE: $tutored_instancesAr = $dh->course_tutor_instance_get($sess_id_user); etc
				// check only on course_instance
				if  ($id_course_instance == $id_instance) {
					$id_course = $one_instance[1];
					$data_inizio = $one_instance[2];
					$data_previsto = $one_instance[3];
					$sub_courses = $dh->get_subscription($id_student, $id_instance);
					//mydebug(__LINE__,__FILE__,$sub_courses);
					if ($sub_courses['tipo'] == 2) {
						$out_fields_ar = array('nome','titolo','id_istanza','data_creazione','testo');
						$clause = "tipo = '2' AND id_utente = '$id_student'";
						$nodes = $dh->find_course_nodes_list($out_fields_ar, $clause,$id_course);
						$course = $dh->get_course($id_course);
						$course_title = $course['titolo'];
						foreach ($nodes as $one_node) {
							$row = array(
									translateFN('Servizio')=>$course_title,
									//      translateFN('Edizione')=>$id_course_instance."(".ts2dFN($data_inizio).")",
									translateFN('Data')=>ts2dFN($one_node[4]),
									// translateFN('Nodo')=>$one_node[0],
									translateFN('Titolo')=>"<a href=\"$http_root_dir/browsing/sview.php?id_node=".$one_node[0]."&id_course=$id_course&id_course_instance=$id_instance#".$one_node[0]."\">".$one_node[1]."</a>"
									//    translateFN('Keywords')=>$one_node[2]
							);
							array_push($added_nodesHa,$row);
							// exporting  to RTF
							$note =  ts2dFN($one_node[4])."\n".
									$one_node[1]."\n". // title
									$one_node[5]."\n"; //text


						}
					}

				}
			}
		}

		if ($op == 'student_notes_export') {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			// always modified
			header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");                          // HTTP/1.0
			//header("Content-Type: text/plain");
			header("Content-Type: application/rtf");
			//header("Content-Length: ".filesize($name));
			header("Content-Disposition: attachment; filename=forum_".$id_course."_class_".$id_instance."_student_".$id_student.".rtf");
			echo $node_index;
			exit;
		} else {
			$tObj = new Table();
			$tObj->initTable('1','center','0','1','90%','','','','','1','0','','summary','userSummary');
			$caption = "<strong>".translateFN("Interventi dell'utente")." ".$student_dataHa['nome']." ".$student_dataHa['cognome']."</strong>";
			$summary = strip_tags($caption);
			$tObj->setTable($added_nodesHa,$caption,$summary);
			$added_notesHa = $tObj->getTable();
			$data = $added_notesHa;
		}

	break;

	case 'zoom_student' :
		if (isset ($id_student) && intval($id_student)>0 )
		{
			header ('Location: zoom_user.php?id='.intval($id_student));
			exit();
		}
	// if id_student is not set or invalid fall into the default

	default:
		/*
		 * Cosa deve esserci:
		 * 1. agenda del giorno
		 * 2. messaggi eventuali dello switcher del tester
		 * 3. lista degli utenti assegnati all'EP
		 * 4. titoli di messaggi di altri utenti
		 *
		 * nel menu:
		 * messaggeria, agenda
		 */

		/*
		 * 3. Lista degli utenti assegnati all'EP
		 */

		$clients_list = $dh->get_list_of_tutored_users($userObj->getId());
		$thead_data = array(translateFN('utente'),translateFN('azioni'),translateFN('servizio'),translateFN('stato'),translateFN('data inizio'));
		//$thead_data = array(translateFN('utente'),translateFN('azioni'),translateFN('servizio'),translateFN('stato'),translateFN('data inizio'),translateFN('durata servizio'), translateFN('data fine'));
		$tbody_data = array();
		if (is_array($clients_list) && sizeof($clients_list) > 0) {

		  /**
           * @author giorgio 18/nov/2013
           * gets the list of services of ADA_SERVICE_HELP level
           * in the selected provider
		   */
		  $clauseSQL = '(s.livello='. ADA_SERVICE_HELP. ' OR s.livello='. ADA_SERVICE_IN_ITINERE .')' ;
		  if (!MULTIPROVIDER) $clauseSQL .= ' AND st.id_tester = '. $GLOBALS['testers_dataAr'][$GLOBALS['user_provider']];
		  $tempResults = $common_dh->get_services(null, $clauseSQL);
		  $serviceHelpIDs = array();
		  if (!AMA_DB::isError($tempResults) && !empty($tempResults))
		  {
		  	foreach ($tempResults as $tempResult) $serviceHelpIDs[] = $tempResult[3];
		  }

		  $user_history_link_label = translateFN('View service status');
		  $appointment_link_label  = translateFN('Proponi appuntamento');
		  $status_opened_label     = translateFN('In corso');
		  $status_closed_label     = translateFN('Terminato');
		  $timeline_link_label = translateFN('Entra nella timeline');

		foreach($clients_list as $user_data) {
		    $id_course = $user_data['id_corso'];

				/**
		     * @author giorgio 18/nov/2013
		     *
		     * if the id_course is not associated to
		     * a service of ADA_SERVICE_HELP level it
		     * is safe to skip to next iteration
				 */
		    if (!in_array($id_course,$serviceHelpIDs)) continue;
			    $id_course_instance = $user_data['id_istanza_corso'];
			    // dettagli utente:
			    $href = 'edit_user.php?id='.$user_data['id_utente'];
			    // $user_link = CDOMElement::create('a');
			     $user_link = CDOMElement::create('a', "href:$href");
			    /* end mod */
			    $user_link->addChild(new CText($user_data['nome'] . ' ' . $user_data['cognome']));


			    $href = HTTP_ROOT_DIR.'/tutor/service_info.php?id_course='.$id_course.'&id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance;
			    $service_link = CDOMElement::create('a',"href:$href");
			    $service_link->addChild(new CText(translateFN($user_data['titolo'])));

			    $user_history_link = CDOMElement::create('a', 'href:user_service_detail.php?id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance);
			    $user_history_link->addChild(new CText($user_history_link_label));

			    $id_node   = $id_course.'_'.ADA_DEFAULT_NODE;
			    $href = HTTP_ROOT_DIR.'/browsing/sview.php?id_course='.$id_course.'&id_node='.$id_node.'&id_course_instance='.$id_course_instance;
			    $timeline_link = CDOMElement::create('a', "href:$href");
			    $timeline_link->addChild(new CText($timeline_link_label));
			    $current_timestamp = time();

			    $status = $instanceStatusDescription[$user_data['instance_status']];
			    /*
			     * Graffio 23/10/2015
			     * introduced Status of instance instead of data_inizio and data_fine
			     *
			    if($user_data['data_inizio'] > 0 && $user_data['data_fine'] > 0
			       && $current_timestamp > $user_data['data_inizio']
			       && $current_timestamp < $user_data['data_fine']) {
			      $status = $status_opened_label;

			      $serviceCloseLink = CDOMElement::create('a');
			      $serviceCloseLink->setAttribute('href','#');
			      $serviceCloseLink->setAttribute('onclick', "javascript:openMessenger('eguidance_tutor_form.php?id_course_instance=".$id_course_instance."&popup=1',800,600);");
			      $serviceCloseLink->addChild(new CText(translateFN('chiudi')));

			      $status .= $serviceCloseLink->getHtml();
			     *
			     */
			    if ($user_data['instance_status'] != ADA_INSTANCE_CLOSED && ($user_data['data_fine'] == 0 || $user_data['data_fine'] > time())) {
			      $url = HTTP_ROOT_DIR.'/comunica/send_event_proposal.php?id_user='.$user_data['id_utente'].'&id_course_instance='.$id_course_instance;
			      $onclick = "openMessenger('$url',800,600);";
			      $appointment_link = CDOMElement::create('a');
			      $appointment_link->setAttribute('href','#');
			      $appointment_link->setAttribute('onclick',$onclick);
			      $appointment_link->addChild(new CText($appointment_link_label));

			      $actions = BaseHtmlLib::plainListElement('class:actions',array($appointment_link, $timeline_link, $user_history_link));

			    }
			    else {
			      $status = $status_closed_label;

			      $actions = BaseHtmlLib::plainListElement('class:actions', array($timeline_link, $user_history_link));
			    }

			    $tbody_data[] = array(
			      $user_link,
			      $actions,
			      $service_link,
			      $status,
			      ts2dFN($user_data['data_inizio'])
			//      $user_data['durata'],
			//      ts2dFN($user_data['data_fine'])
			    );
		  }
		  $table = BaseHtmlLib::tableElement('class:sortable',$thead_data,$tbody_data);
		  $data  = $table;
		}
		else {
		  /*
		   * errore nell'ottenimento dei dati relativi agli utenti
		   */
		  $data = new CText(translateFN('Non ci sono utenti assegnati'));
		}
		$title = "<h3>".translateFN('utenti che seguo').'</h3>';
		$cont = CDOMElement::create('div','calss:appointments');
		$cont->addChild($data);
		$data = $title.$cont->getHtml();

		/**
		 * get instances for which this user is tutor
		 */
		$tutoredInstances = $dh->get_tutors_assigned_course_instance($userObj->getId());

		$groupsIFollow = array();
		$practiceCommunities = array();

		if (!AMA_DB::isError($tutoredInstances))
		{
			foreach ($tutoredInstances as $tutoredInstanceAr)
			{
				foreach ($tutoredInstanceAr as $tutoredInstance)
				{
					$serviceInfo = $common_dh->get_service_info_from_course($tutoredInstance['id_corso']);

					$isEnded = ($tutoredInstance['data_fine'] > 0 && $tutoredInstance['data_fine'] < time()) ? true : false;
					$isStarted = ($tutoredInstance['data_inizio'] > 0 && $tutoredInstance['data_inizio'] <= time()) ? true : false;
					$instanceStatus = $tutoredInstance['status'];

					if (!AMA_DB::isError($serviceInfo)) {
						if ($serviceInfo[3]==ADA_SERVICE_COMMON_STUDENT || $serviceInfo[3]==ADA_SERVICE_COMMON)
						{
							// this is a student common area for which the user is tutor (i.e. she's following it)
							$groupsIFollow[] = array ('id_corso'=>$tutoredInstance['id_corso'],
										  'id_istanza_corso'=> $tutoredInstance['id_istanza_corso'],
										  'titolo'=>$serviceInfo[1],
										  'descrizione'=>$serviceInfo[2],
										  'isStarted' => $isStarted,
										  'isEnded' => $isEnded,
										  'active'=>true);
						}
						else if ($serviceInfo[3]==ADA_SERVICE_COMMON_TUTOR)
						{
							// this is a practice community for which the user is tutor (i.e. is 'active')
							$practiceCommunities[] = array ('id_corso'=>$tutoredInstance['id_corso'],
														'id_istanza_corso'=> $tutoredInstance['id_istanza_corso'],
														'titolo'=>$serviceInfo[1],
														'descrizione'=>$serviceInfo[2],
														'isStarted' => $isStarted,
														'isEnded' => $isEnded,
														'active'=>true);
						}
					}
				}
			}
		}

		/**
		 * must get all courses that have a service level of ADA_SERVICE_COMMON_TUTOR
		 * AND are associated with the current user provider
		 * AND for which the current tutor has not subscribed yet
		 */

		// 1. get the array of id_corso contained in $practiceCommunities
		//    by getting only the id_corso key
		$subscribedIDs = array_map(
				function($arr) { return $arr['id_corso']; },
				$practiceCommunities);
		// 2. build the sql clause
		$clauseSQL = 's.livello='. ADA_SERVICE_COMMON_TUTOR ;
		if (!MULTIPROVIDER) $clauseSQL .= ' AND st.id_tester = '. $GLOBALS['testers_dataAr'][$GLOBALS['user_provider']];
		if (!empty($subscribedIDs) ) $clauseSQL .= ' AND st.id_corso NOT IN('. implode(',', $subscribedIDs) .')';
		// 3. ask for resultset
		$tempResults = $common_dh->get_services(null, $clauseSQL);

		if (!AMA_DB::isError($tempResults))
		{
			foreach ($tempResults as $tempResult)
			{
				// id_corso is $tempResult[3] and titolo is $tempResult[1]
				$instancesList = $dh->course_instance_get_list( array('title','data_inizio','data_inizio_previsto','data_fine') , $tempResult[3] );

				if (!AMA_DB::isError($instancesList))
				{
					foreach ($instancesList as $tempRecord)
					{
						$isEnded = ($tempRecord[4] > 0 && $tempRecord[4] < time()) ? true : false;
						$isStarted = ($tempRecord[2] > 0 && $tempRecord[2] <= time()) ? true : false;

						// this is a practice community for which the user is NOT tutor (i.e. is 'NOT active')
						$practiceCommunities[] = array ('id_corso'=>$tempResult[3],
								'id_istanza_corso'=> $tempRecord[0],
								'titolo'=>$tempResult[1],
								'descrizione'=>$tempResult[8],
								'isStarted' => $isStarted,
								'isEnded' => $isEnded,
								'active'=>false);
					}
				}
			}
		}
		/*
		 * everything should be set let's build the html blocks!
		 */
		foreach (array ('dati3'=>$groupsIFollow, 'dati4'=>$practiceCommunities) as $boxnum=>$elementArray)
		{
			if (count($elementArray)>0)
			{
				$box_dataAr[$boxnum] = '';
				foreach ($elementArray as $elementnum=>$element)
				{
					$courseId = $element['id_corso'];
					$nodeId = $courseId.'_0';
					$courseInstanceId = $element['id_istanza_corso'];
					$description = (strlen ($element['descrizione']) > 50) ? substr($element['descrizione'], 0, 50).'...' : $element['descrizione'];

					$divel = CDOMElement::create('div','class:tutorServiceBlock');

					// add area title by itself
					$areaTitle =  new CText('<h3>'.$element['titolo'].'</h3>');
					// add area description
					$divel->addChild(new CText($description.'<br />'));

					// link if service has started and not ended
					$access_link = BaseHtmlLib::link("#", translateFN('servizio'));
					if ($element['isStarted'] && !$element['isEnded'])
					{
						$access_link = CDOMElement::create('div','class:helpRequired');
						$access_link->addChild(new CText('<br /> '));
						$link = CDOMElement::create('a');

						// if tutor is active in this service
						if ($element['active']) {
							$link->setAttribute('href', '../browsing/sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
						}
						// if tutor is not active in this service
						else {
							$link->setAttribute('href', 'tutor_service_instance_subscribe.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
						}
						$link->addChild(new CText(translateFN('Accedi per partecipare...')));
						$access_link->addChild($link);

						// must add statistics link if it's groupsIFollow
						if ($elementArray === $groupsIFollow)
						{
							$stats_link = CDOMElement::create('a','class:tutorStatsLink');
							$stats_link->setAttribute('href', 'tutor.php?op=stats&id_instance='.$courseInstanceId.'&id_course='.$courseId.'&mode=update' );
							$stats_link->addChild(new CText(translateFN('clicca per le statistiche')));

							$access_link->addChild ($stats_link);

						}
					}
					// link is service has ended
					else if ($element['isEnded'])
					{
						$access_link = BaseHtmlLib::link("#", translateFN('Servizio terminato'));
					}
					// add the access link to the div
					$divel->addChild($access_link);
					// sets title and div html to the proper content array
					$box_dataAr[$boxnum] .= $areaTitle->getHtml().$divel->getHtml();
				}
			} // end if ($count($elementArray)>0)
			else {
				// sets proper message depending on missing elements
				if ($boxnum == 'dati3') $box_dataAr[$boxnum] = translateFN("Ancora non segui nessun gruppo");
				if ($boxnum == 'dati4') $box_dataAr[$boxnum] = translateFN("Non ci sono comunit&agrave; di pratica");
			}
		}

		/**
		 * dati6: pre-assigned students box
		 */
		$listStudentIds = $dh->get_preassigned_students_for_tutor($userObj->getId());
		if (!AMA_DB::isError($listStudentIds) && is_array($listStudentIds) && count($listStudentIds)>0) {
			$tableHead = array (translateFN('cognome e nome'),
					translateFN('num. richieste'), translateFN('ultima richiesta'),
					translateFN('azioni'));
			$tableBody = array();

			$helpCourses = array();
			$helpCoursesRES = $dh->find_courses_list(array('titolo'),'`tipo_servizio`='.ADA_SERVICE_HELP.' OR `tipo_servizio`='.ADA_SERVICE_IN_ITINERE);
			if (!AMA_DB::isError($helpCoursesRES)) {
				foreach ($helpCoursesRES as $anHelpCourse) $helpCourses[$anHelpCourse['id_corso']] = $anHelpCourse['titolo'];
			}

			$appointment_link = CDOMElement::create('a');
			$appointment_link->setAttribute('href','javascript:void(0);');
			$appointment_link->addChild(new CText($appointment_link_label));

			foreach ($listStudentIds as $student_id) {
				// load the user from the db
				$studentObj = MultiPort::findUser($student_id);
				if (is_object($studentObj) && $studentObj instanceof ADAUser && $studentObj->getStatus()==ADA_STATUS_REGISTERED) {
					$getInstancesData = true;
					$instancesRES = $dh->get_course_instances_for_this_student($studentObj->getId(), $getInstancesData);
					$countInstances = 0;
					$lastRequestTime = 0;
					if (!AMA_DB::isError($instancesRES)) {
						foreach ($instancesRES as $anInstance) {
							// count only instances having a course with ADA_SERVICE_HELP or ADA_SERVICE_IN_ITINERE as tipo_servizio
							if (in_array((int)$anInstance['tipo_servizio'], array(ADA_SERVICE_HELP, ADA_SERVICE_IN_ITINERE))) {
								$countInstances++;
								$lastRequestTime = max(array($anInstance['data_iscrizione'],$lastRequestTime));
							}
						}
					}
					$onclick = 'javascript:sendEventProposal('.$studentObj->getId().');';
					$appointment_link->setAttribute('onclick',$onclick);
					$tableBody[] = array(
							BaseHtmlLib::link('edit_user.php?id='.$studentObj->getId(), $studentObj->getLastName().' '.$studentObj->getFirstName())->getHtml(),
							$countInstances,
							($lastRequestTime > 0) ? AMA_Common_DataHandler::ts_to_date($lastRequestTime) : $lastRequestTime,
							$appointment_link->getHtml()
					);
				}
			}

			/**
			 * Prepare hidden div or hidden input field to be used
			 * when tutor wants to propose an appointment to student
			 */
			reset($helpCourses);
			if (count($helpCourses)==1) {
				// if there's only one course of type ADA_SERVICE_HELP, set up an hidden field
				$hiddenElement = CDOMElement::create('hidden','id:helpServiceID');
				$hiddenElement->setAttribute('value', key($helpCourses));
			} else if (count($helpCourses)>1) {
				$hiddenElement = CDOMElement::create('div','id:selectServiceDialog,title:'.translateFN('Selezionare un servizio'));
				$hiddenElement->setAttribute('style', 'display:none;');
				$selectMSG = CDOMElement::create('span','class:selectServiceMSG');
				$selectMSG->addChild(new CText('Selezionare il servizio da associare alla proposta d\'appuntamento'));
				$selectElement = BaseHtmlLib::selectElement2('id:selectHelpService',$helpCourses,key($helpCourses));
				$hiddenElement->addChild($selectMSG);
				$hiddenElement->addChild(CDOMElement::create('div','class:clearfix'));
				$hiddenElement->addChild($selectElement);
			} else if (count($helpCourses)==0) {
				$hiddenElement = CDOMElement::create('span','id:noHelpServiceMSG');
				$hiddenElement->setAttribute('style', 'display:none;');
				$hiddenElement->addChild(new CText(translateFN('Nessun servizio di tipo').
						' '.$_SESSION['service_level'][ADA_SERVICE_HELP].' o '.$_SESSION['service_level'][ADA_SERVICE_IN_ITINERE]));
			}

			$box_dataAr['dati6'] = ((isset($hiddenElement)) ? $hiddenElement->getHtml() : '').
			                       BaseHtmlLib::tableElement('id:table_preassigned_students',
			                       		$tableHead, $tableBody)->getHtml();
		} else {
			$box_dataAr['dati6'] = translateFN('Non hai studenti assegnati');
		}

		break; // for default case

	} // end switch $op

//$online_users_listing_mode = 2;
//$online_users = WISPLoggableUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


$banner = include ROOT_DIR.'/include/banner.inc.php';

/* ***********************
* Appointment for all instances
* $user_events are valorized in tutor_function.inc.php
*/
if (is_object($user_events)) {
$divAppointments = CDOMElement::create('div','class:appointments');
//                    $divAppointments->addChild(new CText('<h3>'.translateFN('Appuntamenti').'</h3>'));
if (is_object($user_events) && $user_events->getHtml() != '') $divAppointments->addChild($user_events);
if ($user_events->getHtml() == '') {
$divAppointments->addChild(new CText(translateFN('Non ci sono appuntamenti')));
}
}

foreach ($user_agendaAr as $providerUserDate => $appointmentTmp) {
    $dhUserDate = AMA_DataHandler::instance(MultiPort::getDSN($providerUserDate));
    foreach ($appointmentTmp as $idAppTmp => $singleApp) {
	$event_token = ADAEventProposal::extractEventToken($singleApp[2]);
	$guidanceSession = $dhUserDate->get_eguidance_session_with_event_token($event_token);
	if (AMA_DB::isError($guidanceSession)) {
	    $user_agendaAr[$providerUserDate][$idAppTmp]['report'] = false;
//	    unset($user_agendaAr[$providerUserDate][$idAppTmp]);
	    if ($userObj->getType() == AMA_TYPE_TUTOR)
			$user_agendaAr[$providerUserDate][$idAppTmp]['crea_report'] = true;
	}
	else {
	    $user_agendaAr[$providerUserDate][$idAppTmp]['report'] = true;

	}
    }
}
$user_agenda   = CommunicationModuleHtmlLib::displayAppointmentsWithAssessementLink($user_agendaAr, ADA_MSG_AGENDA, $testers_dataAr,$showRead);



/* ***********************
* proposal for all instances
* $user_events are valorized in browsing_function.inc.php
*/
if (is_object($user_events_proposed)) {
$divAppointmentsProposed = CDOMElement::create('div','class:appointments');
if (is_object($user_events_proposed) && $user_events_proposed->getHtml() != '') $divAppointmentsProposed->addChild($user_events_proposed);
if ($user_events_proposed->getHtml() == '') {
$divAppointmentsProposed->addChild(new CText(translateFN('Non ci sono appuntamenti')));
}
}


$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$bloccoUnoTitolo = '<h2>'.translateFN('utenti che seguo').'</h2>';
$bloccoDueTitolo = '<h2>'.translateFN('Interazioni').'</h2>';
$bloccoTreTitolo = '<h2>'.translateFN('gruppi che seguo').'</h2>';
$bloccoQuattroTitolo = '<h2>'.translateFN('Comunit&agrave; di pratica').'</h2>';
$bloccoSeiTitolo = '<h2>'.translateFN('Utenti che mi sono assegnati').'</h2>';

$content_dataAr = array(
'banner'          => $banner,
'bloccoUnoTitolo' => $bloccoUnoTitolo,
'bloccoDueTitolo'  => $bloccoDueTitolo,
'bloccoTreTitolo'  => $bloccoTreTitolo,
'bloccoQuattroTitolo'  => $bloccoQuattroTitolo,
'bloccoSeiTitolo'  => $bloccoSeiTitolo,
'user_name'       => $user_name,
'user_type'       => $user_type,
'level'           => $user_level,
//  'messages'        => $user_messages->getHtml(),
//  'agenda'          => $user_agenda->getHtml(),
//  'events'          => $user_events->getHtml(),
'user_avatar'     => $avatar->getHtml(),
'events_proposed' => $user_events_proposed->getHtml(),
'course_title'    => translateFN("Practitioner's home"),
'dati'            => $data,
'data'            => $data,
//  'menu_01'         => $questionaire,
'menu_02'         => '',
'menu_03'         => '',
'menu_04'         => '',
'menu_05'         => '',
'menu_06'         => '',
'menu_07'         => '',
'menu_08'         => ''
);

if (isset ($box_dataAr)) $content_dataAr = array_merge($content_dataAr, $box_dataAr);


$content_dataAr['bloccoDueAppuntamenti'] = '<h3>'.translateFN('Appuntamenti').'</h3>';
//$content_dataAr['bloccoDueAppuntamenti'] .= $divAppointments->getHtml();
$content_dataAr['bloccoDueAppuntamenti'] .= $user_agenda->getHtml();

$content_dataAr['bloccoDueAppuntamenti'] .= '<h3>'.translateFN('Proposte di appuntamento').'</h3>';
$content_dataAr['bloccoDueAppuntamenti'] .= $divAppointmentsProposed->getHtml();

$content_dataAr['bloccoDueAppuntamenti'] .= '<h3>'.translateFN('Messaggi ricevuti').'</h3>';
$content_dataAr['bloccoDueAppuntamenti'] .= $user_messages->getHtml();

$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();

$layout_dataAr['JS_filename'] = array(
JQUERY,
JQUERY_UI,
JQUERY_DATATABLE,
JQUERY_DATATABLE_DATE,
ROOT_DIR.'/js/include/jquery/dataTables/formattedNumberSortPlugin.js',
JQUERY_NO_CONFLICT
);
$menuOptions = array();
if (isset($id_course))   $menuOptions['id_course'] = $id_course;
if (isset($id_instance)) $menuOptions['id_instance'] = $id_instance;
if (isset($id_instance)) $menuOptions['id_course_instance'] = $id_instance;
if (isset($id_student))  $menuOptions['id_student'] =$id_student;

/**
* add a define for the supertutor menu item to appear
*/
if ($userObj instanceof ADAPractitioner && $userObj->isSuper()) define ('IS_SUPERTUTOR', true);
else define ('NOT_SUPERTUTOR', true);

$layout_dataAr['CSS_filename']= array(
JQUERY_DATATABLE_CSS,
JQUERY_UI_CSS
);
$render = null;
$options['onload_func'] = 'initDoc()';

/**
* Sends data to the rendering engine
*/
ARE::render($layout_dataAr, $content_dataAr, $render, $options);
