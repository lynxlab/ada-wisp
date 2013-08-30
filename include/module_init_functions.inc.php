<?php
/**
 * Functions used by module_init.inc.php
 *
 * PHP version >= 5.0
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		module_init_functions
 * @version		0.1
 */

/**
 *
 * @return unknown_type
 */
function session_controlFN($neededObjAr=array(), $allowedUsersAr=array(), $trackPageToNavigationHistory = true) {
  //ADALogger::log('session control FN');

  if(!session_start()) {
    /*
     * As of PHP 5.3.0 if session fails to star for some reason,
     * FALSE is returned.
     */
      ADALogger::log('session failed to start');
  }
  
  /**
   * giorgio 11/ago/2013
   * if it's not multiprovider and we're asking for index page,
   * sets the selected provider by detecting it from the filename that's executing
   */
   if (!MULTIPROVIDER) { 
	$httpParts = explode ('/',HTTP_ROOT_DIR);
	$httpDir = $httpParts[count($httpParts)-1];

	$parts = explode ('/',$_SERVER['REQUEST_URI']);
	$potentialProvider = $parts[1];
  	/*
  	 * if it's a direct request (e.g. /info.php and NOT provider/info.php)
  	 * all previously set data must be unset, so that the selected provider
  	 * (if any) becomes unselected. 
  	 */
	//  	if (preg_match('/\/\S+\/+/',$_SERVER['REQUEST_URI'])==0)	
	// unset session and global provider var only if user has asked for the home page
	if ($httpDir == $potentialProvider) //  || is_dir(ROOT_DIR.'/'.$potentialProvider) || is_file(ROOT_DIR.'/'.$potentialProvider)
  	{
  		// must unset
  		unset ($_SESSION['sess_user_provider']);
  		unset ($GLOBALS['user_provider']); 
  		// remove the cookie
  		unset ($_COOKIE['ada_provider']);
  		setcookie ('ada_provider', false, time() - 4800,'/');
  	}
	else 
	{
		// preg_match('/\/(\w*)\/?.*/i', $_SERVER['REQUEST_URI'],$matches);

	  	// if (!isset($_SESSION['sess_user_provider']) && isset ($matches[1]) && 
	  	//     !empty($matches[1]) && is_dir(ROOT_DIR.'/clients/'.$matches[1]))
		if (!empty ($parts) && isset ($potentialProvider) &&
		     is_dir(ROOT_DIR.'/clients/'.$potentialProvider))
	  	{
	  		$_SESSION['sess_user_provider'] = $potentialProvider;
	  		// other session vars per provider may go here...  		  		
	  	}
	  	$GLOBALS['user_provider'] = $_SESSION['sess_user_provider'];
	  	// if it's not set, set a cookie that shall expire in one year
	  	if (!isset($_COOKIE['ada_provider']) && isset($GLOBALS['user_provider'])) setcookie('ada_provider',$GLOBALS['user_provider'],+time()+ 86400 *365 ,'/');
	}
// 	var_dump ($_SESSION['sess_user_provider']);
// 	var_dump ($GLOBALS['user_provider']);
  } // end if !MULTIPROVIDER
  
  /*
   * Navigation history
   */
  require_once ROOT_DIR.'/include/navigation_history.inc.php';

  $debug_backtrace = debug_backtrace();
  $level = sizeof($debug_backtrace) - 1;

  if ($trackPageToNavigationHistory) {
	$caller_file     = $debug_backtrace[$level]['file'];
	if ( !isset($_SESSION['sess_navigation_history']) ) {
	  $navigation_history = new NavigationHistory(NAVIGATION_HISTORY_SIZE);
	  $navigation_history->addItem($caller_file);
	  $_SESSION['sess_navigation_history'] = $navigation_history;
	}
	else {
	  $navigation_history = $_SESSION['sess_navigation_history'];
	  $navigation_history->addItem($caller_file);
	  $_SESSION['sess_navigation_history'] = $navigation_history;
	}
  }

  $GLOBALS['sess_id'] = session_id();

  $parm_errorHa = parameter_controlFN($neededObjAr,$allowedUsersAr);
  //var_dump($parm_errorHa);


  if($parm_errorHa['session']) {
  }

  if($parm_errorHa['user']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_USER_REQUIRED_BUT_NOT_FOUND,
      ADA_ERROR_SEVERITY_FATAL,
      'index.php'
    );
  }

  /*
   * URL a cui redirezionare l'utente in caso di errore su corso, istanza_corso, nodo
   */
  $sess_userObj = $_SESSION['sess_userObj'];
  if($sess_userObj instanceof ADAGenericUser) {
    $redirectTo = $sess_userObj->getHomePage();
  }
  else {
  	/**
	 * giorgio 21/ago/2013
	 * if it's not a multiprovider environment, try to redirect the user
	 * to the selected provider that should be stored in the browser's cookie
  	 */
  	if (!MULTIPROVIDER && isset($_COOKIE['ada_provider'])) $redirectTo = $_COOKIE['ada_provider']. '/';
  	else $redirectTo = '';
  	
    $redirectTo .= 'index.php';
  }

  if($parm_errorHa['course']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_SERVICE_REQUIRED_BUT_NOT_FOUND,
      ADA_ERROR_SEVERITY_FATAL,
      $redirectTo
    );
  }

  if($parm_errorHa['course_instance']) {
    // FIXME: passare messaggio di errore
    // TODO: forse il controllo su ADAGuest in questo if puo' essere rimosso, 
    // dato che non settiamo $parm_errorHa['coutrse_instance'] nel caso in cui 
    // l'utente e' sul tester pubblico (ADAGuest e' solo sul tester pubblico) 
    if (!($sess_userObj instanceof ADAAuthor) &&
   !($sess_userObj instanceof  ADAGuest) // ??? steve 6/9
    ) {
      $errObj = new ADA_Error(
        NULL,
        NULL,
        NULL,
        ADA_ERROR_ID_CINST_REQUIRED_BUT_NOT_FOUND,
        ADA_ERROR_SEVERITY_FATAL,
        $redirectTo
      );
    }
  }

  if($parm_errorHa['node']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_NODE_REQUIRED_BUT_NOT_FOUND,
      ADA_ERROR_SEVERITY_FATAL,
      $redirectTo
    );
  }

  if($parm_errorHa['guest_user_not_allowed']) {
    // FIXME: passare messaggio di errore
    $errObj = new ADA_Error(
      NULL,
      NULL,
      NULL,
      ADA_ERROR_ID_CINST_NOT_PUBLIC,
      ADA_ERROR_SEVERITY_FATAL,
      $redirectTo
    );
  }

// FIXME: controllare su livello utente?
//  if($parm_errorHa['user_level']) {
//  }

  $GLOBALS['sess_id_user']            = $_SESSION['sess_id_user'];
  $GLOBALS['sess_id_user_type']       = $_SESSION['sess_id_user_type'];
  $GLOBALS['sess_user_level']         = $_SESSION['sess_user_level'];
  $GLOBALS['sess_id_course']          = $_SESSION['sess_id_course'];
  $GLOBALS['sess_id_course_instance'] = $_SESSION['sess_id_course_instance'];
  $GLOBALS['sess_id_node']            = $_SESSION['sess_id_node'];
  $GLOBALS['sess_selected_tester']    = $_SESSION['sess_selected_tester'];
  $GLOBALS['sess_user_language']      = $_SESSION['sess_user_language'];
}

function parameter_controlFN($neededObjAr=array(), $allowedUsersAr=array()) {

  $invalid_session         = FALSE;
  $invalid_user            = FALSE;
  $invalid_node            = FALSE;
  $invalid_course          = FALSE;
  $invalid_course_instance = FALSE;
  $invalid_user_level      = FALSE;
  $guest_user_not_allowed  = FALSE;

  /*
   * ADA common data handler
   */
  $common_dh = $GLOBALS['common_dh'];
  if(!$common_dh instanceof AMA_Common_DataHandler) {
    $common_dh = AMA_Common_DataHandler::instance();
    $GLOBALS['common_dh'] = $common_dh;
  }

  /*
   * User object: always load a user
   */
  $sess_id_user = isset($_SESSION['sess_id_user']) ? (int)$_SESSION['sess_id_user'] : 0;
  $sess_userObj = read_user($sess_id_user);
  if (ADA_Error::isError($sess_userObj)) {
    $sess_userObj->handleError();
  }
  $_SESSION['sess_id_user'] = $sess_id_user;
  if($sess_userObj instanceof ADAGenericUser) {
    $_SESSION['sess_userObj'] = $sess_userObj;
    
    /*
     * Check if this user is allowed to access the current module
     */
    if(!in_array($sess_userObj->getType(), $allowedUsersAr)) {
      header('Location: '.$sess_userObj->getHomePage());
      exit();
    }
  }
  else {
    unset($_SESSION['sess_userObj']);
    $invalid_user = TRUE;
  }

  $id_profile = $sess_userObj->getType();

  /*
   * Get needed object for this user from $neededObjAr 
   */
  if(is_array($neededObjAr) && is_array($neededObjAr[$id_profile])) {
    $thisUserNeededObjAr = $neededObjAr[$id_profile];
  }
  else {
    $thisUserNeededObjAr = array();
  }

  /*
   * 
   * 'default_tester' AL MOMENTO VIENE RICHIESTO SOLO DA USER.php
   * QUI ABBIAMO NECESSITA' DI CANCELLARE LA VARIABILE DI SESSIONE
   * sess_id_course.
   * Gia' che ci siamo facciamo unset anche di sess_id_node 
   * e di sess_id_course_instance
   * 
   * Tester selection: 
   * 
   * se ho richiesto la connessione al database del tester di default, 
   * controllo che il tipo di utente sia ADAUser (al momento e' l'unico ad
   * avere questa necessita').
   * 
   * se non ho richiesto la connessione al tester di default, allora verifico
   * se l'utente e' di tipo ADAUser, e ottengo la connessione al database
   * tester appropriato. 
   */
  if(in_array('default_tester',$thisUserNeededObjAr) && $id_profile == AMA_TYPE_STUDENT) {
    $_SESSION['sess_selected_tester'] = NULL;

    unset($_SESSION['sess_id_course']);
    unset($_SESSION['sess_id_course_instance']);
    unset($_SESSION['sess_id_node']);

  }
  else if($id_profile == AMA_TYPE_STUDENT){
    $id_course      = DataValidator::is_uinteger($_REQUEST['id_course']/*$GLOBALS['id_course']*/);
    $sess_id_course = DataValidator::is_uinteger($_SESSION['sess_id_course']);
    if($id_course !== FALSE && $id_course !== $sess_id_course) {

      $tester_infoAr = $common_dh->get_tester_info_from_id_course($id_course);
      if (AMA_Common_DataHandler::isError($tester_infoAr)) {
        $selected_tester = NULL;
      }
      else {
        $selected_tester = $tester_infoAr['puntatore'];
      } 
      $_SESSION['sess_selected_tester'] = $selected_tester;  
    }
  }
  
  /* 
   * ADA tester data handler
   * Data validation on $sess_selected_tester is performed by MultiPort::getDSN()
   */
  /**
   * giorgio 12/ago/2013
   * set selected tester if it's not a multiprovider environment
   */
  if (!MULTIPROVIDER && isset($GLOBALS['user_provider']))
  {
  	$sess_selected_tester = $GLOBALS['user_provider'];
  }
  else
  {
  	$sess_selected_tester = $_SESSION['sess_selected_tester'];
  }
  
  //$dh = AMA_DataHandler::instance(MultiPort::getDSN($sess_selected_tester));
  
  $sess_selected_tester_dsn = MultiPort::getDSN($sess_selected_tester);
  $_SESSION['sess_selected_tester_dsn'] = $sess_selected_tester_dsn;

  $dh = new AMA_DataHandler($sess_selected_tester_dsn);
  $GLOBALS['dh'] = $dh;

  if(empty($GLOBALS['sess_id'])) {
    $invalid_session = TRUE;
  }

  /*
   * Node object
   */
  // TODO: portare in sessione $nodeObj?
  if(in_array('node',$thisUserNeededObjAr)) {
    $id_node      = DataValidator::validate_node_id($_REQUEST['id_node']/*$GLOBALS['id_node']*/); 
    $sess_id_node = DataValidator::validate_node_id($_SESSION['sess_id_node']);
    
    if($id_node !== FALSE) {
      $nodeObj = read_node_from_DB($id_node);
      if (ADA_Error::isError($nodeObj)) {
        $nodeObj->handleError(); 
      }
      $_SESSION['sess_id_node'] = $id_node;
    }
    elseif($sess_id_node !== FALSE) {
      $nodeObj = read_node_from_DB($sess_id_node);
      if (ADA_Error::isError($nodeObj)) {
        $nodeObj->handleError(); 
      }
      $_SESSION['sess_id_node'] = $sess_id_node;
    }
    else {
      $invalid_node = TRUE;
    }
  }

  /*
   * Course object
   */
  if(in_array('course',$thisUserNeededObjAr)) {
    
    $id_course      = DataValidator::is_uinteger($_REQUEST['id_course']/*$GLOBALS['id_course']*/);
    $sess_id_course = DataValidator::is_uinteger($_SESSION['sess_id_course']);
    /* extracting the course id from node id, if given */
    if ($nodeObj instanceof Node){
      $node_id_courseAr = explode ('_',$nodeObj->id);
      $node_id_course = $node_id_courseAr[0];   
      $sess_courseObj = read_course($node_id_course);  
      
      if (ADA_Error::isError($sess_courseObj)) {
       if($sess_userObj instanceof ADAGuest) {
          /*
           * Maybe a user not logged in tried to access a non public content.
           */
          header('Location: '.$sess_userObj->getHomePage());
          exit();
        }
        $sess_courseObj->handleError();
      }
      else if ($sess_userObj instanceof ADAGuest  && $id_course!=PUBLIC_COURSE_ID_FOR_NEWS) {
      	header('Location: '.$sess_userObj->getHomePage());
      	exit();
      }
      
      $_SESSION['sess_courseObj'] = $sess_courseObj;
      $_SESSION['sess_id_course'] = $node_id_course;
    }    
    elseif($id_course !== FALSE) {
      $sess_courseObj = read_course($id_course);
      if (ADA_Error::isError($sess_courseObj)) {
        if($sess_userObj instanceof ADAGuest) {
          /*
           * Maybe a user not logged in tried to access a non public content.
           */
          header('Location: '.$sess_userObj->getHomePage());
          exit();
        }
        $sess_courseObj->handleError();
      }
      $_SESSION['sess_courseObj'] = $sess_courseObj;
      $_SESSION['sess_id_course'] = $id_course;
    }
    elseif($sess_id_course !== FALSE) {
      $sess_courseObj = read_course($sess_id_course);
      if (ADA_Error::isError($sess_courseObj)) {
        $sess_courseObj->handleError();
      }
      $_SESSION['sess_courseObj'] = $sess_courseObj;
      $_SESSION['sess_id_course'] = $sess_id_course;
    }
    else {
      unset($_SESSION['sess_courseObj']);
      $invalid_course = TRUE;
    }
  }
  else {
    unset($_SESSION['sess_courseObj']);
  }

  /*
   * Course_instance object
   */
  if(in_array('course_instance',$thisUserNeededObjAr)) {
    /*
     * Se ci troviamo nel tester pubblico, allora non dobbiamo leggere un'istanza corso
     * dato che non ce ne sono.
     */
//      var_dump(array($sess_id_course_instance,$id_course_instance,MultiPort::isUserBrowsingThePublicTester()));
    if(!MultiPort::isUserBrowsingThePublicTester() && $id_course!=PUBLIC_COURSE_ID_FOR_NEWS) {
      $id_course_instance      = DataValidator::is_uinteger($_REQUEST['id_course_instance']/*$GLOBALS['id_course_instance']*/); // FIXME: qui ci va $_REQUEST['id_course_instance']
      $sess_id_course_instance = DataValidator::is_uinteger($_SESSION['sess_id_course_instance']);      
      if($id_course_instance !== FALSE) {
        $course_instanceObj = read_course_instance_from_DB($id_course_instance);
        if (ADA_Error::isError($course_instanceObj)) {
          $course_instanceObj->handleError();
        }
        $UserType = $sess_userObj->getType();
          switch ($sess_userObj->getType()) {
              case AMA_TYPE_STUDENT:
                  $studentLevel = $dh->_get_student_level($sess_id_user, $id_course_instance);
                  if (AMA_DataHandler::isError($studentLevel)) {
                      $invalid_course_instance = TRUE;
                  }
                  break;
              case AMA_TYPE_TUTOR:
                  $tutorsInstance = $dh->course_instance_tutor_get($id_course_instance,$number=2);
                  if (AMA_DataHandler::isError($tutorsInstance)) {
                      $invalid_course_instance = TRUE;
                  } elseif (!in_array($sess_id_user, $tutorsInstance)) {
                      $invalid_course_instance = TRUE;
                  }
                  break;
              default:    
//                  $invalid_course_instance = TRUE;
                  break;
          }
          if (!$invalid_course_instance) {
                $_SESSION['sess_id_course_instance'] = $id_course_instance;
                $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
          }
      } //end id_course_istance passed as parameter in the URL and valid
      elseif($sess_id_course_instance !== FALSE) {
        $instanceIdRequired = array();
        if ($nodeObj instanceof Node) { // required a node
              $instanceIdRequired[] = $nodeObj->instance; 
              if ($instanceIdRequired[0] == 0) { // the node is NOT a note 
                    $field_list_ar = array();
                    $courseIdRequired = $sess_courseObj->id;
                    $InstanceIdList = $dh->course_instance_get_list($field_list_ar, $courseIdRequired);
                    if (AMA_DataHandler::isError($InstanceIdList) || count($InstanceIdList) == 0 ) {
                              $invalid_course_instance = TRUE;
                    }
                    $instanceIdRequired = array();
                    foreach ($InstanceIdList as $InstanceId) {
                        array_push($instanceIdRequired, $InstanceId[0]); 
                    }
              } // end if NOTE
          } // end if node         
          elseif ($sess_courseObj instanceof Course) {
                    $courseIdRequired = $sess_courseObj->id;
                    $InstanceIdList = $dh->course_instance_get_list($field_list_ar, $courseIdRequired);
                    if (AMA_DataHandler::isError($InstanceIdList) || count($InstanceIdList) == 0 ) {
                              $invalid_course_instance = TRUE;
                    }
                    $instanceIdRequired = array();
                    foreach ($InstanceIdList as $InstanceId) {
                        array_push($instanceIdRequired, $InstanceId[0]); 
                    }
          }
//          var_dump($instanceIdRequired,$sess_id_course_instance);
          $UserType = $sess_userObj->getType();
          switch ($UserType) {
              case AMA_TYPE_STUDENT:
              case AMA_TYPE_TUTOR:
                  if (!in_array($sess_id_course_instance,$instanceIdRequired)) {
                      $invalid_course_instance = TRUE;
                  }
                  break;
              case AMA_TYPE_SWITCHER:
              case AMA_TYPE_AUTHOR:
              default:    
                    break;
          } //end switch UserType
          $course_instanceObj = read_course_instance_from_DB($sess_id_course_instance);
          if (ADA_Error::isError($course_instanceObj)) {
              $course_instanceObj->handleError();
          }
          $_SESSION['sess_id_course_instance'] = $sess_id_course_instance;
      }
      else {
       $invalid_course_instance = TRUE;      
      }
    } //end isUserBrowsingThePublicTester
  } // end if in_array
  /*
   * Check if current user is a ADAGuest user and that he/she has requested
   * a public course instance.
   */

//
//  if(in_array('user', $neededObjAr[$user_type]) && in_array('course_instance', $neededObjAr[$user_type])) {
//    if(!$invalid_user && $sess_userObj instanceof ADAGuest) {
//      if ($invalid_course_instance || $course_instanceObj->status != ADA_COURSEINSTANCE_STATUS_PUBLIC) {
//        $guest_user_not_allowed = TRUE;
//      }
//    }
//  }

  // TODO: controllo livello utente
  /*
   * controllare che sia settato $sess_user_level e che il valore sia tra 0 e
   * ADA_MAX_USER_LEVEL
   */

  $parm_errorHa = array(
    'session'                => $invalid_session,
  	'user'                   => $invalid_user,
  	'user_level'             => $invalid_user_level,
    'course'                 => $invalid_course,
    'course_instance'         => $invalid_course_instance,
  	'node'                   => $invalid_node,
    'guest_user_not_allowed' => $guest_user_not_allowed
  );
  return $parm_errorHa;
}

/**
 *
 * @param $data
 * @return unknown_type
 */
function clear_dataFN($variableToClearAr=array()) {
  //ADALogger::log('clear data FN');

  $sess_id                 = $_SESSION['sess_id'];
  $sess_id_user            = $_SESSION['sess_id_user'];
  $sess_id_course          = $_SESSION['sess_id_course'];
  $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
  $sess_id_node            = $_SESSION['sess_id_node'];

  /**
   * Node variables
   */
  $node_parent = $GLOBALS['node_parent'];
  $node_map    = $GLOBALS['node_map'];
  $node_index  = $GLOBALS['node_index'];
  $node_path   = $GLOBALS['node_path'];
  $node_title  = $GLOBALS['node_title'];

  /**
   * User variables
   */
  $id_profile     = $GLOBALS['id_profile'];
  $user_name      = $GLOBALS['user_name'];
  $user_history   = $GLOBALS['user_history'];
  $user_bookmarks = $GLOBALS['user_bookmarks'];
  $user_level     = $GLOBALS['user_level'];

  /**
   * Course variables
   */
  $id_course    = $GLOBALS['id_course'];
  $course_title = $GLOBALS['course_title'];

  /**
   * Layout variables
   */
  $layout_CSS = $GLOBALS['layout_CSS'];
  $layout_template = $GLOBALS['layout_template'];

  if (in_array( 'node', $variableToClearAr)) {
    $GLOBALS['node_title']  = '';
    $GLOBALS['node_path']   = '' ;
    $GLOBALS['node_index']  = '';
    $GLOBALS['node_map']    = '';
    $GLOBALS['node_parent'] = $sess_id_course.'_0';
  }

  if (in_array('course', $variableToClearAr)) {
    $GLOBALS['id_course']    = ADA_DEFAULT_COURSE;
    $GLOBALS['course_title'] = '';
    $GLOBALS['id_toc']       = $sess_id_course."_".ADA_DEFAULT_NODE;
  }

  if (in_array('user', $variableToClearAr)) {
    $GLOBALS['id_profile']     = 1;
    $GLOBALS['user_name']      =  translateFN('Ospite');
    $GLOBALS['user_history']   = '';
    $GLOBALS['user_bookmarks'] = '';
    $GLOBALS['user_level']     = 1;
  }

  if (in_array('layout', $variableToClearAr)) {
    $GLOBALS['layout_CSS']      = 'css/default/default.css';
    $GLOBALS['layout_template'] = 'templates/default/default';
  }
}
?>