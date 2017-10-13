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
	$istanza_ha['service_level']= (int)$serviceAr[3];
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

        /**
         * @author giorgio 13/oct/2017
         *
         * On WISP/UNIMC only:
         * add an hidden field to tell to closeMeAndReloadParent function to not reload the page
         */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        	$data->addChild(CDOMElement::create('hidden','name:dontReload,id:dontReload,value:1'));
        }

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

        if (!AMA_DB::isError($res_presub)) {
        	/**
        	 * UNIMC code:
        	 * if user has a preassigned tutor, do the tutor/student assignment
        	 */
        	$tutorID = $provider_dh->get_tutor_preassigned_to_student_for_course($id_user, $id_course);
        	if (!AMA_DB::isError($tutorID) && $tutorID>0) {
        		$postURL = HTTP_ROOT_DIR .'/switcher/assign_practitioner.php';
        		$postDATA = array (
        				'id_tutor_old' => "no",
        				'id_course_instance' => $id_instance,
        				'id_course' => $id_course,
        				'id_student' => $id_user,
        				'id_tutor_new' => $tutorID,
        				'comeFromAskService'=>1
        		);
        		$postString = '';
        		//create name value pairs seperated by &
        		foreach($postDATA as $k=>$v) $postString .= $k.'='.$v.'&';
        		rtrim($postString, '&');

        		$sessionName = session_name();
        		$strCookie = $sessionName.'=' . $_COOKIE[$sessionName] . '; path=/';

        		// make a POST request to assign_practitioner using cURL
        		$ch = curl_init();
        		curl_setopt($ch, CURLOPT_URL, $postURL);
        		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        		curl_setopt($ch, CURLOPT_COOKIE, $strCookie );
        		curl_setopt($ch, CURLOPT_POST, count($postString));
        		curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        		// session is no longer needed here, must close it before making the cURL exec
        		session_write_close();
        		$output=curl_exec($ch);
        		curl_close($ch);
        		// cURL call will  return 'OK' if all went ok
        		if ($output!=='OK') {
        			$errorObj = new ADA_Error($res_inst_add,$output,NULL,NULL,NULL,$error_page.'?message='.$output);
        		} else {
        			// restart the session, just in case...
        			session_start();
        		}
        	} // if (!AMA_DB::isError($tutorID) && intval($tutorID)>0)
        } // else if (!AMA_DB::isError($res_presub))



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
            	if (in_array((int)$service['livello'], array(ADA_SERVICE_HELP, ADA_SERVICE_IN_ITINERE))) {

                       $serviceId = $service['id_servizio'];
                       $serviceName = $service['nome'];
                       $coursesAr = $common_dh->get_courses_for_service($serviceId);
                       if (!AMA_DataHandler::isError($coursesAr)) {
                            $currentTesterId = 0;
                            $currentTester = '';
                            $provider_dh = null;
                            foreach($coursesAr as $courseData) {
                                if (defined ('PUBLIC_COURSE_ID_FOR_NEWS') && PUBLIC_COURSE_ID_FOR_NEWS!=$courseData['id_corso']) {
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
    'home' => $link_to_home->getHtml()
);

/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr, $content_dataAr);
