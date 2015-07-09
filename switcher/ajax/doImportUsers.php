<?php
/**  
 * doImportUsers - Performs user import from CSV file
 * 
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/include/Forms/nationList.inc.php';

/**
 * UNIMC IMPORT ALGORITHM:
 * 
 * 1. The uploaded files may have lots of data, but MUST HAVE a column whose name
 * matches the UNIMC_IMPORT_USE_AS_PRIMARY_KEY
 * 
 * E.g. 
 * - UNIMC_IMPORT_USE_AS_PRIMARY_KEY is 'COD_FIS' (but could have been MATRICOLA or blablabla)
 * - uploaded file is:
 * MATRICOLA,COD_FIS,blablabla
 * xxxx,bcdfgh12a34h501x,ehmehmehm
 * 
 * If UNIMC_IMPORT_USE_AS_PRIMARY_KEY is undefined, the first field is used for its value at runtime
 * 
 * 2. Then the UNIMC API returns data of all students, one per row.
 * 
 * 3. Only data of students with matching UNIMC_IMPORT_USE_AS_PRIMARY_KEY will be imported
 */

define ('UNIMC_IMPORT_USE_AS_PRIMARY_KEY','COD_FIS');
$retVal = translateFN('Errore nell\'importazione');
$result=false;
// this is the final array that will be actually imported
$data = null;

if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['file']) && strlen(trim($_POST['file']))>0) {
 	$file = ADA_UPLOAD_PATH.trim($_POST['file']);
	
	if (is_file($file) && is_readable($file)) {
		$delimiter=',';
		$header = NULL;
		$filterArray = array();
		if (($handle = fopen($file, 'r')) !== false) {
			
			if (defined('UNIMC_IMPORT_USE_AS_PRIMARY_KEY')) {
				$fieldToUseAsFilter = UNIMC_IMPORT_USE_AS_PRIMARY_KEY;
			}
			
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
				if(!$header) {
					$header = $row;
					if (!isset($fieldToUseAsFilter)) $fieldToUseAsFilter = reset($header);
				}
				else {
					$combined = array_combine($header, $row);
					$filterArray[] = $combined[$fieldToUseAsFilter];
				}
			}
			fclose($handle);
		}
		unlink($file);
	}
	
	require_once ROOT_DIR. '/switcher/include/config_user_import.inc.php';
	if (defined('UNIMC_IMPORT_URL') && defined('UNIMC_IMPORT_USER') && defined ('UNIMC_IMPORT_PASSWD') &&
		strlen(UNIMC_IMPORT_URL)>0 && strlen(UNIMC_IMPORT_USER)>0 && strlen(UNIMC_IMPORT_PASSWD)>0) {
			
		$cookie_file = tempnam(ADA_UPLOAD_PATH, 'unimc-cookie');
		
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, UNIMC_IMPORT_URL );
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($c, CURLOPT_TIMEOUT,       60);
		curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($c, CURLOPT_USERPWD, UNIMC_IMPORT_USER.":".UNIMC_IMPORT_PASSWD);
		curl_setopt($c, CURLOPT_COOKIEJAR, $cookie_file);
		
		$response = curl_exec($c);
		$responseArr = json_decode($response, true);
		
		if ($response!== false && isset($responseArr['results']['studenti']) && is_array($responseArr['results']['studenti'])
				&& count($responseArr['results']['studenti'])>0) {
			$data = array_filter($responseArr['results']['studenti'],
					function($row) use ($filterArray, $fieldToUseAsFilter) {
						return in_array($row[$fieldToUseAsFilter], $filterArray);
					}
			);
		} else if ($response !== false) {
			$retVal = translateFN('Nessuno studente nella risposta JSON');
		} else {
			$retVal = curl_error($c);
		}
		
		curl_close($c);
		unlink($cookie_file);			
	}	
	
	/**
	 * Basic ADA user fields mapping to UNIMC structure
	 */
	$mainADAFieldsMap = array(
			'MATRICOLA' => 'matricola',
			'COGNOME' => 'cognome',
			'NOME' => 'nome',
			'SESSO' => 'sesso',
			'DATA_NASCITA' => 'birthdate',
			'NAZIONE_NASCITA_DESC' => 'birthcity',
			'COD_FIS' => 'codice_fiscale',
			'EMAIL_ATE' => 'email',
			'COMUNE_RESIDENZA_DESC' => 'citta',
			'PROVINCIA_RESIDENZA_SIGLA' => 'provincia',
			'REGIONE_RESIDENZA_DESC' => 'indirizzo',
			'NAZIONE_RESIDENZA_DESC' => 'nazione',
			'CELLULARE' => 'telefono'
	);
	
	/**
	 * $data must contain all the data to be imported
	 */
	if (is_array($data) && count($data)>0) {
		// rebase array keys
		$data = array_values($data);
		// get supported countries list, in lowered for case insensitive search
		$countries =array_map('strtolower', countriesList::getCountriesList($_SESSION['sess_user_language']));		
		$importCount=0;
		$updateCount=0;
		foreach ($data as $number=>$userData) {
			foreach ($userData as $key=>$userDetail) {
				if (strtolower($userDetail)==='null' || strtolower($userDetail)==='none') $userDetail = null;
				$userDetail = trim($userDetail);
				
				if (array_key_exists($key, $mainADAFieldsMap)) {
					if ($mainADAFieldsMap[$key]=='email') {
						$newuserData['username'] = substr($userDetail, 0, strpos($userDetail, '@'));
					}
					// convert field formats if needed
					if ($mainADAFieldsMap[$key]=='birthdate') {
						$newuserData[$mainADAFieldsMap[$key]] = date("d/m/Y", strtotime($userDetail));
					} else if ($mainADAFieldsMap[$key]=='nazione') {
						// array search will return the key corresponding to found value or null
						$newuserData[$mainADAFieldsMap[$key]] = array_search(strtolower($userDetail), $countries);
					} else {
						$newuserData[$mainADAFieldsMap[$key]] = $userDetail;
					}
					unset($data[$number][$key]);
				} else {
					// save as extra
					if ($key=='DATA_ISCR') {
						$extraData[$key] = date("d/m/Y", strtotime($userDetail));
					} else if ($key=='TASSE_IN_REGOLA_OGGI') {
						$extraData[$key] = ((intval($userDetail)>0) ? translateFN('Sì'): translateFN('No') );
					} else if ($key=='TIPO_TITOLO_STRA_DESC') {
						if (!is_null($userDetail)) $extraData['TIPO_TITOLO_DESC'] = $userDetail;
					} else if ($key=='TIPO_TITOLO_SUP_DESC') {
						if (!is_null($userDetail)) $extraData['TIPO_TITOLO_DESC'] = $userDetail;
					} else if ($key=='EMAIL') {
						$extraData['privateEmail'] = $userDetail;
					}
					else {
						$extraData[$key] = $userDetail;
					}
					unset($data[$number][$key]);
				}
			}
			// data that was on the cvs file and was not used to build the arrays
			// this is not used, but could be useful for future developements
			$leftData[$number] = $data[$number];
	
			// set not nullable db fields to empty
			$newuserData['cap'] = '';
			$newuserData['avatar'] = '';
			
			$ADAuser = MultiPort::findUserByUsername($newuserData['username']);
			if (is_object($ADAuser) && $ADAuser instanceof ADALoggableUser) {
				$newuserData['id'] = $ADAuser->getId();
				$ADAuser->fillWithArrayData($newuserData);
				$id_user = MultiPort::setUser($ADAuser,array(),true);
			} else {
				// this is a new user
				$newUserObj = new ADAUser($newuserData);
				$newUserObj->setType(AMA_TYPE_STUDENT);
				$newUserObj->setLayout('');
				$newUserObj->setStatus(ADA_STATUS_REGISTERED);
				$newUserObj->setPassword(sha1(time())); // force unguessable password				
				/**
				 * save the user in the appropriate provider
				 */
				if (!MULTIPROVIDER && isset ($GLOBALS['user_provider'])) {
					$regProvider = array ($GLOBALS['user_provider']);
				} else {
					$regProvider = array (ADA_PUBLIC_TESTER);
				}				
				$id_user = Multiport::addUser($newUserObj,$regProvider);				
			}
			
			if ($id_user < 0) {
				// abort import and return error message
				break;
			} else {
				// reload the user just to be double sure
				$editUserObj = MultiPort::findUserByUsername($newuserData['username']);
				// set extra data
				$editUserObj->setExtras($extraData);
				// save
				$result = MultiPort::setUser($editUserObj, array(), true, ADAUser::getExtraTableName());
				if (AMA_DB::isError($result)) {
					// abort import and return error message
					break;
				} else {
					if (isset($newUserObj)) { $importCount++; unset($newUserObj); }
					else $updateCount++;
				}
			}
		}
	}
	
	if (isset($importCount) && $importCount>0) $retArr[] = $importCount.' '.translateFN('studenti importati');
	if (isset($updateCount) && $updateCount>0) $retArr[] = $updateCount.' '.translateFN('studenti aggiornati');
	if (isset($retArr)) { $result=true; $retVal = implode('<br/>', $retArr); }
}

die(json_encode(array(
		'OK' => (isset($result) && $result===true) ? 1 :0,
		'message'=>$retVal
)));