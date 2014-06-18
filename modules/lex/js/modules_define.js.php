<?php
/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
/**
 * Module config file
 */
require_once MODULES_LEX_PATH.'/config/config.inc.php';

if (defined('UPLOAD_SESSION_VAR')) {
	echo 'var UPLOAD_SESSION_VAR=\''.UPLOAD_SESSION_VAR.'\';';
}