<?php
/**
 *
 * @package		user
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
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
 * Performs basic controls before entering this module
 */
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('layout'),
    AMA_TYPE_STUDENT => array('layout'),
);

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/comunica/include/ChatRoom.inc.php';


require_once ROOT_DIR . '/include/Forms/AskServiceForm.inc.php';
require_once ROOT_DIR . '/include/HtmlLibrary/AskServiceModuleHtmlLib.inc.php';
require_once ROOT_DIR . '/include/AskService.inc.php';


$op = DataValidator::validate_string($op);
$today_date = today_dateFN();
if ($op == false) $op = 'default';
//$self = 'list_chatrooms'; // x template
$self = whoami();

if(!$userObj instanceof ADAUser) {
    header('Location: ' . HTTP_ROOT_DIR . '/login_required.php');
    exit();
}

$error_page = $_SERVER['HTTP_REFERER'];
if ($message == null || !isset($message)) 
    $message = '';
$id_user = $userObj->getId();
$name = $userObj->getFirstName();
$surname = $userObj->getLastName();

        
switch ($op) {
    case 'subscribe':
      $idProviderAndCourse = DataValidator::validate_node_id($id_service); // ID course is composed by idTtester_idCourse so we can use the Node Data Validator
      $idTmpAr = explode('_',$idProviderAndCourse);
//      $idTmpAr = explode($idProviderAndCourse, '_'); // SERVE PER PRODURRE UN ERRORE
      $id_course = $idTmpAr[1];
      $providerId = $idTmpAr[0];
      
      $start_date1 = 0;
      $start_date2 = AMA_DataHandler::date_to_ts("now");
//      $days = $serviceinfoAr[4];
//      print_r($_POST);
      
      /*
       * creating course/service instance
       */
      
      /*
       * FIXME: durata must be received from service parameter
       */
      $istanza_ha = array(
            'data_inizio'=>$start_date1,
            'durata'=>'365',
//            'durata'=>$days,
            'data_inizio_previsto'=>$start_date2,
            'id_layout'=>NULL,
            'self_instruction' => 0,
            'open_subscription' => 1,
            'self_registration' => 0
      );

        $serviceAr = $common_dh->get_service_info_from_course($id_course);
        $service_name = $serviceAr[1];
        $providerInfoAr = $common_dh->get_tester_info_from_id($providerId);
        if(!AMA_Common_DataHandler::isError($providerInfoAr)) {
            $provider = $providerInfoAr[10];
            $provider_name = $providerInfoAr[1];

            $providerAr[0] = $provider; // it is a pointer (string)
            $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($provider));
        } else {
           $message = urlencode(translateFN("Errore nella richiesta di servizio: "));
           $errorObj = new ADA_Error($providerInfoAr,$message,NULL,NULL,NULL,$error_page.'?message='.$message);
//           $errorObj = new ADA_Error($providerInfoAr,$message,NULL,NULL,NULL,$error_page);
            
        }
             
      /*
       *  add an instance to tester db
       */
      $id_instance = $provider_dh->course_instance_add($id_course, $istanza_ha);

      if ((!AMA_DataHandler::isError($id_instance)) OR ($id_instance->code == AMA_ERR_UNIQUE_KEY)){
        // we add an instance OR there already was one with same data

          /*
           * 
        // get an instance
            $clause = "id_corso = $id_course AND data_inizio_previsto = $start_date2 AND durata  = $days";
            $course_instanceAr = $provider_dh->course_instance_find_list(NULL, $clause);
            $id_instance = $course_instanceAr[0][0];
           */

          /*
           * presubscribe user to the instance
           */
            $res_presub = $provider_dh->course_instance_student_presubscribe_add($id_instance,$id_user);
            if ((AMA_DataHandler::isError($res_presub)) && ($res_presub->code != AMA_ERR_UNIQUE_KEY)){
                $message = urlencode(translateFN("Errore nella richiesta di servizio:"). ' '. $res_presub->code);
                $errorObj = new ADA_Error($res_inst_add,$message,NULL,NULL,NULL,$error_page.'?message='.$message);
            }

      } else {
        $message = urlencode(translateFN("Errore nella richiesta di servizio: 1"));
        $errorObj = new ADA_Error($res_inst_add,$message,NULL,NULL,NULL,$error_page.'?message='.$message);
      }        
      
      /*
       * Prepare Feeedback
       */
      $userHomePage=$userObj->getHomePage();
        $username = $userObj->getUserName();
        $dataAr= array (
            'question' => $question,
            'name'=> $name,
            'surname'=>$surname,
            'service_name'=> $service_name,
            'userHomePage' => $userHomePage   
        );
        $data = AskServiceModuleHtmlLib::getFeedbackTextHtml($dataAr);
        
        /*
         * Prepare and send message to User
         */

          $mh = MessageHandler::instance(MultiPort::getDSN($provider));

          $MailText4User = AskServiceModuleHtmlLib::getFeedbackTextPlain($dataAr);
          $admtypeAr = array(AMA_TYPE_SWITCHER);
          //$admList = $common_dh->get_users_by_type($admtypeAr);
          $admList = $provider_dh-> get_users_by_type($admtypeAr, true);

          if (!AMA_DataHandler::isError($admList)){
                        $adm_uname = $admList[0]['username'];
                        $adm_id = $admList[0]['id_utente'];
                        $adm_name = $admList[0]['nome'];
                        $adm_surname = $admList[0]['cognome'];
          } else {
                        $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
          }

        $titolo = PORTAL_NAME . ': '.translateFN('Richiesta di servizio') .' '. $dataAr['service_name'];
        $username = $userObj->getUserName();
        $recipientsAr =  array($username);
        $message_ha = array();
        $message_ha['titolo'] = $titolo;
        $message_ha['testo'] = $MailText4User; // $data->getHtml(); //$MailText4User;
        $message_ha['destinatari'] = $recipientsAr;
        $message_ha['data_ora'] = "now";
        $message_ha['tipo'] = ADA_MSG_MAIL;
        $message_ha['mittente'] = $adm_uname;

        // delegate sending to the message handler
         $res = $mh->send_message($message_ha);

        if (AMA_DataHandler::isError($res)){
          // $errObj = new WISP_Error($res,translateFN('Impossibile spedire il messaggio'),
          //NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
        }

        
        /*
         * Prepare and send message to Switcher
         * 
         * Switcher receive a reminder message 
         * 
         * A message is sent as additional text containing the user question
         * A token is added to the subject
         * The token is generated from:
         * + User id
         * + Switcher id
         * + instance id
         * + Time (added by the method invocated)
         */
        $helpRequiredToken = AskService::generateQuestionToken($id_user, $adm_id,$id_instance);

      /* ****************************
       * add chatroom to the instance
       */
            $data_inizio_previsto = $istanza_ha['data_inizio'];
            $durata = $istanza_ha['durata'];
            $data_fine = $dh->add_number_of_days($durata,$data_inizio);
            $id_istanza_corso = $result;
//            $chatroom_ha['id_chat_owner']= $userObj->id_user; The owner will be set in the tutor assignment process
            $chatroom_ha['chat_title'] = translateFN('chat '). $helpRequiredToken;
            $chatroom_ha['chat_topic'] = translateFN('Chat');
            $chatroom_ha['welcome_msg'] = translateFN('Benvenut* nella chat');
            $chatroom_ha['max_users']= 99;
            $chatroom_ha['start_time']= $data_inizio_previsto;
            $chatroom_ha['end_time']= $data_fine;
            $chatroom_ha['id_course_instance']= $id_instance;

            // add chatroom_ha to the database
            $chatroom = Chatroom::add_chatroomFN($chatroom_ha);
            if ((AMA_DataHandler::isError($chatroom)) && ($chatroom->code != AMA_ERR_UNIQUE_KEY)){
                $message = urlencode(translateFN("Errore nella creazione della chat:"). ' '. $chatroom->code);
                $errorObj = new ADA_Error($res_inst_add,$message,NULL,NULL,NULL,$error_page.'?message='.$message);
            }
      /* ****************************
       * ChatRoom creation ended
       */
        

          $MailText4User = AskServiceModuleHtmlLib::getFeedbackTextPlain($dataAr);
          /*
           * 
          $admtypeAr = array(AMA_TYPE_SWITCHER);
          //$admList = $common_dh->get_users_by_type($admtypeAr);
          $admList = $provider_dh-> get_users_by_type($admtypeAr);

          if (!AMA_DataHandler::isError($admList)){
                        $adm_uname = $admList[0]['username'];
          } else {
                        $adm_uname = ""; // ??? FIXME: serve un superadmin nel file di config?
          }
           * 
           */

        $subject = PORTAL_NAME . ': '.translateFN('Richiesta di servizio') .' '. $dataAr['service_name'];
        $titolo = AskService::addMessageToken($helpRequiredToken, $subject);
        
        $username = $userObj->getUserName();
        $recipientsAr =  array($adm_uname);
        $message_ha = array();
        $message_ha['titolo'] = $titolo;
        $message_ha['testo'] = $question;
        $message_ha['destinatari'] = $recipientsAr;
        $message_ha['data_ora'] = "now";
        $message_ha['tipo'] = ADA_MSG_SIMPLE;
        $message_ha['mittente'] = $username;

        // delegate sending to the message handler
        $res = $mh->send_message($message_ha);

        if (AMA_DataHandler::isError($res)){
          // $errObj = new WISP_Error($res,translateFN('Impossibile spedire il messaggio'),
          //NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
        }
        
        /*
         * Reminder message to the switcher
         */        
        
        $dataSwitcherText= array (
            'question' => $question,
            'name'=> $adm_name,
            'surname'=>$adm_surname,
            'service_name'=> $service_name,
            'asking_name' => $name,
            'asking_surname' => $surname,
            'asking_username' => $username
       );
                        
        $dataSwitcherText = AskServiceModuleHtmlLib::getToSwitcherTextHtml($dataSwitcherText);
        $ReminderSubject = PORTAL_NAME . ': '.$name. ' '. $surname . ' ' . translateFN('ha richiesto aiuto per') .' '. $dataAr['service_name'];
        $message_ha = array();
        $message_ha['titolo'] = $ReminderSubject;
        $message_ha['testo'] = $MailText4User; // $dataSwitcherText->getHtml();
        $message_ha['destinatari'] = $recipientsAr;
        $message_ha['data_ora'] = "now";
        $message_ha['tipo'] = ADA_MSG_MAIL;
        $message_ha['mittente'] = $username;

        // delegate sending to the message handler
        $res = $mh->send_message($message_ha);

        if (AMA_DataHandler::isError($res)){
          // $errObj = new WISP_Error($res,translateFN('Impossibile spedire il messaggio'),
          //NULL,NULL,NULL,$error_page.'?err_msg='.urlencode(translateFN('Impossibile spedire il messaggio')));
        }
        unset ($mh);
        

        
        
      break;
    case 'default':
	/**
	 * giorgio 13/ago/2013
	 * if it's not a multiprovider environment, must load only published course
	 * of the only selected single provider stored in GLOBALS.
	 * else make the default function call
	 */
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
                $data = new AskServiceForm($serviceToSubscribeAr,$user_provider_id);
            }
            else {
                $data = new CText(translateFN('Non è possibile chiedere aiuto'));
            }
        } else {
            $data = new CText(translateFN('Non è possibile chiedere aiuto'));
        }
        
        

        break;
}

/* display user help services */
$ServiceDiv = CDOMElement::create('div','id:servicesRequired');
         
$courseInstances = array();
$serviceProviders = $userObj->getTesters();

$courseInstances = array();
if (count($serviceProviders) == 1) {
    $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($serviceProviders[0]));
    $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
} else {
    foreach ($serviceProviders as $Provider) {
        $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
        $courseInstances = $provider_dh->get_course_instances_for_this_student($userObj->getId());
        $courseInstances = array_merge($courseInstances, $courseInstances_provider);
    }
}
        
if(!AMA_DataHandler::isError($courseInstances) && (!empty($courseInstances))){
    $Ada_Services_Help=false;
    foreach($courseInstances as $instance){
        $courseId = $instance['id_corso'];
        /* service level */
        $clause = ' st.id_corso ='.$courseId;
        $ServiceInfo = $common_dh->get_services(null,$clause);
        $serviceLevel=$ServiceInfo[0][2];
        
        if($serviceLevel==ADA_SERVICE_HELP){
            
            $Ada_Services_Help=true;
            $nodeId = $courseId . '_0';
            $courseInstanceId = $instance['id_istanza_corso'];
            $subscription_status = $instance['status'];
            $started = ($instance['data_inizio'] > 0 && $instance['data_inizio'] < time()) ? translateFN('Si') : translateFN('No');
            $start_date = ($instance['data inizio'] > 0) ? $instance['data_inizio'] : $instance['data_inizio_previsto'];
            $isEnded = ($instance['data_fine'] > 0 && $instance['data_fine'] < time()) ? true : false;
            $isStarted = ($instance['data_inizio'] > 0 && $instance['data_inizio'] <= time()) ? true : false;
            $service = CDOMElement::create('div');
            $service->setAttribute('class', 'single_service');
            $access_link = BaseHtmlLib::link("#", translateFN('Attendi che ti contatti un consulente...'));
            if ($subscription_status != ADA_STATUS_SUBSCRIBED && $subscription_status != ADA_STATUS_VISITOR && $subscription_status!= ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) {
                    $access_link = BaseHtmlLib::link("#",translateFN('Attendi che ti contatti un consulente...'));
            } elseif ($isStarted && !$isEnded){
                $tutorAssignedAR = $dh->course_instance_tutor_info_get($courseInstanceId,1);
                if (!AMA_DataHandler::isError($tutorAssignedAR) && count($tutorAssignedAR) > 0) {
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
            }
            elseif ($isEnded) {
                $access_link = BaseHtmlLib::link("#",
                        translateFN('Servizio terminato'));
            }

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
        if (is_object($access_link)) $service->addChild($access_link);
        $ServiceDiv->addChild(new CText('<h3>'.translateFN('Aiuto per Consulenza').'</h3>'));

        if (is_object($service)) $ServiceDiv->addChild($service);
        if (is_object($divNews)) $ServiceDiv->addChild($divNews);
        }
       

    }
    if(!$Ada_Services_Help){
        $access_link = BaseHtmlLib::link("#",
                        translateFN('Nessuna Consulenza trovata'));
        $ServiceDiv->addChild(new CText('<h3>'.translateFN('Aiuto per Consulenza').'</h3>'));
        $service = CDOMElement::create('div');
        $service->setAttribute('class', 'single_service');
        $service->addChild($access_link);
        $ServiceDiv->addChild($service);
    }
}


$title = translateFN('Chiedi aiuto');
$help = translateFN('Da questa pagina puoi chiedere aiuto ai professionisti di') . ' '. PORTAL_NAME;
$homeUser = $userObj->getHomePage();
$imgAvatar = $userObj->getAvatar();
$avatar = CDOMElement::create('img','src:'.$imgAvatar);
$avatar->setAttribute('class', 'img_user_avatar');

$link_to_home = BaseHtmlLib::link($homeUser, translateFN('Home'));
$status = translateFN('registrato');
$content_dataAr = array(
    'course_title' => $title,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'message' => $message,
    'help' => $help,
    'data' => $data->getHtml(),
    'user_avatar'=>$avatar->getHtml(),
    'home' => $link_to_home->getHtml(),
    'bloccoUnoTitolo'=>'<h2>'.translateFN('Consulenze richieste').'</h2>'
);
if(is_object($ServiceDiv)){
    $content_dataAr['bloccoUnoContenuto']=$ServiceDiv->getHtml();
}
$layout_dataAr['JS_filename'] = array(
    JQUERY,
    JQUERY_UI,
    JQUERY_NO_CONFLICT,
    
);
$layout_dataAr['CSS_filename'] = array (
    JQUERY_UI_CSS,
);
$optionsAr['onload_func'] =  "initDoc();";

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr,NULL,$optionsAr);
