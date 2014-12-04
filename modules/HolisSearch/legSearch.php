<?php
/**
 * HOLISSEARCH MODULE.
 *
 * @package        HolisSearch module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           HolisSearch
 * @version		   0.1
 */

/**
 * This files implements the same functionality as index.php but
 * forces the search to abrogated assets only.
 * 
 * It's done this way to hide the $forceAbrogated parameters that
 * should have been otherwise passed in the GET request. 
 */

$forceAbrogated = true;
$noTypology = true;

include_once 'index.php';
