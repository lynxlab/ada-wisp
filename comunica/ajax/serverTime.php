<?php
/**
 * GET SERVER CURRENT TIME
 *
 * @package		comunica
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 * 
 * gets the server current time, for countdown syncronization
 * ajax called by read_event.js that has a countdown timer
 */
$now = new DateTime();
echo $now->format("M j, Y H:i:s O")."\n";
?>
