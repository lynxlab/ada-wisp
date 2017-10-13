<?php
/**
 * getCareer - gets a student UNIMC career with a call to the ESSE3 web service
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
$message = translateFN('Richiesta carriera non valida');

if (!defined('ESSE3_URL') || !defined('ESSE3_LOGIN') || !defined('ESSE3_PASSWD') ||
	strlen(ESSE3_URL)<=0 || strlen(ESSE3_LOGIN)<=0 || strlen(ESSE3_PASSWD)<=0) {
	$message = translateFN('ESSE3 non configurato');
	$confiOK = false;
} else $configOK = true;

if ($configOK && $_SERVER['REQUEST_METHOD']=='GET' && strlen(trim($_GET['cf']))>0) {
	$cf = trim($_GET['cf']);

	$languageMappings = array ('it'=>'ita', 'en'=>'eng');

	if (array_key_exists($_SESSION['sess_user_language'], $languageMappings)) {
		$selLang = $languageMappings[$_SESSION['sess_user_language']];
	} else $selLang = 'ita';

	$soapClient = new SoapClient(ESSE3_URL);
	$sid = null;
	$res = $soapClient->fn_dologin(ESSE3_LOGIN,ESSE3_PASSWD);
	$sid = $res['sid'];
	$res = $soapClient->fn_retrieve_xml_p ('GET_CV','COD_FISCALE='.$cf.';SESSIONID='.$sid);
	$soapClient->fn_dologout($sid);

	if (isset($res['xml']) && strlen($res['xml'])>0) {
		// check XML declared character encoding
		if (preg_match("/encoding=\"(.[^\"]*)\"/", $res['xml'], $output_array)) {
			$sourceEncoding = $output_array[1];
		} else $sourceEncoding = false;

		$xmlArr = simplexml_load_string($res['xml']);
	}
	else $xmlArr = null;

	if (isset($xmlArr->CARRIERE->CARRIERA->ESAMI->ESAME) &&
		is_a($xmlArr->CARRIERE->CARRIERA->ESAMI->ESAME, 'SimpleXMLElement')) {

		$tableData = array();

		$header = array (
				'COD_AD',
				'DES_AD',
				'VOTO',
				'GIUDIZIO',
				'PESO',
				'TIPO_SETT_COD',
				'TIPO_AF_DES',
				'AMB_DES',
				'TIPO_SETT_DES',
				'DATA_SUP_ESA' );

		/**
		 * cycle to have values for each header field.
		 */
		foreach ($xmlArr->CARRIERE->CARRIERA->ESAMI->ESAME as $esame) {
			$rowData = array();
			foreach ($header as $position=>$field) {
				if (isset($esame->$field)) {
					$data = $esame->$field;
					$children = $data->children();
					if (isset($data['type']) && $data['type']=='date') {
						$rowData[$position] = date(str_replace('%', '', ADA_DATE_FORMAT),strtotime($data));
					} else if (count($children)>0 && isset($data['type']) && $data['type']=='lang') {
						foreach ($children as $child) {
							if (isset($child['codice_lingua']) && $child['codice_lingua']==$selLang) {
								if ($sourceEncoding) {
									$rowData[$position] = trim(iconv(ADA_CHARSET, $sourceEncoding, $child));
								} else $rowData[$position] = trim($child);
								break;
							}
						}
					} else if (count($children)==0) {
						if ($sourceEncoding) {
							$rowData[$position] = trim(iconv(ADA_CHARSET, $sourceEncoding, $data));
						} else $rowData[$position] = trim($data);
						// put VOTO, BASE_VOTO and LODE_FLG together
						if ($data->getName()=='VOTO') {
							$rowData[$position] .= '/'.$esame->BASE_VOTO;
							if (isset($esame->LODE_FLG) && intval($esame->LODE_FLG)>0) {
								$rowData[$position] .= ' +L';
							}
						}
					} else $rowData[$position] = null; // don't know what to do
				} else $rowData[$position] = null; // $esame->field does not exist
			}
			$tableData[] = $rowData;
		}

		if (count($tableData)>0) {
			$message = BaseHtmlLib::tableElement('id:exams_table,class:'.ADA_SEMANTICUI_TABLECLASS,$header,$tableData)->getHtml();
			$result = true;
		} else $message = translateFN('Nessun dato tornato da ESSE3');

	} // end check if at least one esame exists and is SimpleXMLElement
	else $message = translateFN('Nessun dato tornato da ESSE3');
}
sleep(1); // just in case we've been too quick, the fadein/out could do a messe
die(json_encode(array('status'=>($result ? 1:0), 'msg'=>$message)));
