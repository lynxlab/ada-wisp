<?php
/**
 * downloadEurovoc.php
 *
 * @package        lex
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           downloadEurovoc
 * @version		   0.1
 */

/**
 * Base config file
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array ('node', 'layout', 'course', 'user' );

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array ( AMA_TYPE_SWITCHER );

/**
 * Get needed objects
 */
$neededObjAr = array (
		AMA_TYPE_SWITCHER => array ( 'layout' ),
	);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==='GET' &&
    isset($filename) && strlen(trim($filename))>0) {

    $filename = trim($filename);
    
    $realFile = ADA_UPLOAD_PATH . $_SESSION['sess_userObj']->getId() . 
    			DIRECTORY_SEPARATOR .'eurovoc' . DIRECTORY_SEPARATOR . basename(trim($filename));
    
	// http headers for zip downloads
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"".$filename."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($realFile));
	ob_end_flush();
	@readfile($realFile);
	/**
	 * PLS NOTE: Looks like the file will be unlinked
	 * AFTER it's been served by the sever!!!
	 */
	unlink ($realFile);
} else {
	die ('error');
}

