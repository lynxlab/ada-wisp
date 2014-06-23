<?php
/**
 * datePicker jQuery plugin dynamic translation file for ADA.
 *
 *
 *
 * PHP version >= 5.0
 *
 * @package		
 * @author		Giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version		0.1
 */
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
header("Content-type: application/x-javascript");
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../../../../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * $_SESSION was destroyed, so we do not need to clear data in session.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_ADMIN);
/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

echo'
(function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define([ "../datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}(function( datepicker ) {

datepicker.regional = {
	closeText: "'.translateFN('Chiudi').'",
	prevText: "&#x3C;'.translateFN('Prec').'",
	nextText: "'.translateFN('Succ').'&#x3E;",
	currentText: "'.translateFN('Oggi').'",
	monthNames: ["'.translateFN('Gennaio').'","'.translateFN('Febbraio').
				'","'.translateFN('Marzo').'","'.translateFN('Aprile').
				'","'.translateFN('Maggio').'","'.translateFN('Giugno').
				'","'.translateFN('Luglio').'","'.translateFN('Agosto').
				'","'.translateFN('Settembre').'","'.translateFN('Ottobre').
				'","'.translateFN('Novembre').'","'.translateFN('Dicembre').'"],
	monthNamesShort: ["'.translateFN('Gen').'","'.translateFN('Feb').
					 '","'.translateFN('Mar').'","'.translateFN('Apr').'","'.translateFN('Mag').
					 '","'.translateFN('Giu').'","'.translateFN('Lug').'","'.translateFN('Ago').
					 '","'.translateFN('Set').'","'.translateFN('Ott').'","'.translateFN('Nov').
					 '","'.translateFN('Dic').'"],
	dayNames: ["'.translateFN('Domenica').'","'.translateFN('Lunedì').'","'.translateFN('Martedì').
			'","'.translateFN('Mercoledì').'","'.translateFN('Giovedì').'","'.translateFN('Venerdì').'","'.translateFN('Sabato').'"],
	dayNamesShort: ["'.translateFN('Dom').'","'.translateFN('Lun').'","'.translateFN('Mar').
					 '","'.translateFN('Mer').'","'.translateFN('Gio').'","'.translateFN('Ven').'","'.translateFN('Sab').'"],
	dayNamesMin: ["'.translateFN('Do').'","'.translateFN('Lu').'","'.translateFN('Ma').
	           '","'.translateFN('Me').'","'.translateFN('Gi').'","'.translateFN('Ve').'","'.translateFN('Sa').'"],
	weekHeader: "Sm",
	dateFormat: "'.translateFN('dd/mm/yy').'",
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ""};
datepicker.setDefaults(datepicker.regional);

return datepicker.regional;

}));';
