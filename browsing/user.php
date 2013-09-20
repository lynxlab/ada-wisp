<?php

/**
 * USER.
 *
 * @package		user
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		user
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
$allowedUsersAr = array(AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout', 'default_tester')
);
require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();
require_once 'include/browsing_functions.inc.php';

$courseInstances = array();
$serviceProviders = $userObj->getTesters();

$courseInstances = array();
if (count($serviceProviders) == 1) {
    $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($serviceProviders[0]));
    $courseInstances = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
//    $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
} else {
    foreach ($serviceProviders as $Provider) {
        $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
        $courseInstances_provider = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
        $courseInstances = array_merge($courseInstances, $courseInstances_provider);
    }
}
if(!AMA_DataHandler::isError($courseInstances)) {
    $found = count($courseInstances);
    $thead_dataAr = array(
           translateFN('Titolo'),
           translateFN('Iniziato'),
           translateFN('Data inizio'),
           translateFN('Durata'),
           translateFN('Data fine'),
           translateFN('Azioni')
        );
   
/**
 * @author giorgio 24/apr/2013
 * 
 * if the 3 $_GET params are all set, display the (kind of) "what's new" page
 */
    
    $get_id_node            = (isset($_GET['id_node'])) ? $_GET['id_node'] : '';
    $get_id_course          = (isset($_GET['id_course'])) ? $_GET['id_course'] : '';
    $get_id_course_instance = (isset($_GET['id_course_instance'])) ? $_GET['id_course_instance'] : '';
    
    $displayWhatsNew = false;
    
    if ( $get_id_node!=='' && $get_id_course!=='' && $get_id_course_instance!=='')
    {   
    	$courseId = $get_id_course;
    	$courseInstanceId = $get_id_course_instance;
    	$nodeId = $get_id_node;
    	
    	$displayWhatsNew = true;
    	// show (kind of) what's new page: user page with user.tpl template
    }        
    else { // resume 'normal' operation: user page using default template
	    if( $found > 1) {
	
	        $tbody_dataAr = array();
	        foreach($courseInstances as $c) {
	            $courseId = $c['id_corso'];
	            $nodeId = $courseId . '_0';
	            $courseInstanceId = $c['id_istanza_corso'];
	            $subscription_status = $c['status'];
	
	
	            $started = ($c['data_inizio'] > 0 && $c['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
	            $start_date = ($c['data inizio'] > 0) ? $c['data_inizio'] : $c['data_inizio_previsto'];
	
	            $isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	            $isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	            $access_link = BaseHtmlLib::link("#",
	                        translateFN('Attendi apertura corso'));
	            /*
	            if ($isStarted && !$isEnded) {
	                $access_link = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",
	                        translateFN('Accedi'));
	            }
	            if ($isEnded) {
	                $access_link = BaseHtmlLib::link("#",
	                        translateFN('Corso terminato'));
	            }
	             *
	             */
	            if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR) {
	               $access_link = BaseHtmlLib::link("#",
	                        translateFN('Abilitazione in corso...'));
	            } elseif ($isStarted && !$isEnded) {
	            		            	
					$access_link = CDOMElement::create('div');
					$link = CDOMElement::create('a','href:view.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
					$link->addChild(new CText(translateFN('Accedi')));
					$access_link->addChild($link);
					
					// @author giorgio 24/apr/2013
					// adds whats new link if needed
					if (MultiPort::checkWhatsNew($userObj, $courseInstanceId, $courseId))
					{				
						$link = CDOMElement::create('a','href:user.php?id_node='.$nodeId.
																	 '&id_course='.$courseId.
																	 '&id_course_instance='.$courseInstanceId);
						$link->setAttribute("class", "whatsnewlink");
						$link->addChild(new CText(translateFN('Novit&agrave;')));
						$access_link->addChild($link);												
					}
					
	            }
	            if ($isEnded) {
	                $access_link = BaseHtmlLib::link("#",
	                        translateFN('Corso terminato'));
	            }	            
	
	            $tbody_dataAr[] = array(
	                $c['titolo'],
	                $started,
	                ts2dFN($start_date),
	                sprintf(translateFN('%d giorni'), $c['durata']),
	                ts2dFN($c['data_fine']),
	                $access_link
	            );
	        }
	
	        $data = BaseHtmlLib::tableElement('', $thead_dataAr, $tbody_dataAr);
	    } elseif ($found == 1) {
	    		    		    		    	
	        $c = $courseInstances[0];
	        $currentTimestamp = time();
	
	        $isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	        $isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	        $courseId = $c['id_corso'];
	        $nodeId = $courseId . '_0';
	        $courseInstanceId = $c['id_istanza_corso'];
	        $subscription_status = $c['status'];
	        // automatic entering the course
	        // redirect to the first node of the ONLY instance to which the student is subscribed
	        // only the first time (coming from login page)
	        // TODO: we should use some kind of constant to change this behavoiur for specific installation, provider, service, courses, instances, ....
	
	
	        $navigationHistoryObj = $_SESSION['sess_navigation_history'];
	        if(ADA_USER_AUTOMATIC_ENTER && $navigationHistoryObj->userComesFromLoginPage() && $isStarted && !$isEnded
	                && ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR)) {
	            header("Location: view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId");
	            exit();
	        }
	        else {
	            $started = ($c['data_inizio'] > 0 && $c['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
	            $start_date = ($c['data inizio'] > 0) ? $c['data_inizio'] : $c['data_inizio_previsto'];
	
	            $isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	            $isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	            $access_link = BaseHtmlLib::link("#",
	                        translateFN('Attendi apertura corso'));
	            /*
	            if ($isStarted && !$isEnded) {
	                $access_link = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",
	                        translateFN('Accedi'));
	            }
	            if ($isEnded) {
	                $access_link = BaseHtmlLib::link("#",
	                        translateFN('Corso terminato'));
	            }
	             *
	             */
	            if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR) {
	               $access_link = BaseHtmlLib::link("#",
	                        translateFN('Abilitazione in corso...'));
	            } elseif ($isStarted && !$isEnded) {
	            	/*
	            	 * @author giorgio 24/apr/2013
	            	 * 
	            	 * one course found, with something new to display
	            	 * set displayWhatsNew to true, the renderer will render the appropriate page
	            	 * 
	            	 * NOTE: user.php with appropriate parameters is a kind of "whats new" page
	            	 * 
	            	 */
					 if (MultiPort::checkWhatsNew($userObj, $courseInstanceId, $courseId)) {
					 	$displayWhatsNew = true;
					 }  
					 else {
					 	// resume 'normal' behaviour
					 	$access_link = CDOMElement::create('div');
					 	$link = CDOMElement::create('a','href:view.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
					 	$link->addChild(new CText(translateFN('Accedi')));
					 	$access_link->addChild($link);
					 }          	
	            }
	            if ($isEnded) {
	                $access_link = BaseHtmlLib::link("#",
	                        translateFN('Corso terminato'));
	            }
	
	
	            $tbody_dataAr[] = array(
	                $c['titolo'],
	                $started,
	                ts2dFN($start_date),
	                sprintf(translateFN('%d giorni'), $c['durata']),
	                ts2dFN($c['data_fine']),
	                $access_link
	            );
	            $data = BaseHtmlLib::tableElement('', $thead_dataAr, $tbody_dataAr);
	        }
	    } else {
	        $data = new CText(translateFN('Non sei iscritto a nessuna classe'));
	    }
	    // @author giorgio 24/apr/2013
	    // end else... line
	} 
} else {
    $data = new CText('');
}

/*
 * Output
 */
if (!$displayWhatsNew)
{
	// set default template
	$self = 'default';
	$content_dataAr = array(
	    'banner' => $banner,
	    'today' => $ymdhms,
	    'user_name' => $user_name,
	    'user_type' => $user_type,
	    'last_visit' => $userObj->get_last_accessFN(),
	    'message' => $message,
	//    'iscritto' => $sub_course_data,
	//    'iscrivibili' => $to_sub_course_data,
	    'course_title' => translateFN("Home dell'utente"),
	//    'corsi' => $corsi,
	//    'profilo' => $profilo,
	    'data' => $data->getHtml(),
	    'messages' => $user_messages->getHtml(),
	    'agenda' => $user_agenda->getHtml(),
	    'events' => $user_events_2->getHtml().$user_events->getHtml(),
	    'submenu_actions' => $submenu_actions,
	    'status' => $status
	);
}
else {
	// will use user.tpl template here
	
	// look for passed course in courseInstances array
	for ($i=0; $i<count($courseInstances); $i++)
	{
	// break out from the loop if id_corso is found, and in $i
	// we have the array index of the found course
	if ($courseInstances[$i]['id_corso'] == $get_id_course) break;
	}
	
	$c = $courseInstances[$i];	
	$currentTimestamp = time();
	
	$isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
	$isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;
	$subscription_status = $c['status'];	
	
	// @author giorgio 24/apr/2013 students link
	$class_label = translateFN("Classe");
	// $students = "<a href='class_info.php?op=students_list&id_course_instance=$courseInstanceId&id_course=$courseId'>$class_label</a>";
	$students =  BaseHtmlLib::link("class_info.php?op=students_list&id_course_instance=$courseInstanceId&id_course=$courseId'",$class_label);
	$students_link = $students->getHtml();	
	// @author giorgio
    // TODO: class_info.php non esiste, va creato o si toglie questo link?
    // unset ($students_link);
    
    // @author giorgio 26/apr/2013 new nodes    
	$provider = $common_dh->get_tester_info_from_id_course($courseId);
	$providerId = $provider['id_tester'];
	

    $whatsnew = $userObj->getwhatsnew();
    $new_nodes = $whatsnew[$provider['puntatore']];
    
    $new_nodes_html = '';
    //display a link to node if there are new nodes
    if (count($new_nodes) > 0) {    	
    	$olelem = CDOMElement::create('ol');    
    		    	
    	foreach ($new_nodes as $node)
    	{
    		if (strpos($node['id_nodo'],$courseId)!==false)
    		{
	    		$lielem = CDOMElement::create('li');
	    		$link = CDOMElement::create('a', 'href:view.php?id_node='.$node['id_nodo'].'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
	    		$link->addChild(new CText($node['nome']));
	    		$lielem->addChild ($link);
	    		$olelem->addChild($lielem);    		
	    		unset ($lielem);
	    		unset($link);    		    		    		
    		}
    	}    	    	    
    	$new_nodes_html = $olelem->getHtml();
    }
    
    
	
	// @author giorgio 24/apr/2013 forum messages (NOTES!!!!! BE WARNED: THESE ARE NOTES!!!)
	$msg_forum_count = MultiPort::count_new_notes($userObj,$courseInstanceId);
	//display a direct link to forum if there are new messages
	if ($msg_forum_count > 0) {
		$link = CDOMElement::create('a', 'href:main_index.php?op=forum&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
		$link->addChild(new CText($msg_forum_count));
		$msg_forum_count = $link->getHtml();
		unset($link);
	}
	
	// @author giorgio 24/apr/2013 private messages
	$msg_simple_count = 0;
	$msg_simpleAr =  MultiPort::getUserMessages($userObj);
	foreach ($msg_simpleAr as $msg_simple_provider) {
		$msg_simple_count += count($msg_simple_provider);
	}
	
	// @author giorgio 24/apr/2013 agenda messages
	$msg_agenda_count = 0;
	$msg_agendaAr = MultiPort::getUserAgenda($userObj);
	foreach ($msg_agendaAr as $msg_agenda_provider) {
		$msg_agenda_count += count($msg_agenda_provider);
	}
	
	// @author giorgio 24/apr/2013 gocontinue link
	$last_visited_node_id = $userObj->get_last_accessFN($courseInstanceId,"N");
	
	if  ((!empty($last_visited_node_id)) AND (!is_object($last_visited_node_id))&& $isStarted && !$isEnded){
		$last_node_visitedObj = BaseHtmlLib::link("view.php?id_course=$courseId&id_node=$last_visited_node_id&id_course_instance=$courseInstanceId",translateFN("Continua"));
		// echo "<!--"; var_dump($last_node_visitedObj);echo "-->";
		$last_node_visited_link =  $last_node_visitedObj->getHtml();
	
	} else {
		//$last_node_visitedObj = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",translateFN('Continua'));
		$last_node_visitedObj = BaseHtmlLib::link("#",translateFN(''));
		$last_node_visited_link = $last_node_visitedObj->getHtml();
	}
	
	// @author giorgio 24/apr/2013 gostart, goindex, goforum and gocontinue link
	// va sostituita con una select in AMA
	
	//	    Graphical disposition:
	
	$gostart_link = translateFN('Il corso non è ancora iniziato');
	if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR) {
		$gostart = BaseHtmlLib::link("#",
				translateFN('Abilitazione in corso...'));
		$gostart_link = $gostart->getHtml();
		$last_node_visited_link = '';
	
	} elseif ($isStarted && !$isEnded) {
	
		$gostart = BaseHtmlLib::link("view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",translateFN('Inizia'));
		$gostart_link = $gostart->getHtml();
		$goindex  = BaseHtmlLib::link("main_index.php?id_course=$courseId&id_course_instance=$courseInstanceId",translateFN('Indice'));
		$goindex_link = $goindex->getHtml();
		$goforum   = BaseHtmlLib::link("main_index.php?id_course=$courseId&id_course_instance=$courseInstanceId&op=forum",translateFN('Forum'));
	
		$goforum_link = $goforum->getHtml();
	
		if ($self_instruction) {
			if (($subscription_stopUT+AMA_SECONDS_IN_A_DAY) < time()) {
				$gostart = BaseHtmlLib::link("#",
						translateFN('Corso terminato...'));
				$gostart_link = $gostart->getHtml();
				$last_node_visited_link = '';
				$goindex_link = '';
			}
		}
	}
		
	if ($isEnded) {
		$gostart_link = translateFN('Il corso è terminato');
		$last_node_visited_link = '';
	}
	
	 
	$gochat_link = "";
	$content_dataAr['gostart'] = $gostart_link;
	$content_dataAr['gocontinue'] = $last_node_visited_link;
	$content_dataAr['goindex'] = $goindex_link;		
	if ($new_nodes_html!=='') $content_dataAr['new_nodes_links'] = $new_nodes_html;	
	// msg forum sono le note in realta'
	$content_dataAr['msg_forum'] = $msg_forum_count;
	$content_dataAr['msg_agenda'] =  $msg_agenda_count;
	$content_dataAr['msg'] = $msg_simple_count;
	$content_dataAr['goclasse'] = $students_link;
	$content_dataAr['goforum'] = $goforum_link;
	$content_dataAr['gochat'] = $gochat_link;
		
	$content_dataAr['banner'] = $banner;
	$content_dataAr['today'] = $ymdhms;
	$content_dataAr['user_name'] = $user_name;
	$content_dataAr['user_type'] = $user_type;
	$content_dataAr['last_visit'] = $userObj->get_last_accessFN();
	$content_dataAr['message'] = $message;
	$content_dataAr['course_title'] = translateFN("Home dell'utente"). " &gt; ".translateFN("Novità");
	$content_dataAr['submenu_actions'] =  $submenu_actions;
	$content_dataAr['status'] = $status;
}

ARE::render($layout_dataAr,$content_dataAr);

