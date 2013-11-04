<?php
/**
 * index.
 *
 *
 *
 * PHP version >= 5.0
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version		0.1
 */

/**
 * Destroy session
 */
session_start();
/*
 * Redirect the user to the module he/she used to login
 */
if (isset($_SESSION['ada_access_from'])) {
  $access_from =  $_SESSION['ada_access_from'];
  /*
   * Accessed from kiosk
   * ADA_KIOSK_ACCESS = 1
   */
  if($access_from == 1) {
    header('Location: kiosk.php');
    exit();
  }
  /*
   * Accessed from the reserved area
   * ADA_RESERVED_ACCESS = 3
   */
  if($access_from == 3) {
    header('Location: reserved/index.php');
    exit();
  }
}

/**
 * save session user provider before destroying the session
 * and after redirect to provider's own index.php
 */

if (isset($_SESSION['sess_user_provider'])) $saved_provider = $_SESSION['sess_user_provider'];

session_unset();
session_destroy();

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * $_SESSION was destroyed, so we do not need to clear data in session.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN);
/**
 * Performs basic controls before entering this module
 */

require_once ROOT_DIR.'/include/module_init.inc.php';
$self = whoami(); // index
include_once 'include/'.$self.'_functions.inc.php';


// non serve più...
// require_once ROOT_DIR.'/include/aut/login.inc.php';
//

$lang_get = $_GET['lang'];

/**
 * sets language if it is not multiprovider
 * if commented, then language is handled by ranslator::negotiateLoginPageLanguage
 * that will check if the browser language is supported by ADA and set it accordingly
 */

// if (!MULTIPROVIDER && defined('PROVIDER_LANGUAGE')) $lang_get = PROVIDER_LANGUAGE;


/**
 * Negotiate login page language
 */
Translator::loadSupportedLanguagesInSession();
$supported_languages = Translator::getSupportedLanguages();
$login_page_language_code = Translator::negotiateLoginPageLanguage($lang_get);
$_SESSION['sess_user_language'] = $login_page_language_code;

/**
 *
 */
$_SESSION['ada_remote_address'] = $_SERVER['REMOTE_ADDR'];

/**
 * giorgio 12/ago/2013
 * if it isn't multiprovider, loads proper files into clients directories
 */
if (!MULTIPROVIDER && isset ($GLOBALS['user_provider'])) $files_dir = $root_dir.'/clients/'.$GLOBALS['user_provider'];
else $files_dir = $root_dir;

/*
 * Load news file
 */
  $newsfile = 'news_'.$login_page_language_code.'.txt';
  $infofile = 'info_'.$login_page_language_code.'.txt';
  $helpfile = 'help_'.$login_page_language_code.'.txt';

/*
   $infomsg = '';
   $newsmsg = '';
   $hlpmsg = '';
*/

if ($newsmsg == ''){
   $newsfile = $files_dir.'/docs/news/'.$newsfile; //  txt files in ada browsing directory
   if ($fid = @fopen($newsfile,'r')){
      while (!feof($fid))
        $newsmsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $newsmsg = '<p>'.translateFN("File news non trovato").'</p>';
    }
}

if ($hlpmsg == ''){
   $helpfile = $files_dir.'/docs/help/'.$helpfile;  //  txt files in ada browsing directory
   if ($fid = @fopen($helpfile,'r')){
      while (!feof($fid))
        $hlpmsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $hlpmsg = '<p>'.translateFN("File help non trovato").'</p>';
    }
}

if ($infomsg == ''){
   $infofile = $files_dir.'/docs/info/'.$infofile;  //  txt files in ada browsing directory
   if ($fid = @fopen($infofile,'r')){
      while (!feof($fid))
        $infomsg .= fread($fid,4096);
      fclose($fid);
    } else {
       $infomsg = '<p>'.translateFN("File info non trovato").'</p>';
    }
}


$login_error_message = '';
/**
 * Perform login
 */
if(isset($p_login)) {
  $username = DataValidator::validate_username($p_username);
  $password = DataValidator::validate_password($p_password, $p_password);

  if($username !== FALSE && $password !== FALSE) {
    //User has correctly inserted un & pw

    $userObj = MultiPort::loginUser($username, $password);
    
    if ((is_object($userObj)) && ($userObj instanceof ADALoggableUser)){
      $status = $userObj->getStatus();
	  if ($status == ADA_STATUS_REGISTERED)
      {
      	$user_default_tester = $userObj->getDefaultTester();
      	
      	if (!MULTIPROVIDER && $userObj->getType()!=AMA_TYPE_ADMIN) 
      	{
      		if ($user_default_tester!=$GLOBALS['user_provider'])
      		{
      			// if the user is trying to login in a provider
      			// that is not his/her own,
      			// redirect to his/her own provider home page      			
      			$redirectURL = preg_replace("/(http[s]?:\/\/)(\w+)[.]{1}(\w+)/", "$1".$user_default_tester.".$3", $userObj->getHomePage());
      			header('Location:'.$redirectURL);
		  		exit();
      		}      		       		
      	}
      	
        // user is a ADAuser with status set to 0 OR
        // user is admin, author or switcher whose status is by default = 0
    	$_SESSION['sess_user_language'] = $p_selected_language;
		$_SESSION['sess_id_user'] = $userObj->getId();
		$GLOBALS['sess_id_user']  = $userObj->getId();
		$_SESSION['sess_id_user_type'] = $userObj->getType();
		$GLOBALS['sess_id_user_type']  = $userObj->getType();
	    $_SESSION['sess_userObj'] = $userObj;

		if($user_default_tester !== NULL) {
					$_SESSION ['sess_selected_tester'] = $user_default_tester;
					// sets var for non multiprovider environment
					$GLOBALS ['user_provider'] = $user_default_tester;		    
		  }
		  $redirectURL = $userObj->getHomePage();      	
		  header('Location:'.$redirectURL);
		  exit();
		}
		else {
            //  Utente non loggato perché stato <> ADA_STATUS_REGISTERED
	        $login_error_message = translateFN("Utente non abilitato");
	    }
      } else {
        // Utente non loggato perché coppia username password non corretta
		$login_error_message = translateFN("Username  e/o password non valide");
      }
  }
  else {
    // Utente non loggato perche' informazioni in username e password non valide
    // es. campi vuoti o contenenti caratteri non consentiti.
	$login_error_message = translateFN("Username  e/o password non valide");
  }
}

/**
 * Show login page
 */
$form_action = HTTP_ROOT_DIR ;
$form_action .= '/index.php';
$login = UserModuleHtmlLib::loginForm($form_action, $supported_languages,$login_page_language_code, $login_error_message);

//$login = UserModuleHtmlLib::loginForm($supported_languages,$login_page_language_code, $login_error_message);
 /**
 * giorgio 12/ago/2013
 * set up proper link path and tester for getting the news in a multiproivder environment
 */
  if (!MULTIPROVIDER)
  {
  	if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']))
  	{
		$testerName = $GLOBALS['user_provider'];
  	} else {
  		/**
  		 * overwrite $newsmsg with generated available providers listing
  		 */
  		$allTesters = $common_dh->get_all_testers (array('nome'));
  		$addHtml = false;

  		foreach ($allTesters as $aTester)
  		{  			
  			// skip testers having punatore like 'clientXXX'
  			if (!preg_match('/^(?:client)[0-9]{1,2}$/',$aTester['puntatore']) &&
  				is_dir (ROOT_DIR . '/clients/' .$aTester['puntatore'])) {
  				
  				if (!$addHtml) $providerListUL = CDOMElement::create('ol');
  				$addHtml = true;
  				$testerLink = CDOMElement::create('a','href:'.preg_replace("/(http[s]?:\/\/)(\w+)[.]{1}(\w+)/", "$1".$aTester['puntatore'].".$3", HTTP_ROOT_DIR));
  				$testerLink->addChild (new CText($aTester['nome']));

  				$providerListElement = CDOMElement::create('li');
  				$providerListElement->addChild ($testerLink);
  				$providerListUL->addChild ($providerListElement);
  			}
  		}
  		$newsmsg = $addHtml ? $providerListUL->getHtml() : translateFN ('Nessun fornitore di servizi &egrave; stato configurato');
  	}
  } else  {
  	$testers = $_SESSION['sess_userObj']->getTesters();
  	$testerName = $testers[0];
  } // end if (!MULTIPROVIDER)

  $forget_div  = CDOMElement::create('div');
  $forget_linkObj = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/browsing/forget.php?lan='.$_SESSION['sess_user_language']);
  $forget_linkObj->addChild(new CText(translateFN("Did you forget your password?")));
  $forget_link = $forget_linkObj->getHtml();
//  $status = translateFN('Explore the web site or register and ask for a practitioner');
  $status = "";

$message = CDOMElement::create('div');
if(isset($_GET['message'])) {
  $message->addChild(new CText($_GET['message']));
}

/*
 *  Load news from public course indicated in PUBLIC_COURSE_ID_FOR_NEWS
 */
if (isset($testerName))
{
	$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($testerName));
	// select nome or empty string (whoever is not null) as title to diplay for the news
	$newscontent = $tester_dh->find_course_nodes_list(
			array ( "COALESCE(if(nome='NULL' OR ISNULL(nome ),NULL, nome), '')", "testo" ) ,
			"1 ORDER BY data_creazione DESC LIMIT ".NEWS_COUNT,
			PUBLIC_COURSE_ID_FOR_NEWS);

	// watch out: $newscontent is NOT associative
	$bottomnewscontent = '';
	$maxLength = 600;
	if (!AMA_DB::isError($newscontent) && count($newscontent)>0)
	{
		foreach ( $newscontent as $num=>$aNews )
		{
			$aNewsDIV = CDOMElement::create('div','class:news,id:news-'.($num+1));
			$aNewsTitle = CDOMElement::create('a', 'class:newstitle,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
					PUBLIC_COURSE_ID_FOR_NEWS.'&id_node='.$aNews[0]);
			$aNewsTitle->addChild (new CText($aNews[1]));
			$aNewsDIV->addChild ($aNewsTitle);

			// @author giorgio 01/ott/2013
			// remove unwanted div ids: tabs
			// NOTE: slider MUST be removed BEFORE tabs because tabs can contain slider and not viceversa
			$removeIds = array ('slider','tabs');
			
			$html = new DOMDocument('1.0', 'UTF-8');
			$html->loadHTML(utf8_decode($aNews[2]));

			foreach ($removeIds as $removeId)
			{
				$removeElement = $html->getElementById($removeId);
				if (!is_null($removeElement)) $removeElement->parentNode->removeChild($removeElement);				
			}
			
			// output in newstext only the <body> of the generated html
			$newstext = '';
			foreach ($html->getElementsByTagName('body')->item(0)->childNodes as $child)
			{
				$newstext .= $html->saveXML($child);
			}
			// strip off html tags
			$newstext = strip_tags($newstext);
			// check if content is too long...
			if (strlen($newstext) > $maxLength)
			{
				// cut the content to the first $maxLength characters of words (the $ in the regexp does the trick)
				$newstext = preg_replace('/\s+?(\S+)?$/', '', substr($newstext, 0, $maxLength+1));
				$addContinueLink = true;
			}
			else $addContinueLink = false;

			$aNewsDIV->addChild (new CText("<p class='newscontent'>".$newstext.'</p>'));

			if ($addContinueLink)
			{
				$contLink = CDOMElement::create('a', 'class:continuelink,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
						PUBLIC_COURSE_ID_FOR_NEWS.'&id_node='.$aNews[0]);
				$contLink->addChild (new CText(translateFN('Continua...')));
				$aNewsDIV->addChild ($contLink);
			}
			$bottomnewscontent .= $aNewsDIV->getHtml();
		}
	}
}  else $bottomnewscontent = '';


$content_dataAr = array(
	'form' => $login->getHtml().$forget_link,
	'newsmsg' => $newsmsg,
	'helpmsg' => $hlpmsg,
        'infomsg' => $infomsg,
	'bottomnews' => $bottomnewscontent,
	'status' => $status,
	'message' => $message->getHtml()
);

/**
 * @author giorgio 26/set/2013
 * 
 * if you have some widget in the page and need to
 * pass some parameter to it, you can do it this way:
 * 
 * $layout_dataAr['widgets']['<template_field_name>'] = array ("<param_name>"=>"<param_value>");
 */

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
				JQUERY_NO_CONFLICT,
				ROOT_DIR . "/js/main/index.js"
		);
                $layout_dataAr['CSS_filename'] = array (
                    JQUERY_UI_CSS,
                    );
		$optionsAr['onload_func'] = 'initDoc();';
ARE::render($layout_dataAr, $content_dataAr, NULL, (isset($optionsAr) ? $optionsAr : NULL) );
?>