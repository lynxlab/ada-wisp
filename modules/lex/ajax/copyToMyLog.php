<?php
/**
 * LEX MODULE.
 *
 * @package        lex module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @author         maurizio graffio mazzoneschi <graffio@lynxlab.com>         
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
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout','user'),
		AMA_TYPE_AUTHOR => array('layout','user'),
		AMA_TYPE_TUTOR => array('layout','user'),
		AMA_TYPE_STUDENT => array('layout','user')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
#require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';
#require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';

$retArray = array ('status'=>true);
$user_dir = '/upload_file/uploaded_files/';
$date = today_dateFN()." ".today_timeFN()."\n<BR />";
$log_extension = ".htm";	

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($asset) && ($asset)!= '') {
    
    $userUploadPath = $root_dir . $user_dir .  $sess_id_user;
    if (!is_dir($userUploadPath)) {
        if (mkdir($userUploadPath) == FALSE) {
            $retArray['status'] = false;
            $retArray['msg'] = translateFN('Errore nella creazione della cartella').': '. $userUploadPath;        
        }
    }
    if ($retArray['status']) {
        $logfile = $root_dir . $user_dir .  $sess_id_user. '/'.'log'.$sess_id_user.$log_extension;
        if (!$fileOpened = fopen($logfile, 'a')) {
             $retArray['status'] = false;
             $retArray['msg'] = translateFN('Errore nell\'apertura del file').': '. $logfile;
        } elseif (fwrite($fileOpened, $asset) === FALSE) {
             $retArray['status'] = false;
            $retArray['msg'] = translateFN('Errore nella scrittura del file').': '. $logfile;
        } else {
            $retArray['status'] = true;
            $retArray['msg'] = translateFN('Copia nel repository riuscita');
        }
        $res = fclose($fileOpened);            
        
    }
}
echo json_encode($retArray);
