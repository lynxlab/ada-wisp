<?php

/**
 * getStudentPendingInstances.php - return table with pneding instances for passed student
 *
 * @package
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2017, Lynx s.r.l.
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
$retArray=array('html'=>'');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['studentID']) && intval($_GET['studentID'])>0) {
	$getInstancesData = true;
	$instancesRES = $dh->get_course_instances_for_this_student(intval($_GET['studentID']), $getInstancesData);

	$appointment_link_label  = translateFN('Proponi appuntamento');
	$status_link_label = translateFN('Vedi lo stato');

	if (!AMA_DB::isError($instancesRES) && is_array($instancesRES) && count($instancesRES)>0) {
		// sort by data_inizio desc
		uasort($instancesRES, function ($a, $b) { return $a["data_inizio"] - $b["data_inizio"]; });
		$tbody = array();
		foreach ($instancesRES as $anInstance) {
			$actions = array();
			// show only instances having a course with ADA_SERVICE_HELP or ADA_SERVICE_IN_ITINERE as tipo_servizio
			if (in_array((int)$anInstance['tipo_servizio'], array(ADA_SERVICE_HELP, ADA_SERVICE_IN_ITINERE))) {
				if ($anInstance['instance_status'] != ADA_INSTANCE_CLOSED) {

					$id_node   = $anInstance['id_corso'].'_'.ADA_DEFAULT_NODE;
					$href = HTTP_ROOT_DIR.'/browsing/sview.php?id_course='.$anInstance['id_corso'].'&id_node='.$id_node.'&id_course_instance='.$anInstance['id_istanza_corso'];
					$timeline_link = CDOMElement::create('a', "class:pending service link,href:$href");
					$timeline_link->addChild(new CText($anInstance['titolo']));

					$timeline_span = CDOMElement::create('span','class:pending service descr');
					$timeline_span->addChild(new CText('('.translateFN($instanceStatusDescription[$anInstance['instance_status']]).')'));

					$tutorOwnStudent = $dh->is_tutor_of_instance($userObj->getId(), $anInstance['id_istanza_corso']);
					$row = array(
							count($tbody)+1,
							AMA_Common_DataHandler::ts_to_date($anInstance['data_iscrizione']),
							$timeline_link->getHtml().$timeline_span->getHtml());

					if ($tutorOwnStudent) {
						/**
						 * Should give new appointment link only for instances with valid data_fine
						 * to have this behaviour, uncomment the below if condition
						 */
						// if ($anInstance['data_fine'] == 0 || $anInstance['data_fine'] > time()) {
							$url = HTTP_ROOT_DIR.'/comunica/send_event_proposal.php?id_user='.intval($_GET['studentID']).'&id_course_instance='.$anInstance['id_istanza_corso'];
							$onclick = "openMessenger('$url',800,600);";
							$appointment_link = CDOMElement::create('a');
							$appointment_link->setAttribute('href','javascript:void(0);');
							$appointment_link->setAttribute('onclick',$onclick);
							$appointment_link->setAttribute('class', 'new appointment link');
							$appointment_link->addChild(new CText($appointment_link_label));
							$actions[] = $appointment_link->getHtml();
						// }
					}
					$status_link = CDOMElement::create('a', 'class:show status link,href:user_service_detail.php?id_user='.intval($_GET['studentID']).'&id_course_instance='.$anInstance['id_istanza_corso']);
					$status_link->addChild(new CText($status_link_label));
					$actions[] = $status_link->getHtml();

					array_push($row, implode(' | ', $actions));
					$tbody[] = $row;
				}
			}
		}

		if (count($tbody)>0) {
			$retArray['html'] =
			BaseHtmlLib::tableElement('class:student pending services default_table,id:pendingServices-'.intval($_GET['studentID']),
					array('#',translateFN('data'), translateFN('servizio'), translateFN('azioni')), $tbody)->getHtml();
		}

	}


}
header('Content-Type: application/json');
die (json_encode($retArray));