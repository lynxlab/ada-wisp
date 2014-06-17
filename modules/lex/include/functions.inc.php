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
 * recursiverly deletes a directory and all its content
 * 
 * @param string $dir the directory to delete
 */
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

/**
 * sends a string to the browser output buffer
 * 
 * @param string $message the message to output
 */
function sendToBrowser ($message) {

	$style = '';
	$color = 'lime';
	
	if (strpos($message, '...')!==false) $style = 'width:auto; float: left; margin-right: 1em;';	
	if (strpos($message, '[')!==false) $color='yellow';
	if (strpos($message, '**')!==false) $color='red';
	
	echo '<pre style=\'color:'.$color.'; margin:0; font-family:monospace; '.$style.'\'>';
	echo $message;
	echo '</pre>';
// 	echo '<script type="text/javascript">window.scrollTo(0,document.body.scrollHeight);</script>';

	ob_flush();
	flush();
}
