<?php
/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
header("Content-type: application/x-javascript");
/**
 * Module config file
 */

if (MODULES_LEX) {
	echo 'var MODULES_LEX=true;';
	echo 'var MODULES_LEX_HTTP=\''. MODULES_LEX_HTTP .'\';';	
} else {
	echo 'var MODULES_LEX=false;';
}


if (defined('HOLIS_SEARCH_FILTER')) {
    echo 'var HOLIS_SEARCH_FILTER='. HOLIS_SEARCH_FILTER .';';
}
if (defined('HOLIS_SEARCH_CONCEPT')) {
    echo 'var HOLIS_SEARCH_CONCEPT='. HOLIS_SEARCH_CONCEPT .';';
}
if (defined('HOLIS_SEARCH_EUROVOC_CATEGORY')) {
    echo 'var HOLIS_SEARCH_EUROVOC_CATEGORY='. HOLIS_SEARCH_EUROVOC_CATEGORY .';';
}
if (defined('HOLIS_SEARCH_TEXT')) {
    echo 'var HOLIS_SEARCH_TEXT='. HOLIS_SEARCH_TEXT .';';
}
