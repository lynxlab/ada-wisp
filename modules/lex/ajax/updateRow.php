<?php
/**
 * LEX MODULE.
 *
 * @package        lex module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           lex
 * @version		   0.1
 */
ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';
require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';
require_once MODULES_LEX_PATH. '/include/management/sourceTypologyManagement.inc.php';


$dh = AMALexDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

$retArray = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	$oldValue = trim($oldValue);
	
	// validate ID
	$id = DataValidator::is_uinteger($id);
	// validate table name
	$table = DataValidator::validate_not_empty_string(trim($table));
	// validate column name
	$columnName = DataValidator::validate_not_empty_string(trim($columnName));
	
	if (($id===false) || ($id<=0)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Riga non valida"));
	else if ($columnName===false) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Colonna non valida"));
	else if ($table===false) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Tabella non valida"));
	else {		
		// validate value
		switch ($columnName) {
			case 'titolo':			
			case 'label':
			case 'url':
				$value = DataValidator::validate_not_empty_string($value);
				if ($value!==false) {
					$retValue = $value;
				}
				break;
			case 'data_pubblicazione':
			case 'data_inserimento':
			case 'data_modifica':
				$value = DataValidator::validate_date_format($value);
				if ($value!==false) {
					$retValue = $value;
					$value = dt2tsFN($value);
				}
				break;
			case 'tipologia':
			case 'categoria':
			case 'classe':				
				if ($value==='null') $value = null;
				
				// nothing to be saved if this condition is true
				if (urldecode($value)===$oldValue || 
					($oldValue=='' && is_null($value))) die(json_encode($retArray));
				
				// these 3 vars are coming from the POST request
				if (!isset($typology)) $typology = null;
				if (!isset($category)) $category = null;
				$class = null;
				
				// set proper values
				if ($columnName=='tipologia') {
					$typology = $value;
				} else if ($columnName=='categoria') {
					$category = $value;
				} else if ($columnName=='classe') {
					$class = $value;
				}
				
				// get the triple id to be saved
				$tripleID = sourceTypologyManagement::getIDFromTriple(urldecode($typology),
																   is_null($category) ? null :urldecode($category),
																   is_null($class) ? null : urldecode($class));
				if ($tripleID<=0) $value = false;
				else {
					$columnName = $dh::$PREFIX.'tipologie_fonti_id';
					// get triple description
					$triple = sourceTypologyManagement::getTripleFromID($tripleID);					
					$retValue = array ( 'value'=>$value, 'tipologia'=>$triple['descrizione'], 'categoria'=>$triple['categoria'], 'classe'=>$triple['classe']);
					$value = $tripleID;
				}
				break;
			case 'stato':
				// set the oldvalue
				$oldValue = is_numeric($oldValue) ? intval($oldValue) : false;
				if ($oldValue===false) $oldValue='';
				
				$value = is_numeric($value) ? intval($value) : false;
				if ($value!==false && $columnName=='stato') {
					/**
                     * if returned value is an array, it will update the
                     * matching html table column with the returned value
					 */
					$columnName = $dh::$PREFIX.'stati_id';
					$retValue = array ('value'=>$value, 'data_verifica'=>($value==MODULES_LEX_ASSET_STATE_VERIFIED ? ts2dFN() : null));
				} else {
					$retValue = $value;
				}
				break;
			default:
				break;
		}
		
		if ($value===false) {
			$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Valore vuoto o formato non valido"), "value"=>$oldValue );
		} else { 
			$result = $dh->updateModuleLexRow ($table, $columnName, $value, $id);
			
			if (!AMA_DB::isError($result)) {		
				$retArray = array ("status"=>"OK", "msg"=>translateFN("Riga Modificata"), "value"=>$retValue);
			} else
				$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nel salvataggio").' '.$result->errorMessage(), "value"=>$oldValue);
	    }
	}
} else {
	$retArray = array ("status"=>"ERROR", "msg"=>translateFN("Errore nella trasmissione dei dati"), "value"=>$oldValue);
}

if (empty($retArray)) $retArray = array("status"=>"ERROR", "msg"=>translateFN("Errore sconosciuto"), "value"=>$oldValue);

echo json_encode($retArray);
?>