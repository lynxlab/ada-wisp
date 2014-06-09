<?php
/**
 * doImportEurovoc.php
 *
 * @package        lex
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           doImportEurovoc
 * @version		   0.1
 */

/**
 * Base config file
 * This is generated from ADA Eclipse Developer Plugin, pls check if file path is ok!
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * This is generated from ADA Eclipse Developer Plugin, use it as an example!
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
require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==='POST') {
	$eurovoc = new eurovocManagement();
	$eurovoc->run();
}

die();
