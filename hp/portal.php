<?php
/**
 * Sespius Portal
 *
 * 
 *
 * PHP version >= 5.0
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2014,  Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

 /**
  *  Root dir relative path
  */
  define('HP_ROOT_DIR','/home/giorgio/workspaces/ada/ada-wisp/hp/');
  define('HP_HTTP_ROOT_DIR','http://www.localwisp.com/hp/');
	$default_target_page = 'main';
	$target_page_name = (isset($_GET['p'])) ? $_GET['p'] : null;
	$target_page = $target_page_name.".html";
	
	if (($target_page==null) OR (!file_exists(HP_ROOT_DIR.$target_page))){
		$target_page_name = $default_target_page;
		$target_page = $target_page_name.".html";
		}
	$title = $target_page_name;
	
	$css_file = HP_HTTP_ROOT_DIR."sespius_files/hp_style.css";
//	$js_file_1 =  HP_HTTP_ROOT_DIR."sespius_files/ga.js";
	$js_file_2 =  HP_HTTP_ROOT_DIR."sespius_files/js_f7992b518fe17255c16b5583b95af540.js";
	
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r
<html class=\"js\" xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"it\" dir=\"ltr\" lang=\"it\">\r
<head>\r
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\r
<meta name=\"description\" content=\"SESPIUS\" />\r
<meta name=\"keywords\" content=\"normative,EUROVOC,leggi, sentenze,codici\" />\r";

	echo "<title>$title</title>\r";
	echo "<link type=\"text/css\" rel=\"stylesheet\" media=\"all\" href=\"$css_file\">\r";
//	echo "<script src=\"$js_file_1\" async=\"\" type=\"text/javascript\"></script>\r\n";
	echo "<script src=\"$js_file_2\" async=\"\" type=\"text/javascript\"></script>\r";
	echo "</head>\r
<body>\r";
	
	include (HP_ROOT_DIR.$target_page);
	
	echo "</body>\r
</html>";
