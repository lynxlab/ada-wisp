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
	/**
	 * constants for all possible actions a user can do in the lex module
	 */
	define ('IMPORT_EUROVOC', 1);
	define ('IMPORT_JEX', 2);
	define ('EDIT_SOURCE', 3);
	
	/**
	 * constants for asset state
	 */
	define ('MODULES_LEX_ASSET_STATE_UNVERIFIED', 0);
	define ('MODULES_LEX_ASSET_STATE_VERIFIED', 1);
	
	/**
	 * constant for module log dir
	 */
	define ('MODULES_LEX_LOGDIR' , ROOT_DIR.'/log/lex/');

	/**
	 * array of what action a user can do while in the lex module
	 * here you specify which actions a user can do and the order
	 * they appear in the UI tabs
	 */
	$canDO[AMA_TYPE_SWITCHER] = array( IMPORT_JEX, IMPORT_EUROVOC );
	$canDO[AMA_TYPE_AUTHOR]   = array( EDIT_ASSET );
	$canDO[AMA_TYPE_TUTOR]    = array();
	$canDO[AMA_TYPE_STUDENT]  = array();

	require_once MODULES_LEX_PATH.'/include/AMALexDataHandler.inc.php';
?>
