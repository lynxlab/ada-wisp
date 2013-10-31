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
$head_desc_epract = translateFN("epractitioner");
$head_desc_instance = translateFN("instanza");


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

/*
$id_tester = $testers_dataAr[$_SESSION['sess_selected_tester']];
$info_services_for_current_providerAr = $common_dh->get_info_for_tester_services($id_tester);
$info_durataHa = array();
for ($i=0; $i < count($info_services_for_current_providerAr); $i++) {
    $id_course = $info_services_for_current_providerAr[$i]['id_corso'];
    $durata = $info_services_for_current_providerAr[$i]['durata_servizio'];
    $info_durataHa[$id_course] = $durata;
}
//print_r($info_durataHa);
//print_r($info_services_for_current_providerAr);
*/
$numRequiredHelp = 0;
if ($op=='not_started' or $op=='all') {
    $not_startedAr = $dh->get_tester_services_not_started();


    if (is_array($not_startedAr) && sizeof($not_startedAr) > 0) {
      foreach($not_startedAr as $user_registration) {
        $numRequiredHelp ++;
        $href = 'zoom_user.php?id='.$user_registration['id_utente'];
        $user_link = CDOMElement::create('a', "href:$href");
        $user_link->addChild(new CText($user_registration['nome'] .' '.$user_registration['cognome']));

        $href = HTTP_ROOT_DIR.'/browsing/service_info.php?id_course='.$user_registration['id_corso'];
        $service_link = CDOMElement::create('a',"href:$href");
        $service_link->addChild(new CText(translateFN($user_registration['titolo'])));
        $request_date = AMA_DataHandler::ts_to_date($user_registration['data_richiesta']);

        $href = 'assign_practitioner.php?id_corso='.$user_registration['id_corso'].'&id_course_instance='.$user_registration['id_istanza_corso'].'&id_user='.$user_registration['id_utente'];
        $epractitioner_link = CDOMElement::create('a', "href:$href");
        $epractitioner_link->addChild(new CText(translateFN('Assegna')));

        $href = 'edit_instance.php?id_course_instance='.$user_registration['id_istanza_corso'];
        $instance_link = CDOMElement::create('a', "href:$href");
        $instance_link->addChild(new CText(translateFN('edit')));

        $tbody_data[] = array(
          $user_link,
          $service_link,
          $request_date,
          $epractitioner_link,
          $instance_link
        );
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
    $startedAr = $dh->get_tester_services_started();
    if (is_array($startedAr) && sizeof($startedAr) > 0) {

      foreach($startedAr as $user_registration) {

        //print_r($user_registration);
        if (($op == 'closed' &&  time() >= $user_registration['data_fine']) || ($op == 'open' &&  time() < $user_registration['data_fine']) || ($op=='started') || ($op=='all')) {
            $numRequiredHelp ++;
            $href = 'zoom_user.php?id='.$user_registration['id_utente'];
            $user_link = CDOMElement::create('a', "href:$href");
            $user_link->addChild(new CText($user_registration['nome'] .' '.$user_registration['cognome']));

            $href = HTTP_ROOT_DIR.'/browsing/service_info.php?id_course='.$user_registration['id_corso'];
            $service_link = CDOMElement::create('a',"href:$href");
            $service_link->addChild(new CText(translateFN($user_registration['titolo'])));
            $request_date = AMA_DataHandler::ts_to_date($user_registration['data_richiesta']);

            $href = 'assign_practitioner.php?id_corso='.$user_registration['id_corso'].'&id_course_instance='.$user_registration['id_istanza_corso'].'&id_user='.$user_registration['id_utente'];
            $epractitioner_link = CDOMElement::create('a', "href:$href");
            $epractitioner_link->addChild(new CText($user_registration['username_t'].' ('.$user_registration['nome_t'] .' '.$user_registration['cognome_t'].')'));

            $href = 'edit_instance.php?id_course_instance='.$user_registration['id_istanza_corso'];
            $instance_link = CDOMElement::create('a', "href:$href");
            $instance_link->addChild(new CText(translateFN('edit')));
            
            $tbody_data[] = array(
              $user_link,
              $service_link,
              $request_date,
              $epractitioner_link,
              $instance_link
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

$help = translateFN('Richieste') .': ' . $numRequiredHelp .', '. translateFN('Utenti'). ': '. $numUsers .' - '. translateFN("Da qui lo switcher puo' gestire gli assegnamenti client e-practitioner");
$menu_01 = '<a href="list_lservices.php">'.translateFN('Vedi servizi').'</a>';
$menu_02 = '<a href="add_lservice.php">'.translateFN('Aggiungi servizio').'</a>';


if(!isset($status)) {
  $status = 'Lista degli utenti che hanno richiesto un servizio';
}

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
  'data'      => $table->getHtml()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
                JQUERY_DATATABLE_DATE,
		JQUERY_NO_CONFLICT
	);

$layout_dataAr['CSS_filename']= array(
		JQUERY_DATATABLE_CSS
	);
  $render = null;
  $options['onload_func'] = 'dataTablesExec()';
  
/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr, $render, $options);
//ARE::render($layout_dataAr, $content_dataAr);
?>