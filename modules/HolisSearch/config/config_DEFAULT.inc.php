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

/**
 * See http://serendipity.lynxlab.com:81/static/doc/server.search-class.html
 * for possible parameters:
 *
 * minprobratio : (float) minimum ratio among the returned categories probabilities and the highest one [default 0.1]
 * maxnumcats : (int) maximum number of categories returned [default 10]
 * maxnumweights : (int) maximum number of weights retrieved for each query feature [default 100]
 */
define ('EUROVOC_SEARCH_PARAMS', 'minprobratio=0.1&maxnumcats=5');

define ('FULLTEXT_SEARCHTYPE_DISPLAY', 'FT');
define ('ID_SEARCHTYPE_DISPLAY', 'ID');

define ('MODULES_LEX_PROVIDER_POINTER','client0');

$GLOBALS['searchable_service_type'] = array(
    ADA_SERVICE_LEG,
    ADA_SERVICE_TEMI_RISOLTI
);

define ('HOLIS_SEARCH_FILTER',1);
define ('HOLIS_SEARCH_CONCEPT',2);
define ('HOLIS_SEARCH_EUROVOC_CATEGORY',3);
define ('HOLIS_SEARCH_TEXT',4);