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
/**
 * change the two below call to active to let the closed
 * instances completely disappear from the HTML table
 */
if (count($serviceProviders) == 1) {
    $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($serviceProviders[0]));
//     $courseInstances = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
    $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId(), true);
} else {
    foreach ($serviceProviders as $Provider) {
        $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
//         $courseInstances_provider = $provider_dh->get_course_instances_active_for_this_student($userObj->getId());
        $courseInstances_provider = $provider_dh->get_course_instances_for_this_student($userObj->getId(), true);
        $courseInstances = array_merge($courseInstances, $courseInstances_provider);
    }
}
$courseInstanceCommonAreaAr = array();
$courseInstanceHelpAr = array();
if(!AMA_DataHandler::isError($courseInstances)) {
	/**
	 * @author giorgio 23/apr/2015
	 * 
	 *  filter course instance that are associated to a level of service having nonzero
	 *  value in isPublic, so that all instances of public courses will not be shown here
	 */
	$courseInstances = array_filter($courseInstances, function($courseInstance) {
		if (is_null($courseInstance['tipo_servizio'])) $courseInstance['tipo_servizio'] = DEFAULT_SERVICE_TYPE;
		return (intval($_SESSION['service_level_info'][$courseInstance['tipo_servizio']]['isPublic'])===0);
	});
	
    $found = count($courseInstances);
//    if ($found > 0) 
//    {
            foreach($courseInstances as $c) {
                $courseId = $c['id_corso'];
                $serviceForInstanceAr = $common_dh->get_service_info_from_course($courseId);
                if (!AMA_DataHandler::isError($serviceForInstanceAr)) {
                    if ($serviceForInstanceAr[3] == ADA_SERVICE_HELP) {
                        $courseInstanceHelpAr[] = $c;
                    }elseif ($serviceForInstanceAr[3] == ADA_SERVICE_COMMON || ($serviceForInstanceAr[3] == ADA_SERVICE_COMMON_STUDENT && $userObj->getSerialNumber () != '')) {
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
                $HelpTitle = '<h2>'.translateFN('Cose che mi riguardano').'</h2>';

                 /* ***********************
                 * appointments and proposal for all instances
                 * $user_events and $user_events_2 are valorized in browsing_function.inc.php
                 */
                $serviceDOM = CDOMElement::create('div','id:servicesRequired');
                if (is_object($user_events) || is_object($user_events_2)) {
                    $divAppointments = CDOMElement::create('div','class:appointments');
//                    $divAppointments->addChild(new CText('<h3>'.translateFN('Appuntamenti').'</h3>'));
                    if ($user_events->getHtml() != '') $divAppointments->addChild($user_events);
                    if ($user_events_2->getHtml() != '') $divAppointments->addChild($user_events_2);
                    if ($user_events_2->getHtml() == '' && $user_events->getHtml() == '') {
                        $divAppointments->addChild(new CText(translateFN('Non ci sono appuntamenti')));
                    }
                    $content_dataAr['bloccoUnoAppuntamenti'] = '<h3>'.translateFN('Appuntamenti').'</h3>'.$divAppointments->getHtml();
                }   
                
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
         $CommonTitle = '<h2>'.translateFN('Aree comuni').'</h2>';
         $content_dataAr['bloccoDueTitolo'] = $CommonTitle;
         
         /*
          * COMMON AREA ALREADY SUBSCRIBED
          */
         $commonAreasSubscribedAr = array();
         $CommonAreaDOM = CDOMElement::create('div','id:CommonAreaRequired');
         if (count($courseInstanceCommonAreaAr) > 0) {
             foreach ($courseInstanceCommonAreaAr as $singleCommonArea) {
                 $commonAreasSubscribedAr[] = $singleCommonArea['id_istanza_corso'];
                 
                    /* ***********************
                     * INFO for each instance 
                     */
                    $divCommonNews = '';
                    $courseId = $singleCommonArea['id_corso'];
                    $courseDataAr = $dh->get_course($courseId);
                    if (!AMA_DataHandler::isError($courseDataAr)) {
                        $courseName = $courseDataAr['titolo'];
                        $nodeId = $courseId . '_0';
                        $courseInstanceId = $singleCommonArea['id_istanza_corso'];
                        $subscription_status = $singleCommonArea['status'];
                        $started = ($singleCommonArea['data_inizio'] > 0 && $singleCommonArea['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
                        $start_date = ($singleCommonArea['data inizio'] > 0) ? $singleCommonArea['data_inizio'] : $singleCommonArea['data_inizio_previsto'];
                        $isEnded = ($singleCommonArea['data_fine'] > 0 && $singleCommonArea['data_fine'] < time()) ? true : false;
                        $isStarted = ($singleCommonArea['data_inizio'] > 0 && $singleCommonArea['data_inizio'] <= time()) ? true : false;

                        $service = CDOMElement::create('div','id:serviceRequired'.$courseInstanceId);
                        $service->setAttribute('class', 'single_service');

                        $access_link = BaseHtmlLib::link("#", translateFN('Non sei ancora abilitato a partecipare...'));

                        if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR && $subscription_status!= ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) {
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
                                $link->setAttribute('href', 'sview.php?id_node='.$nodeId.'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
                                $link->addChild(new CText(translateFN('Accedi per partecipare...')));
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
                                            $link_news = CDOMElement::create('a','href:sview.php?id_node='.$new_node['id_nodo'].'&id_course='.$courseId.'&id_course_instance='.$courseInstanceId.'#'.$nodeId);
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
                        $CommonAreaDOM->addChild(new CText('<h3>'. $courseName.'</h3>'));
                        $CommonAreaDOM->addChild($service);
                        
                    }
             }
        } else {
            $data = new CText(translateFN('Non sei ancora iscritto a nessuna area comune'));
//            $CommonAreaDOM->addChild($data);
        }
         $content_dataAr['bloccoDueContenuto'] = $CommonAreaDOM->getHtml();

         
         /*
          * COMMON AREA TO SUBSCRIBED (if the user wish to)
          */

         // $userProvider = $GLOBALS['user_provider'];
         $field_list_ar = array('id_corso', 'data_fine', 'price', 'self_registration', 'open_subscription');
         $clause = "self_registration = 1 AND price = '0.00' AND open_subscription  = 1";
         $allServiceInstanceAr = $dh->course_instance_find_list($field_list_ar, $clause);
         if (!AMA_DataHandler::isError($allServiceInstanceAr)) {
             $commonAreaToSubscibeAr = array();
    //         print_r($allServiceInstanceAr);
             foreach ($allServiceInstanceAr as $singleServiceInstanceAr) {
                 if (!in_array($singleServiceInstanceAr[0], $commonAreasSubscribedAr)) {
                     $instanceIdToSub = $singleServiceInstanceAr[0];
                     $courseIdToSub = $singleServiceInstanceAr[1];
                     $serviceForInstanceAr = $common_dh->get_service_info_from_course($courseIdToSub);
                     if (!AMA_DataHandler::isError($serviceForInstanceAr)) {
                         if ($serviceForInstanceAr[3] == ADA_SERVICE_COMMON || ($serviceForInstanceAr[3] == ADA_SERVICE_COMMON_STUDENT && $userObj->getSerialNumber() != '')) {
                             array_push($commonAreaToSubscibeAr, $singleServiceInstanceAr);
                         }
                     }
                 }
             }
             if (count($commonAreaToSubscibeAr)> 0 ) {
                 $divCommonToSubscribe = CDOMElement::create('div','id:commonToSubscribe');
                 foreach ($commonAreaToSubscibeAr as $singleAreaToSubscribe) {
                     $instanceIdToSub = $singleAreaToSubscribe[0];
                     $courseIdToSub = $singleAreaToSubscribe[1];
                     $courseInfoTmp = $dh->get_course($courseIdToSub);
                     if (!AMA_DataHandler::isError($courseInfoTmp)) {
                         $courseName = $courseInfoTmp['titolo'];
                         $nodeId = $courseIdToSub . '_0';
                         $divSingleAreaToSubscribe = CDOMElement::create('div','id:commonToSubscribe'.$instanceIdToSub);
                         $divSingleAreaToSubscribe->setAttribute('class', 'single_service');
//                         $divSingleAreaToSubscribe->addChild(new CText('<h3>'.translateFN('Area comune '). $courseName.'</h3>'));
//                         $divSingleAreaToSubscribe->addChild(new CText('<h3>'. $courseName.'</h3>'));
                         $AreaCommonPreview = substr($courseInfoTmp['descr'], 0, 50).'...';
                         $divSingleAreaToSubscribe->addChild(new CText($AreaCommonPreview.'<br />'));
                         $link = CDOMElement::create('a','href:student_service_instance_subscribe.php?&id_course='.$courseIdToSub.'&id_course_instance='.
                                 $instanceIdToSub.'&userId='.$userObj->getId());
                         $link->addChild(new CText(translateFN('Entra nell\'area comune')));
                         $divSingleAreaToSubscribe->addChild($link);
                     }
                     $divCommonToSubscribe->addChild(new CText('<h3>'. $courseName.'</h3>'));
                     $divCommonToSubscribe->addChild($divSingleAreaToSubscribe);
                 }
                 $content_dataAr['bloccoDueIscrizione'] = $divCommonToSubscribe->getHtml();
             }

         }
//         $id_instance = $course_instanceAr[0][0];
         

        
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

                $content_dataAr['bloccoUnoAskService'] = '<h3>'.translateFN('Chiedi aiuto').'</h3>'.$askServiceDiv->getHtml();
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
	$content_dataAr['status'] = $status;
//        $content_dataAr['events'] = $user_events_2->getHtml().$user_events->getHtml();

//print_r($content_dataAr);
        

$layout_dataAr['CSS_filename'] = array (
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_DATATABLE,
		JQUERY_DATATABLE_DATE,
		ROOT_DIR.'/js/include/jquery/dataTables/formattedNumberSortPlugin.js',
		JQUERY_NO_CONFLICT,
		'user.js' // this file may use different templates, force user.js inclusion here
);

ARE::render($layout_dataAr,$content_dataAr,NULL,array('onload_func'=>'initDoc();'));

