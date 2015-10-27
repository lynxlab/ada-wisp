<?php
/**  
 * getSurvey - gets a student Questionnaire with a call to the ESSE3 web service
 * 
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2015, Lynx s.r.l.
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
$message = translateFN('Richiesta questionario non valida');

if (defined('SURVEY_API_URL') && defined('SURVEY_API_USER') && defined ('SURVEY_API_PASSWD') &&
	strlen(SURVEY_API_URL)>0 && strlen(SURVEY_API_USER)>0 && strlen(SURVEY_API_PASSWD)>0) {					
	if ($_SERVER['REQUEST_METHOD']=='GET' && strlen(trim($_GET['cf']))>0) {
		
		$cf = trim($_GET['cf']);
		
		$cookie_file = tempnam(ADA_UPLOAD_PATH, 'unimc-cookie');
		
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, SURVEY_API_URL .'?COD_FIS='.$cf);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($c, CURLOPT_TIMEOUT,       60);
		curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($c, CURLOPT_USERPWD, SURVEY_API_USER.":".SURVEY_API_PASSWD);
		curl_setopt($c, CURLOPT_COOKIEJAR, $cookie_file);
		
		$response = curl_exec($c);
		$responseArr = json_decode($response, true);
		if ($responseArr!==false && is_array($responseArr['results']['risposte']) && count($responseArr['results']['risposte'])>0) {
			$result = true;
			$aRow = reset($responseArr['results']['risposte']);
			$caption = $aRow['usr_ins_id'].' - '.$aRow['matricola'].' - '.$aRow['cod_fis'];
			$header = array_map('strtoupper',array_keys($aRow));			
			$message = BaseHtmlLib::tableElement('id:surveys_table',$header,$responseArr['results']['risposte'],null,$caption)->getHtml();
		}
	} else $message = translateFN('Codice fiscale non valido');	
} else $message = translateFN('Endpoint per questionario non configurato');


sleep(1); // just in case we've been too quick, the fadein/out could do a messe
die(json_encode(array('status'=>($result ? 1:0), 'msg'=>$message)));
