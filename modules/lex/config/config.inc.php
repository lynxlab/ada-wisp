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
	define ('IMPEXPORT_EUROVOC', 1);
	define ('IMPORT_JEX',        2);
	define ('EDIT_SOURCE',       3); // edit is view only if case of user
	define ('ZOOM_SOURCE',       4);
	define ('DELETE_SOURCE',     5);
	define ('VIEW_SOURCE',       6);
	define ('SEARCH_SOURCE',     7);
	define ('EDIT_EUROVOC',      8);
	
	
	/**
	 * constants for asset state
	 */
	/**
	 * initial asset state
	 * if set causes verification date to be set to null
	 */
	define ('MODULES_LEX_ASSET_STATE_UNVERIFIED', 1);
	/**
	 * verified asset state
	 * if set causes verification date to be set to now
	 */
	define ('MODULES_LEX_ASSET_STATE_VERIFIED', 2);
	
	/**
	 * default associated term weight
	 */
	define ('DEFAULT_WEIGHT', 0.5);
	
	/**
	 * constant for module log dir
	 */
	define ('MODULES_LEX_LOGDIR' , ROOT_DIR.'/log/lex/');

	/**
	 * session var name of the uploaded file
	 */
	define ('UPLOAD_SESSION_VAR','lexFile');

	/**
	 * export zip file name
	 */
	define ('EXPORT_FILENAME', 'holis_eurovoc.zip');

	/**
	 * eurovoc version to use
	 */
	define ('EUROVOC_VERSION',4.40);

	/**
	 * array of what action a user can do while in the lex module
	 * here you specify which actions a user can do and the order
	 * they appear in the UI tabs
	 */
	$GLOBALS['canDO'][AMA_TYPE_SWITCHER] = array( VIEW_SOURCE, ZOOM_SOURCE, SEARCH_SOURCE, DELETE_SOURCE, EDIT_EUROVOC, IMPORT_JEX, IMPEXPORT_EUROVOC );
	$GLOBALS['canDO'][AMA_TYPE_AUTHOR]   = array( EDIT_SOURCE, ZOOM_SOURCE, SEARCH_SOURCE );
	$GLOBALS['canDO'][AMA_TYPE_TUTOR]    = array( VIEW_SOURCE, ZOOM_SOURCE, SEARCH_SOURCE );
	$GLOBALS['canDO'][AMA_TYPE_STUDENT]  = array( VIEW_SOURCE, ZOOM_SOURCE, SEARCH_SOURCE );
	
	/**
	 * array of actions that need a tab in the UI
	 */
	$GLOBALS['tabNeeded'] = array (VIEW_SOURCE, EDIT_SOURCE, SEARCH_SOURCE, IMPORT_JEX, IMPEXPORT_EUROVOC, EDIT_EUROVOC);

	require_once MODULES_LEX_PATH.'/include/AMALexDataHandler.inc.php';
?>
