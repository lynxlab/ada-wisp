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
    //$courseInstances = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
    $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
} else {
    foreach ($serviceProviders as $Provider) {
        $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
        //$courseInstances_provider = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
        $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
        $courseInstances = array_merge($courseInstances, $courseInstances_provider);
    }
}
$courseInstanceCommonAreaAr = array();
$courseInstanceHelpAr = array();
if(!AMA_DataHandler::isError($courseInstances)) {
    $found = count($courseInstances);
//    if ($found > 0) 
//    {
            foreach($courseInstances as $c) {
                $courseId = $c['id_corso'];
                $serviceForInstanceAr = $common_dh->get_service_info_from_course($courseId);
                
                if (!AMA_DataHandler::isError($serviceForInstanceAr)) {
                    if ($serviceForInstanceAr[3] == ADA_SERVICE_HELP) {
                        $courseInstanceHelpAr[] = $c;
                    }elseif ($serviceForInstanceAr[3] == ADA_SERVICE_LEG || ($serviceForInstanceAr[3] == ADA_SERVICE_LEG_NO_TIMELINE && $userObj->getSerialNumber () != '')) {
                        $courseInstanceCommonAreaAr[] = $c;
                    } 
                }
            }
//            if (count($courseInstanceHelpAr) > 0 && $userObj->getSerialNumber() != '') {
            if ($userObj->getSerialNumber() != '') {
                /*
                 * disable the widget (to be used only for generic registered users)
                 */
                $layout_dataAr['widgets']['bloccoUnoContenutoWidget'] = array ("active"=>0);
                $HelpTitle = '<h2>'.translateFN('Consulenze').'</h2>';

                
                $serviceDOM = CDOMElement::create('div','id:servicesRequired');
                
                
                foreach($courseInstanceHelpAr as $c) {
                    
                    /* ***********************
                     * INFO for each instance 
                     */
                    $courseId = $c['id_corso'];
                    $courseDataAr = $dh->get_course($courseId);
                    $divNews = '';
                    if (!AMA_DataHandler::isError($courseDataAr)) {
                        $courseName = $courseDataAr['titolo'];
                        $nodeId = $courseId . '_0';
                        $courseInstanceId = $c['id_istanza_corso'];
                        $subscription_status = $c['status'];
                        $started = ($c['data_inizio'] > 0 && $c['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
                        $start_date = ($c['data inizio'] > 0) ? $c['data_inizio'] : $c['data_inizio_previsto'];
                        $isEnded = ($c['data_fine'] > 0 && $c['data_fine'] < time()) ? true : false;
                        $isStarted = ($c['data_inizio'] > 0 && $c['data_inizio'] <= time()) ? true : false;

                        $service = CDOMElement::create('div','id:serviceRequired'.$courseInstanceId);
                        $service->setAttribute('class', 'single_service');
//                        $service->addChild(new CText('<h3>'.translateFN('Aiuto per '). $courseName.'</h3>'));

                        $access_link = BaseHtmlLib::link("#", translateFN('Attendi che ti contatti un consulente...'));

                        if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR && $subscription_status!= ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) {
                                $access_link = BaseHtmlLib::link("#",translateFN('Attendi che ti contatti un consulente...'));
                        } elseif ($isStarted && !$isEnded) {
                                $tutorAssignedAR = $dh->course_instance_tutor_info_get($courseInstanceId,1);
                                if (!AMA_DataHandler::isError($tutorAssignedAR) && sizeof($tutorAssignedAR) > 0 && $tutorAssignedAR[0] != '') {
                                    $tutorText = sprintf(translateFN('ti sta aiutando %s'), ucfirst($tutorAssignedAR[1]) . ' ' . ucfirst($tutorAssignedAR[2]));
                                } else {
                                    $tutorText = '';
                                }
                                $access_link = CDOMElement::create('div','class:helpRequired');
                                $access_link->addChild(new CText($tutorText . ' '));
                                $access_link->addChild(new CText('<br /> '));
                                $link = CDOMElement::create('a');
                                $link->setAttribute('href','sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
                                $link->addChild(new CText(translateFN('Accedi per continuare...')));
                                $access_link->addChild($link);

                                /* ***********************
                                 * get new nodes for each instance
                                 */
                                $nodeTypesArray = array ( ADA_LEAF_TYPE, ADA_GROUP_TYPE, ADA_NOTE_TYPE );
                                $instancesArray[0]['id_istanza_corso'] = $courseInstanceId;
                                $new_nodes = $dh->get_new_nodes($userObj->getId(), $maxNodes = 3, $nodeTypesArray,$instancesArray);
                                if (!AMA_DataHandler::isError($new_nodes) && sizeof($new_nodes) > 0) {
                                    $ulNews = '';
                                    foreach ($new_nodes as $new_node) {
                                        $courseOfNewNodeAr = explode('_',$new_node['id_nodo']);
                                        if ($courseId == $courseOfNewNodeAr[0]) {
                                            if ($new_node['tipo'] == ADA_NOTE_TYPE && $new_node['ID_ISTANZA'] == $courseInstanceId) {
                                                if (!is_object($ulNews)) $ulNews = CDOMElement::create('ul','class:ulNews');
                                                $liNews = CDOMElement::create('li');
                                                $link_news = CDOMElement::create('a');
                                                $link_news->setAttribute('href','sview.php?id_node='.$new_node['id_nodo'].'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$new_node['id_nodo']);
                                                $link_news->addChild(new CText($new_node['nome']));
                                                $liNews->addChild($link_news);
                                                $ulNews->addChild($liNews);
                                            }
                                        }
                                    }
                                    if (is_object($ulNews)) {
                                        $divNews = CDOMElement::create('div','class:newsInHelpRequired');
                                        $newsText = translateFN('Novità');
                                        $divNews->addChild(new CText('<h4>'.$newsText.'</h4>'));
                                        $divNews->addChild($ulNews);
                                    }

                                }
                                
                                $lastFiles = $userObj->get_new_files($courseInstanceId);
                                
                                if (!is_null($lastFiles) && !empty($lastFiles))
                                {
                                	$divFiles = CDOMElement::create('div','class:newFiles');
                                	$divFiles->addChild (new CText('<h4>'.translateFN('documenti recenti').'</h4>'));
                                	$ulFiles = CDOMElement::create('ul');
                                	
                                	foreach ($lastFiles as $lastFile)
                                	{
                                		$liFile = CDOMElement::create('li');
                                		$queryString = '?file='.$lastFile['link'] .
                                		               '&id_node='.$lastFile['id_node'].
                                		               '&id_course='.$lastFile['id_course'].
                                		               '&id_course_instance='.$lastFile['id_course_instance'];
                                		$aFile  = CDOMElement::create('a','href:download.php'.$queryString);
                                		$aFile->addChild (new CText($lastFile['displaylink']));
                                		$liFile->addChild ($aFile);
                                		$ulFiles->addChild ($liFile);
                                	}                                	
                                	$divFiles->addChild($ulFiles);
                                }
                        }
                        elseif ($isEnded) {
                            $access_link = BaseHtmlLib::link("#",
                                    translateFN('Servizio terminato'));
                        }
                        $service->addChild($access_link);
                        if (is_object($divNews))  $service->addChild($divNews);
                        if (is_object($divFiles)) $service->addChild($divFiles);
//                        $serviceDOM->addChild($divAppointments)
                        $serviceDOM->addChild(new CText('<h3>'.translateFN('Aiuto per '). $courseName.'</h3>'));
                        $serviceDOM->addChild($service);
                        
                    }

            }
        } else {
            $data = new CText(translateFN('Non hai ancora chiesto aiuto per nessun argomento'));
//            $serviceDOM->addChild($data);
        }
        
        /* *****************
         * COMMON AREA SERVICE
         */
         $UserServices = '<h2>'.translateFN('Lista servizi').'</h2>';
         $content_dataAr['bloccoDueTitolo'] = $UserServices;
         
         /*
          * COMMON AREA ALREADY SUBSCRIBED
          */
         
         $User_Services = CDOMElement::create('div','id:user_services');
        
         if (count($courseInstances) > 0) {
             foreach ($courseInstances as $Instance) {
                /* ***********************
                 * INFO for each instance 
                 */
                $divCommonNews = '';
                $courseId = $Instance['id_corso'];


                /* service level */
                $clause = ' st.id_corso ='.$courseId;
                $ServiceInfo = $common_dh->get_services(null,$clause);
                $serviceLevel=$ServiceInfo[0][2];

                $courseDataAr = $dh->get_course($courseId);
                if(!AMA_DataHandler::isError($courseDataAr) && (!empty($courseDataAr))){
                    if($serviceLevel!=ADA_SERVICE_HELP)
                    {
                        if($serviceLevel == ADA_SERVICE_MANUALE){

                            $courseInstanceId = $Instance['id_istanza_corso'];
                            $nodeId = $courseId . '_0';
                            $link_Manuale = 'browsing/view.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId;
                            
                        }
                        else{

                            $courseName = $courseDataAr['titolo'];
                            $nodeId = $courseId . '_0';
                            $courseInstanceId = $Instance['id_istanza_corso'];
                            $subscription_status = $Instance['status'];
                            $started = ($Instance['data_inizio'] > 0 && $Instance['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
                            $start_date = ($Instance['data inizio'] > 0) ? $Instance['data_inizio'] : $Instance['data_inizio_previsto'];
                            $isEnded = ($Instance['data_fine'] > 0 && $Instance['data_fine'] < time()) ? true : false;
                            $isStarted = ($Instance['data_inizio'] > 0 && $Instance['data_inizio'] <= time()) ? true : false;

                            $service = CDOMElement::create('div','id:serviceRequired'.$courseInstanceId);
                            $service->setAttribute('class', 'single_service');

                            $access_link = BaseHtmlLib::link("#", translateFN('Non sei ancora abilitato a partecipare...'));

                            if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR) {
                                    $access_link = BaseHtmlLib::link("#",translateFN('Non sei ancora abilitato a partecipare...'));
                            } elseif ($isStarted && !$isEnded) {
                                    $tutorAssignedAR = $dh->course_instance_tutor_info_get($courseInstanceId,1);
                                    if (!AMA_DataHandler::isError($tutorAssignedAR) && sizeof($tutorAssignedAR) > 0 && $tutorAssignedAR[0] != '') {
                                        $tutorText = translateFN('il moderatore dell\'area è').' '. ucfirst($tutorAssignedAR[1]) . ' ' . ucfirst($tutorAssignedAR[2]);
                                    } else {
                                        $tutorText = '';
                                    }
                                    $access_link = CDOMElement::create('div','class:helpRequired');
                                    $access_link->addChild(new CText($tutorText . ' '));
                                    $access_link->addChild(new CText('<br /> '));
                                    $link = CDOMElement::create('a');
                                    if($serviceLevel == ADA_SERVICE_LEG){
                                        $link->setAttribute('href', 'sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
                                    }
                                    else
                                        {
                                        $link->setAttribute('href', 'view.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
                                    }
                                    $link->addChild(new CText(translateFN('Accedi')));
                                    $access_link->addChild($link);

                                    /* ***********************
                                     * get new nodes for each instance
                                     */
                                    $ulNews = '';
                                    $nodeTypesArray = array ( ADA_LEAF_TYPE, ADA_GROUP_TYPE, ADA_NOTE_TYPE );
                                    $instancesArray[0]['id_istanza_corso'] = $courseInstanceId;
                                    $new_nodes = $dh->get_new_nodes($userObj->getId(), $maxNodes = 3, $nodeTypesArray,$instancesArray);
                                    if (!AMA_DataHandler::isError($new_nodes) && sizeof($new_nodes) > 0) {


                                        foreach ($new_nodes as $new_node) {
                                            $courseOfNewNodeAr = explode('_',$new_node['id_nodo']);
                                            if ($courseId == $courseOfNewNodeAr[0]) {
                                                if (!is_object($ulNews)) $ulNews = CDOMElement::create('ul','class:ulNews');
                                                $liNews = CDOMElement::create('li');
            //                                        $access_news->addChild(new CText(translateFN('Hai chiesto di essere aiutato per '). $courseName . ', '. $tutorText . ' '));
                                                if($serviceLevel == ADA_SERVICE_LEG){
                                                    $link_news = CDOMElement::create('a','href:sview.php?id_node='.$new_node['id_nodo'].'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
                                                }
                                                else{
                                                    $link_news = CDOMElement::create('a','href:view.php?id_node='.$new_node['id_nodo'].'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
                                                }
                                                $link_news->addChild(new CText($new_node['nome']));
                                                $liNews->addChild($link_news);
                                                $ulNews->addChild($liNews);
                                            }
                                        }
                                        if (is_object($ulNews)) {
                                            $divCommonNews = CDOMElement::create('div','class:newsInCommonArea');
                                            $newsText = translateFN('Novità');
                                            $divCommonNews->addChild(new CText('<h4>'.$newsText.'</h4>'));
                                            $divCommonNews->addChild($ulNews);

                                        }
                                    }
                            }
                            elseif ($isEnded) {
                                $access_link = BaseHtmlLib::link("#",
                                        translateFN('Servizio terminato'));
                            }
                            $service->addChild($access_link);
                            if (is_object($divCommonNews)) $service->addChild($divCommonNews);
            //                        $serviceDOM->addChild($divAppointments)
                            $User_Services->addChild(new CText('<h3>'. $courseName.'</h3>'));
                            $User_Services->addChild($service);


                    }
                } 
              } 
           }
        } else {
            $data = new CText(translateFN('Non sei ancora iscritto a nessun servizio'));
//            $CommonAreaDOM->addChild($data);
        }
        
         $content_dataAr['bloccoDueContenuto'] = $User_Services->getHtml();

    
            // @author giorgio 24/apr/2013
	    // end else... line
                    // @author giorgio 24/apr/2013
                    // adds whats new link if needed
        /*
            if (MultiPort::checkWhatsNew($userObj, $courseInstanceId, $courseId))
            {				
                    $link = CDOMElement::create('a','href:user.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId);
                    $link->setAttribute("class", "whatsnewlink");
                    $link->addChild(new CText(translateFN('Novit&agrave;')));
                    $access_link->addChild($link);												
            }
         * 
         */
/*
     } else
     {
                $data = new CText(translateFN('Non sei iscritto a nessuna classe'));
     }
 * 
 */
} 
                
        
        /* ASK SERVICE FORM
         * if student show the ask service form
         */
        if ($userObj->getSerialNumber() != '') {
            /*
             * show the form to ask help
             */
            require_once ROOT_DIR . '/include/Forms/AskServiceForm.inc.php';
            require_once ROOT_DIR . '/include/HtmlLibrary/AskServiceModuleHtmlLib.inc.php';
            require_once ROOT_DIR . '/include/AskService.inc.php';
            if (!MULTIPROVIDER)
            {
                    // if provider is not set or there's an error loading its id, retirect to home
                    $redirect = false;

                    /**
                     * either the user is anonymous and has the $_GLOBALS['user_provider'] set
                     * or it is a logged user and then it'll have client0 by default and only 
                     * the client it's registered into and this info will be index 1 of the returned array
                     * 
                     */
                    if (isset($GLOBALS['user_provider']))
                            $user_provider_name = $GLOBALS['user_provider'];
                    else if ($userObj instanceof ADALoggableUser)
                    {
                            $tmpTesters = $userObj->getTesters();
                            if (!empty($tmpTesters)) $user_provider_name = $tmpTesters[1];
                    }

                    if (isset($user_provider_name))
                    {
                            $userTesterInfo = $common_dh->get_tester_info_from_pointer($user_provider_name);
                            $user_provider_id = (!AMA_DB::isError($userTesterInfo)) ? $userTesterInfo[0] : null;
                            $redirect = is_null($user_provider_id);
                    }

                    else $redirect = true;

                    if (!$redirect) $publishedServices = $common_dh->get_published_courses($user_provider_id);
                    else {
                            $url = HTTP_ROOT_DIR . ((isset($_COOKIE['ada_provider'])) ? '/'.$_COOKIE['ada_provider'].'/info.php' : '');
                            header ('Location: '.$url);
                            die();
                    }		
            } else {
                    $publishedServices = $common_dh->get_published_courses();
            }

            if(!AMA_Common_DataHandler::isError($publishedServices)) {
                $serviceToSubscribeAr = array();
                foreach($publishedServices as $service) {
                       if ($service['livello'] == ADA_SERVICE_HELP) {

                           $serviceId = $service['id_servizio'];
                           $serviceName = $service['nome'];
                           $coursesAr = $common_dh->get_courses_for_service($serviceId); 
                           if (!AMA_DataHandler::isError($coursesAr)) {
                                $currentTesterId = 0;
                                $currentTester = '';
                                $provider_dh = null;
                                foreach($coursesAr as $courseData) {
                                    if (defined ('PUBLIC_COURSE_ID_FOR_NEWS') && intval(PUBLIC_COURSE_ID_FOR_NEWS)>0 && PUBLIC_COURSE_ID_FOR_NEWS!=$courseData['id_corso']) {
                                        $newTesterId = $courseData['id_tester'];
                                        $courseId = $newTesterId . '_' . $courseData['id_corso'];
                                        $serviceToSubscribeAr[$courseId] = $serviceName;
                                    }
                                }   
                           }
                      } 

                }
                if(sizeof($serviceToSubscribeAr) > 0) {
                    $AskServiceForm = new AskServiceForm($serviceToSubscribeAr,$user_provider_id);
                } 

            } else {
                $data = new CText(translateFN('Non è possibile chiedere aiuto'));
            }
            if (is_object($AskServiceForm)) {
                
                $askServiceHelp = translateFN('Se non hai ancora chiesto aiuto puoi farlo ora!');
                $askServiceDiv = CDOMElement::create('div','id:Askservice');
                $askServiceDiv->setAttribute('class', 'single_service');
    //            $askServiceDiv->addChild(new CText('<h3>'.translateFN('Chiedi aiuto').'</h3>'));
                $askServiceDiv->addChild(new CText($askServiceHelp));
                $askServiceDiv->addChild(new CText($AskServiceForm->getHtml()));

                $content_dataAr['bloccoUnoAskService'] = '<h3>'.translateFN('Chiedi una consulenza').'</h3>'.$askServiceDiv->getHtml();
            }
            //print_r($content_dataAr);
            
            
        } else {
            
        }
           
        /*******************
         * end Ask Service form
         */




                $navigationHistoryObj = $_SESSION['sess_navigation_history'];
                /*
                if(ADA_USER_AUTOMATIC_ENTER && $navigationHistoryObj->userComesFromLoginPage() && $isStarted && !$isEnded
                        && ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR)) {
                    header("Location: view.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId");
                    exit();
                }
                    else {
                 * 
                 */
                 if (MultiPort::checkWhatsNew($userObj, $courseInstanceId, $courseId)) {
                        $displayWhatsNew = true;
                 }  
                 else {
                 	// resume 'normal' behaviour
                 	$access_link = CDOMElement::create('div');
                 	$link = CDOMElement::create('a','href:sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
                 	$link->addChild(new CText(translateFN('Accedi')));
                 	$access_link->addChild($link);
                 }

$last_access=$userObj->get_last_accessFN(null,"UT",null);
$last_access=AMA_DataHandler::ts_to_date($last_access);

if($last_access=='' || is_null($last_access))
{
    $last_access='-';
}
/*
 * Output
 */
	
	// @author giorgio 24/apr/2013 gocontinue link
	$last_visited_node_id = $userObj->get_last_accessFN($courseInstanceId,"N");
	
	if  ((!empty($last_visited_node_id)) AND (!is_object($last_visited_node_id))&& $isStarted && !$isEnded){
		$last_node_visitedObj = BaseHtmlLib::link("sview.php?id_course=$courseId&id_node=$last_visited_node_id&id_course_instance=$courseInstanceId#$nodeId",translateFN("Continua"));
		// echo "<!--"; var_dump($last_node_visitedObj);echo "-->";
		$last_node_visited_link =  $last_node_visitedObj->getHtml();
	
	} else {
		//$last_node_visitedObj = BaseHtmlLib::link("sview.php?id_node=$nodeId&id_course=$courseId&id_course_instance=$courseInstanceId",translateFN('Continua'));
		$last_node_visitedObj = BaseHtmlLib::link("#",translateFN(''));
		$last_node_visited_link = $last_node_visitedObj->getHtml();
	}
	
        $imgAvatar = $userObj->getAvatar();
        $avatar = CDOMElement::create('img','src:'.$imgAvatar);
        $avatar->setAttribute('class', 'img_user_avatar');
        
        $content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();
        
        if (array_key_exists($userObj->getSerialNumber(), $GLOBALS['user_type_labels'])) {
        	$UserProfile = ' ('.translateFN($GLOBALS['user_type_labels'][$userObj->getSerialNumber()]).')';
        } else {
        	$UserProfile = '';
        }
          
        $welcome_msg = translateFN('<strong> Benvenuto/a ').$userObj->getFullName().'!'.$UserProfile.translateFN(' Questa è la tua Home Page </strong>');

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
	//$content_dataAr['last_visit'] = $userObj->get_last_accessFN();
        $content_dataAr['last_visit'] = $last_access;
	$content_dataAr['message'] = $message;
        $content_dataAr['welcome_msg'] = $welcome_msg;
	$content_dataAr['course_title'] = translateFN("Home dell'utente");
	$content_dataAr['submenu_actions'] =  $submenu_actions;
        
        $content_dataAr['user_avatar'] = $avatar->getHtml(); 
        if (is_object($serviceDOM)) {
            $content_dataAr['bloccoUnoContenuto'] =  $serviceDOM->getHtml();
            $content_dataAr['bloccoUnoTitolo'] =  $HelpTitle;
        } else {
            $content_dataAr['bloccoUnoTitolo'] =  '<h2>'.translateFN('Le ultime notizie').'</h2>';
            $content_dataAr['bloccoUnoH3Widget'] =  '<h3>'.translateFN('Twitter').'</h3>';
        }
        if(!is_null($link_Manuale)){
            $content_dataAr['manuale'] = $link_Manuale;
            $content_dataAr['info'] = translateFN('Manuale');
        }
        else
        {
            $link_info='info.php';
            $content_dataAr['manuale'] = $link_info;
            $content_dataAr['info'] = translateFN('Informazioni');
        }
	$content_dataAr['status'] = $status;
//        $content_dataAr['events'] = $user_events_2->getHtml().$user_events->getHtml();

//print_r($content_dataAr);

	/**
	 * user home page links for HOLIS
	 */
	$content_dataAr['giurLink'] =  MODULES_LEX_HTTP;
	$content_dataAr['orgLink'] =   HTTP_ROOT_DIR.'/browsing/view.php?id_node=10_0&id_course=10&id_course_instance=19#10_0';
	if ($userObj->getSerialNumber()==AMA_TYPE_USER_LAWYER) {
		// leg no timeline for lawyer only so the link is to view.php
		$content_dataAr['legLink'] =   HTTP_ROOT_DIR.'/browsing/view.php?id_node=6_0&id_course=6&id_course_instance=5#6_0';
	} else {
		// leg with timeline for other users, so the link is to sview.php
		$content_dataAr['legLink'] =   HTTP_ROOT_DIR.'/browsing/sview.php?id_node=6_0&id_course=6&id_course_instance=5#6_0';
	}
	
	$content_dataAr['soluzioniLink'] =  HTTP_ROOT_DIR.'/browsing/view.php?id_node=5_0&id_course=5&id_course_instance=3#5_0';
	$content_dataAr['etutoringLink'] =  HTTP_ROOT_DIR.'/browsing/ask_service.php';
	$content_dataAr['elearningLink'] =  HTTP_ROOT_DIR.'/browsing/view.php?id_node=8_0&id_course=8&id_course_instance=17#8_0';
	$content_dataAr['fontiLink'] =  HTTP_ROOT_DIR.'/browsing/sview.php?id_node=4_0&id_course=4&id_course_instance=2#4_0';
	
        

/**
 * Sends data to the rendering engine
 * 
 * @author giorgio 25/set/2013
 * REMEMBER!!!! If there's a widgets/main/index.xml file
 * and the index.tpl has some template_field for the widget
 * it will be AUTOMAGICALLY filled in!!
 */
// ARE::render($layout_dataAr,$content_dataAr);
		$layout_dataAr['JS_filename'] = array(
				JQUERY,
				JQUERY_UI,
				JQUERY_NO_CONFLICT
		);
                $layout_dataAr['CSS_filename'] = array (
                    JQUERY_UI_CSS,
                    );
//		$optionsAr['onload_func'] = '';
		$optionsAr['onload_func'] = 'initDoc();';
ARE::render($layout_dataAr, $content_dataAr, NULL, (isset($optionsAr) ? $optionsAr : NULL) );        
//ARE::render($layout_dataAr,$content_dataAr);

