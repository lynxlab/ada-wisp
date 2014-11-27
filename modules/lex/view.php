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
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR,AMA_TYPE_STUDENT);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout'),
		AMA_TYPE_AUTHOR => array('layout'),
		AMA_TYPE_TUTOR => array('layout'),
		AMA_TYPE_STUDENT => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_LEX_PATH .'/config/config.inc.php';

$self = whoami();

/**
 * TODO: Add your own code here
 */
require_once MODULES_LEX_PATH . '/include/management/lexManagement.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' &&
(isset($assetID) || isset($sourceID)) ) {
	
	$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
	$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));
	
	$languageId = getLanguageCode();
	$htmlObj = CDOMElement::create('div','id:assetDetailsContainer');
	$title = translateFN('Visualizzazione Dettaglio');
	
	/**
	 * prepare the assetsArray that contains the ids of the
	 * assets to be loaded, the view.js will take care of
	 * loading and displaying everything with ajax calls
	 */
	if (isset($assetID) && intval($assetID)) {
		// user has requested to view an asset
		$assetsArray = array (intval($assetID));
		$isSource = false;
	} else if (isset($sourceID) && intval($sourceID)>0) {
		$sourceTitleAr = $dh->get_sources(array('titolo'),true,AMALexDataHandler::$PREFIX.'fonti_id='.intval($sourceID));
		if (!AMA_DB::isError($sourceTitleAr) && is_array($sourceTitleAr) && count($sourceTitleAr)==1) {
			$sourceTitle = reset($sourceTitleAr)['titolo'];
			if (strlen($sourceTitle)>0) {
				$titleH2 = CDOMElement::create('h2','class:sourceTitle');
				$titleH2->addChild(new CText($sourceTitle));
				$htmlObj->addChild($titleH2);
			}
		}
		// user has requested to view a source
		$assetsArray = $dh->get_source_assetids(intval($sourceID),'`label` ASC');
		$isSource = true;
	} else {
		$htmlObj = CDOMElement::create('div');
		$title = '';
	}
} else {
	$htmlObj = CDOMElement::create('div');
	$title = '';
}

$data = array ('title'=>$title, 'htmlObj'=>$htmlObj);

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'status' => $status,
		'title' =>  $data['title'],
		'data'  =>  $data['htmlObj']->getHtml()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT,
);

$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS
);

$avatar = CDOMElement::create('img','class:img_user_avatar,src:'.$userObj->getAvatar());
$content_dataAr['user_avatar'] = $avatar->getHtml();
$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();

if (is_array($assetsArray) && count($assetsArray)>0) {
	$optionsAr['onload_func'] = 'initDoc('.json_encode($assetsArray).','.($isSource ? 'true' : 'false').');';
} else $optionsAr = null;

if(isset($assetID)) {
	$menuOptions['assetID']=$assetID;
} else $menuOptions = null;
 
ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr,$menuOptions);
?>
