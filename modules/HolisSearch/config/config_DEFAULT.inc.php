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

require_once MODULES_HOLISSEARCH_PATH.'/include/AMAHolisSearchDataHandler.inc.php';

define ('MULTIWORDNET_SYNONYMS_URL','http://serendipity.lynxlab.com:81/services/DEMO2014/taxonomy/MWNL/synonyms/');
define ('EUROVOC_SEARCH_SERVICE_URL','http://serendipity.lynxlab.com:81/services/DEMO2014/search/HOLIS/');

define ('FULLTEXT_SEARCHTYPE_DISPLAY', 'FT');
define ('ID_SEARCHTYPE_DISPLAY', 'ID');

define ('MODULES_LEX_PROVIDER_POINTER','client0');

$GLOBALS['searchable_service_type'] = array(
    ADA_SERVICE_LEG,
    ADA_SERVICE_TEMI_RISOLTI
);
?>
