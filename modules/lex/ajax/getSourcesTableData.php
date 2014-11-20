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
ini_set('display_errors', '0'); error_reporting(E_ALL);
/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array();
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout','user'),
		AMA_TYPE_AUTHOR => array('layout','user'),
		AMA_TYPE_TUTOR => array('layout','user'),
		AMA_TYPE_STUDENT => array('layout','user')
);

/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';
require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
	
	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
	
	$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));
	
	$output = $dh->getDataForDataTable (
			array ( $dh::$PREFIX.'fonti_id',
					'numero',
					'titolo',
					'data_pubblicazione',
 					array (
 							'fieldName'=>'tipologia',
 							'columnName'=>'descrizione',
 							'primaryKey'=>$dh::$PREFIX.'tipologie_fonti_id',
 							'tableName'=>$dh::$PREFIX.'tipologie_fonti'),
					array (
							'fieldName'=>'categoria',
							'columnName'=>'categoria',
							'primaryKey'=>$dh::$PREFIX.'tipologie_fonti_id',
							'tableName'=>$dh::$PREFIX.'tipologie_fonti'),
					array (
							'fieldName'=>'classe',
							'columnName'=>'classe',
							'primaryKey'=>$dh::$PREFIX.'tipologie_fonti_id',
							'tableName'=>$dh::$PREFIX.'tipologie_fonti')
			        ),
					$dh::$PREFIX.'fonti_id',
					$dh::$PREFIX.'fonti');
	
	foreach ($output['aaData'] as $i=>$elem) {
		// generate actions link for each row
		$links = array();
		$linksHtml = "";
		$id = intval(str_replace($dh::$PREFIX.'fonti:', '', $elem['DT_RowId']));
		
		for ($j=0;$j<3;$j++) {
		// set the type, title and link
			switch ($j)
			{
				case 0:
					$type = 'zoom';
					$title = translateFN('Clicca per i dettagli della fonte');
					$link = 'self.document.location.href=\'index.php?op='.$type.'&id='.$id.'\';';
					break;
				case 1:
					$type = 'linkSource'; 
					$title = translateFN('Clicca per il testo completo della fonte');
					$link = 'self.document.location.href=\'view.php?op=source&sourceID='.$id.'\';';
					break;
				case 2:
					if (in_array(DELETE_SOURCE, $canDO[$userObj->getType()])) {
						$type = 'delete';
						$title = translateFN ('Clicca per cancellare la fonte');
						$link = 'deleteSource ($j(this), '.$id.' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
					}
					break;
			}
			// generate a li item for each action
			if (isset($type)) {
				
				$links[$j] = CDOMElement::create('li','class:liactions');
				 
				$linkshref = CDOMElement::create('button');
				$linkshref->setAttribute('onclick','javascript:'.$link);
				$linkshref->setAttribute('class', $type.'Button tooltip');
				$linkshref->setAttribute('title',$title);
				$links[$j]->addChild ($linkshref);
				// unset for next iteration
				unset ($type);
			}
		}
		
		// generate an ul to hold the actions buttons
		if (!empty($links)) {
			$linksul = CDOMElement::create('ul','class:ulactions');
			foreach ($links as $link) $linksul->addChild ($link);
			$linksHtml = $linksul->getHtml();
		} else $linksHtml = '';
		
		array_push($output['aaData'][$i], $linksHtml);
	}
	
	$output['sColumns'] .= ',azioni';
	
	echo json_encode($output);
	
}
