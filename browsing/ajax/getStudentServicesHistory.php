<?php
/**
 * getStudentServicesHistory - gets a student UNIMC service history table
 *
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2017, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_SWITCHER     => array('layout'),
  AMA_TYPE_STUDENT      => array('layout'),
  AMA_TYPE_TUTOR 		=> array('layout'),
  AMA_TYPE_AUTHOR       => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

$result = false;
$message = translateFN('Richiesta non valida');

if ($_SERVER['REQUEST_METHOD']=='GET' && intval(trim($_GET['userId']))>0) {
	$userId = intval(trim($_GET['userId']));

	$getInstancesData = true;
	$instancesRES = $dh->get_course_instances_for_this_student($userId, $getInstancesData);

	if (!AMA_DB::isError($instancesRES) && count($instancesRES)>0) {

		$result = true;

		$header = array(
			'#',
			translateFN('Servizio'),
			translateFN('Data richiesta'),
			translateFN('Orientatore'),
			translateFN('Stato'),
			translateFN('Azioni')
		);
		$tableData = array();
		$status_link_label = translateFN('Vedi lo stato');

		foreach ($instancesRES as $anInstance) {
			// count only instances having a course with ADA_SERVICE_HELP or ADA_SERVICE_IN_ITINERE as tipo_servizio
			if (in_array((int)$anInstance['tipo_servizio'], array(ADA_SERVICE_HELP, ADA_SERVICE_IN_ITINERE))) {

				// if sess_user is a tutor (and NOT supertutor, do not show instances for which she is not the tutor
				// load one tutor only from the db, since instances should NOT have more than one tutor here
				$tutorRes = $dh->course_instance_tutor_info_get($anInstance['id_istanza_corso'],1);

				if (!AMA_DB::isError($tutorRes)) {

// 					if (in_array($_SESSION['sess_userObj']->getType(), array(AMA_TYPE_SWITCHER)) ||
// 						($_SESSION['sess_userObj']->getType() == AMA_TYPE_TUTOR && $_SESSION['sess_userObj']->isSuper()) ||
// 						($_SESSION['sess_userObj']->getType() == AMA_TYPE_TUTOR && $_SESSION['sess_userObj']->getId() == $tutorRes[0])) {

						$id_node   = $anInstance['id_corso'].'_'.ADA_DEFAULT_NODE;
						$href = HTTP_ROOT_DIR.'/browsing/sview.php?id_course='.$anInstance['id_corso'].'&id_node='.$id_node.'&id_course_instance='.$anInstance['id_istanza_corso'];
						$timeline_link = CDOMElement::create('a', "class:history service link,href:$href");
						$timeline_link->addChild(new CText($anInstance['titolo']));

						$status_link = CDOMElement::create('a', 'class:show status link,href:'.HTTP_ROOT_DIR.'/tutor/user_service_detail.php?id_user='.intval($_GET['userId']).'&id_course_instance='.$anInstance['id_istanza_corso']);
						$status_link->addChild(new CText($status_link_label));

						$tableData[] = array(
							count($tableData)+1,
							$timeline_link->getHtml(),
							AMA_Common_DataHandler::ts_to_date($anInstance['data_inizio']),
							$tutorRes[1].' '.$tutorRes[2],
							translateFN($instanceStatusDescription[$anInstance['instance_status']]),
							$status_link->getHtml()
						);

// 					}
				}

			}
		}
		$message = BaseHtmlLib::tableElement('id:studentHistoryTable,class:'.ADA_SEMANTICUI_TABLECLASS,$header,$tableData)->getHtml();
	}
	else $message = translateFN('Nessuno storico per lo studente');
}
sleep(1); // just in case we've been too quick, the fadein/out could do a messe
die(json_encode(array('status'=>($result ? 1:0), 'msg'=>$message)));
