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

function sendToBrowser ($message) {

	$style = '';
	$color = 'lime';
	
	if (strpos($message, '...')!==false) $style = 'width:auto; float: left; margin-right: 1em;';	
	if (strpos($message, '[')!==false) $color='yellow';
	if (strpos($message, '**')!==false) $color='red';
	
	echo '<pre style=\'color:'.$color.'; margin:0; font-family:monospace; '.$style.'\'>';
	echo $message;
	echo '</pre>';

	ob_flush();
	flush();
}

?>
