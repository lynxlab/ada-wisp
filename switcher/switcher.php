<?php
/**
 * SWITCHER.
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


require_once ROOT_DIR.'/include/module_init.inc.php';
$self = 'switcher'; //whoami();  // = switcher!

include_once 'include/'.$self.'_functions.inc.php';
if ($_REQUEST[op] == NULL) $op='all';

/*
 * YOUR CODE HERE
 */
//require_once ROOT_DIR.'/include/form/phpOpenFormGen.inc.php';
//require_once ROOT_DIR.'/admin/include/htmladmoutput.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';

$data = CDOMElement::create('div');
$head_desc_user = translateFN("utente");
$head_desc_service = translateFN("servizio");
$head_desc_date = translateFN("date of request");
$head_desc_epract = translateFN("consulente");
//$head_desc_instance = translateFN("azioni");
$head_desc_instance = translateFN("status");


$thead_data = array($head_desc_user,$head_desc_service,$head_desc_date,$head_desc_epract,$head_desc_instance);
$tbody_data = array();

switch ($op) {
    case 'not_started':
        $filter_link = "<a href='switcher.php?op=all'>".translateFN("All")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<strong>".translateFN("Show not started")."</strong>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=started'>".translateFN("Show started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=open'>".translateFN("Show open")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=closed'>".translateFN("Show closed")."</a>&nbsp;| &nbsp;";
        break;
    case 'started':
        $filter_link = "<a href='switcher.php?op=all'>".translateFN("All")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=not_started'>".translateFN("Show not started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<strong>".translateFN("Show started")."</strong>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=open'>".translateFN("Show open")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=closed'>".translateFN("Show closed")."</a>&nbsp;| &nbsp;";
        break;
    case 'closed':
        $filter_link = "<a href='switcher.php?op=all'>".translateFN("All")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=not_started'>".translateFN("Show not started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=started'>".translateFN("Show started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=open'>".translateFN("Show open")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<strong>".translateFN("Show closed")."</strong>&nbsp;| &nbsp;";
        break;
    case 'open':
        $filter_link = "<a href='switcher.php?op=all'>".translateFN("All")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=not_started'>".translateFN("Show not started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=started'>".translateFN("Show started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<strong>".translateFN("Show open")."</strong>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=closed'>".translateFN("Show closed")."</a>&nbsp;| &nbsp;";
        break;
    case 'all':
    default:
        $filter_link = "<strong>".translateFN("All")."</strong>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=not_started'>".translateFN("Show not started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=started'>".translateFN("Show started")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=open'>".translateFN("Show open")."</a>&nbsp;| &nbsp;";
        $filter_link .= "<a href='switcher.php?op=closed'>".translateFN("Show closed")."</a>&nbsp;| &nbsp;";
        break;
}
//print_r($GLOBALS);
if (MULTIPROVIDER) {
    $providerPointer = $GLOBALS['sess_selected_tester'];
}
else
{
    $providerPointer = $GLOBALS['user_provider'];
}
$providerDataAr = $common_dh->get_tester_info_from_pointer($providerPointer);
$idProvider = $providerDataAr[0];

$infoServicesForCurrentProviderAr = $common_dh->get_info_for_tester_services($idProvider);
$infoServicelHa = array();
foreach ($infoServicesForCurrentProviderAr as $infoService) {
    $idLocalService = $infoService['id_corso'];
    $infoServicelHa[$idLocalService]['level'] = $infoService['livello'];
    $infoServicelHa[$idLocalService]['idCommonService'] = $infoService['id_servizio'];
    $infoServicelHa[$idLocalService]['name'] = $infoService['nome'];
}

/**
 * use AA_ISCR_DESC and ANNO_CORSO from $_GET, if any
 *
 * @var string $aa_iscr_desc
 * @var string $anno_corso
 */
$aa_iscr_desc = null;
$anno_corso = null;
if (array_key_exists('AA_ISCR_DESC', $_GET) && array_key_exists('ANNO_CORSO', $_GET)) {
	$aa_iscr_desc = trim($_GET['AA_ISCR_DESC']);
	$anno_corso = trim($_GET['ANNO_CORSO']);
}

$annoCorsoAr = $dh->get_tutor_anno_corso_filter();
if (is_array($annoCorsoAr) && count($annoCorsoAr)>0) {
	$annoCorsoSelect = array( 'all' => translateFN('Tutti gli anni'));
	foreach ($annoCorsoAr as $anAnnoCorso) {
		// split annocorso option
		list ($curr_aa_iscr_desc, $curr_anno_corso) = explode(' ', $anAnnoCorso['option']);
		$curr_anno_corso = trim($curr_anno_corso,'()');
		$key = 'AA_ISCR_DESC='.$curr_aa_iscr_desc.'&ANNO_CORSO='.$curr_anno_corso;
		$annoCorsoSelect[$key] = $anAnnoCorso['option'];
	}
	if (!is_null($aa_iscr_desc) && !is_null($anno_corso)) {
		$selected = 'AA_ISCR_DESC='.$aa_iscr_desc.'&ANNO_CORSO='.$anno_corso;
	} else {
		$selected = 'all';
	}
	$annoCorsoEl = BaseHtmlLib::selectElement2('id:annocorso-select', $annoCorsoSelect, $selected);
}



$numRequiredHelp = 0;
if ($op=='not_started' or $op=='all') {
    $not_startedAr = $dh->get_tester_services_not_started();

    if (is_array($not_startedAr) && sizeof($not_startedAr) > 0) {
      foreach($not_startedAr as $not_startedKey => $user_registration) {

      	// load the user from the db and do not loop if their AA_ISCR_DESC and ANNO_CORSO does not match the passed ones
      	$studentObj = MultiPort::findUser($user_registration['id_utente']);
        if (!is_null($aa_iscr_desc) && property_exists($studentObj, 'AA_ISCR_DESC') && $studentObj->AA_ISCR_DESC != $aa_iscr_desc &&
        	!is_null($anno_corso) && property_exists($studentObj, 'ANNO_CORSO') && $studentObj->ANNO_CORSO != $anno_corso) {
        	unset ($not_startedAr[$not_startedKey]);
        	continue;
        }

        $idLocalService = $user_registration['id_corso'];
        if (in_array((int)$infoServicelHa[$idLocalService]['level'], array(ADA_SERVICE_HELP, ADA_SERVICE_IN_ITINERE))) {
            $numRequiredHelp ++;
            $href = 'edit_user.php?id_user='.$user_registration['id_utente'].'&usertype='.AMA_TYPE_STUDENT;
            $user_link = CDOMElement::create('a', "href:$href");
            $user_link->addChild(new CText($user_registration['nome'] .' '.$user_registration['cognome']));

            $href = HTTP_ROOT_DIR.'/browsing/service_info.php?id_course='.$user_registration['id_corso'];
            $service_link = CDOMElement::create('a',"href:$href");
            $service_link->addChild(new CText(translateFN($user_registration['titolo'])));
            $request_date = AMA_DataHandler::ts_to_date($user_registration['data_richiesta']);

            $href = 'assign_practitioner.php?id_course='.$user_registration['id_corso'].'&id_course_instance='.$user_registration['id_istanza_corso'].'&id_user='.$user_registration['id_utente'];
            $epractitioner_link = CDOMElement::create('a', "href:$href");
            $epractitioner_link->addChild(new CText(translateFN('Assegna')));

            // $href = 'edit_instance.php?id_course_instance='.$user_registration['id_istanza_corso'];
	    $instance_status = CDOMElement::create('span','class:instance_status');
            $instance_status->addChild(new CText(translateFN($instanceStatusDescription[$user_registration['instance_status']]).'/'));

            $instance_link = CDOMElement::create('a');
            $instance_link->setAttribute('href','../tutor/user_service_detail.php?id_user='.$user_registration['id_utente'].'&id_course_instance='.$user_registration['id_istanza_corso']);
            $instance_link->addChild(new CText(translateFN('Modifica')));

	    $instance_status->addChild($instance_link);

            $tbody_data[] = array(
              $user_link,
              $service_link,
              $request_date,
              $epractitioner_link,
              $instance_status
            );
        }
      }
    }
    else {
      /*
       * Siamo qui sia se si e' verificato un errore sia se non ci sono istanze di
       * corso.
       */
    }
}
if ($op=='started' || $op=='all' || $op=='open' || $op=='closed') {
    $getUnassignedServices = true;
    $startedAr = $dh->get_tester_services_started($getUnassignedServices);
    if (is_array($startedAr) && sizeof($startedAr) > 0) {

      foreach($startedAr as $startedKey => $user_registration) {

      	// load the user from the db and do not loop if their AA_ISCR_DESC and ANNO_CORSO does not match the passed ones
      	$studentObj = MultiPort::findUser($user_registration['id_utente']);
      	if (!is_null($aa_iscr_desc) && property_exists($studentObj, 'AA_ISCR_DESC') && $studentObj->AA_ISCR_DESC != $aa_iscr_desc &&
      		!is_null($anno_corso) && property_exists($studentObj, 'ANNO_CORSO') && $studentObj->ANNO_CORSO != $anno_corso) {
      		unset($startedAr[$startedKey]);
      		continue;
      	}

        if (($op == 'closed' &&  time() >= $user_registration['data_fine']) || ($op == 'open' &&  time() < $user_registration['data_fine']) || ($op=='started') || ($op=='all')) {
            $idLocalService = $user_registration['id_corso'];
            if (in_array((int)$infoServicelHa[$idLocalService]['level'], array(ADA_SERVICE_HELP, ADA_SERVICE_IN_ITINERE))) {

                $numRequiredHelp ++;
                $href = 'edit_user.php?id_user='.$user_registration['id_utente'].'&usertype='.AMA_TYPE_STUDENT;
                $user_link = CDOMElement::create('a', "href:$href");
                $user_link->addChild(new CText($user_registration['nome'] .' '.$user_registration['cognome']));

                $href = HTTP_ROOT_DIR.'/browsing/service_info.php?id_course='.$user_registration['id_corso'];
                $service_link = CDOMElement::create('a',"href:$href");
                $service_link->addChild(new CText(translateFN($user_registration['titolo'])));
                $request_date = AMA_DataHandler::ts_to_date($user_registration['data_richiesta']);

                /**
                 * @author giorgio check if service opened is done on data_fine
                 * @author giorgio epractioner link is built here, depending on service status
                 */
                // $href = 'edit_instance.php?id_course_instance='.$user_registration['id_istanza_corso'];
                $instance_link = CDOMElement::create('a');
//                $instance_link->setAttribute('href','../tutor/eguidance_tutor_form.php?id_course_instance='.$user_registration['id_istanza_corso']);
		$instance_link->setAttribute('href','../tutor/user_service_detail.php?id_user='.$user_registration['id_utente'].'&id_course_instance='.$user_registration['id_istanza_corso']);
		$instance_link->addChild(new CText(translateFN('Modifica')));

		$instance_status = CDOMElement::create('span','class:instance_status');
		$instance_status->addChild(new CText(translateFN($instanceStatusDescription[$user_registration['instance_status']]).'/'));
		$instance_status->addChild($instance_link);

		$current_timestamp = time();

                if(($user_registration['data_inizio'] > 0 && $user_registration['data_fine'] > 0
                && $current_timestamp > $user_registration['data_inizio']
                && $current_timestamp < $user_registration['data_fine']) || $user_registration['instance_status'] != ADA_INSTANCE_CLOSED) {
                	// 1. build epractiotioner link
                	$href = 'assign_practitioner.php?id_course='.$user_registration['id_corso'].'&id_course_instance='.$user_registration['id_istanza_corso'].'&id_user='.$user_registration['id_utente'];
                	$epractitioner_link = CDOMElement::create('a', "href:$href");
                	if (!is_null($user_registration['username_t'])) {
                		$epractitioner_link->addChild(new CText($user_registration['username_t'].' ('.$user_registration['nome_t'] .' '.$user_registration['cognome_t'].')'));
                	} else {
                		$epractitioner_link->addChild(new CText(translateFN('Assegna')));
                	}
                	// 2. build instance link
//                	$instance_link->addChild(new CText(translateFN('chiudi')));
                } else {
                	// 1. build epractiotioner link, that is a span in this case
                	$epractitioner_link = CDOMElement::create('span');
                	if (!is_null($user_registration['username_t'])) {
                		$epractitioner_link = new CText($user_registration['username_t'].' ('.$user_registration['nome_t'] .' '.$user_registration['cognome_t'].')');
                	} else {
                		$epractitioner_link = new CText(translateFN('Non Assegnato'));
                	}
                	// 2. build instance link
  //              	$instance_link->addChild(new CText(translateFN('terminato')));
                }


                $tbody_data[] = array(
                  $user_link,
                  $service_link,
                  $request_date,
                  $epractitioner_link,
//                  $instance_link
		  $instance_status
		);
            }
        }
      }
    }
    else {
      /*
       * Siamo qui sia se si e' verificato un errore sia se non ci sono istanze di
       * corso.
       */
    }
}

$allServicesRequired = array_merge($not_startedAr,$startedAr);
$numUsers = 0;
$userRequiringTmp = array();
foreach ($allServicesRequired as $oneService) {
    if (!in_array($oneService['id_utente'], $userRequiringTmp)) {
        array_push($userRequiringTmp, $oneService['id_utente']);
    }
}
$numUsers = count($userRequiringTmp);

//$table = BaseHtmlLib::tableElement('class:sortable',$thead_data, $tbody_data);
$table = BaseHtmlLib::tableElement('id:table_users_for_service',$thead_data, $tbody_data);

// SERVICE:  BANNER, HELP, STATUS

$banner = include ROOT_DIR.'/include/banner.inc.php';
$label = translateFN('Coordinator Home Page');

$help = translateFN('Richieste') .': ' . $numRequiredHelp .', '. translateFN('Utenti'). ': '. $numUsers .' - '. translateFN("Qui puoi vedere le richieste di servizio in corso e gestire il lavoro del team di Consulenti");
$menu_01 = '<a href="list_lservices.php">'.translateFN('Vedi servizi').'</a>';
$menu_02 = '<a href="add_lservice.php">'.translateFN('Aggiungi servizio').'</a>';


if(!isset($status)) {
  $status = 'Lista degli utenti che hanno richiesto un servizio';
}

$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$content_dataAr = array(
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml(),
  'status'    => $status,
  'course_title'     => $label,
  'banner'    => $banner,
  'help'      => $help,
  'filter_link' => $filter_link,
  'menu_01' => $menu_01,
  'menu_02' => $menu_02,
  'data'      => $table->getHtml(),
  'user_avatar'=>$avatar->getHtml(),
  'user_modprofilelink' => $userObj->getEditProfilePage()
);

if (isset($annoCorsoEl)) $content_dataAr['annocorsofilter'] = $annoCorsoEl->getHtml();

$layout_dataAr['JS_filename'] = array(
	JQUERY,
	JQUERY_DATATABLE,
	JQUERY_DATATABLE_DATE,
        ROOT_DIR. '/js/include/jquery/dataTables/selectSortPlugin.js',
	JQUERY_NO_CONFLICT
);

$layout_dataAr['CSS_filename']= array(
        JQUERY_UI_CSS,
	JQUERY_DATATABLE_CSS
    );
  $render = null;
  $options['onload_func'] = 'initDoc()';

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, $render, $options);
