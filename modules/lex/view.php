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
isset($assetID) && intval($assetID)>0) {
	
$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));

$languageId = getLanguageCode();
$assetText = $dh->asset_get_text($assetID);

if (!AMA_DB::isError($assetText)) {

	$htmlObj = CDOMElement::create('div','class:assetDetail');
	if (strlen($assetText)>0) {
		$htmlObj->addChild (new CText('<p>'.$assetText.'</p>'));
	}

	$assetEurovocAr = $dh->get_asset_eurovoc ($assetID,$languageId);

	/**
	 * build an ordered list with associated eurovoc terms
	*/
	if (!is_null($assetEurovocAr)) {
		foreach ($assetEurovocAr as $count=>$assetEurovoc) {
			if (isset($assetEurovoc['libelle']) && strlen($assetEurovoc['libelle'])>0) {
				// make an UL and a section title element if needed
				if (!isset($ulElement)) $ulElement = CDOMElement::create('ol','class:assetWords');
					
				$liElement = CDOMElement::create('li','class:assetWord');
				$liElement->addChild(new CText($assetEurovoc['libelle']));
					
				if (isset($assetEurovoc['weight']) && strlen($assetEurovoc['weight'])>0) {
					$weightSpan = CDOMElement::create('span','class:assetWordWeight');
					$weightSpan->addChild(new CText(' ('.translateFN('peso: ').
							number_format($assetEurovoc['weight'],3).')'));
					$liElement->addChild($weightSpan);
				}
				$ulElement->addChild($liElement);
			}
		}
			
		/**
		 * if a list was built (i.e. there is at least one associated term)
		 * add it to the returned HTML
		 */
		if (isset($ulElement)) {
			$spanWordsTitle = CDOMElement::create('span','class:assetWordsTitle');
			$spanWordsTitle->addChild(new CText(translateFN('Termini Associati')));
			$htmlObj->addChild($spanWordsTitle);
			$htmlObj->addChild($ulElement);
		}
	}
		$assetHa = $dh->asset_get($assetID);
		if (!AMA_DB::isError($assetHa)) {
			$title = str_replace('_', ' ', $assetHa->label);
		} else $title = '';
}
	
} else {
 $htmlObj = CDOMElement::create('div');
 $title = '';
}
$data = array ('help'=>$title, 'title'=>'', 'htmlObj'=>$htmlObj);


/**
 * include proper jquery ui css file depending on wheter there's one
 * in the template_family css path or the default one
*/

$templateFamily = (isset($userObj->template_family) && strlen($userObj->template_family)>0) ? $userObj->template_family : ADA_TEMPLATE_FAMILY;

if (!is_dir(MODULES_LEX_PATH.'/layout/'.$templateFamily.'/css/jquery-ui'))
{
	$layout_dataAr['CSS_filename'] = array(
			JQUERY_UI_CSS
	);
}
else
{
	$layout_dataAr['CSS_filename'] = array(
			MODULES_LEX_PATH.'/layout/'.$templateFamily.'/css/jquery-ui/jquery-ui-1.10.3.custom.min.css'
	);
}

array_push($layout_dataAr['CSS_filename'], JQUERY_DATATABLE_CSS);
array_push($layout_dataAr['CSS_filename'], ROOT_DIR . '/js/include/jquery/pekeUpload/pekeUpload.css' );
array_push($layout_dataAr['CSS_filename'], MODULES_LEX_PATH.'/layout/'.$templateFamily.'/css/skin-vista/ui.fancytree.css' );
array_push($layout_dataAr['CSS_filename'], MODULES_LEX_PATH.'/layout/tooltips.css');

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'status' => $status,
		'help'  =>  $data['help'],
		'title' =>  $data['title'],
		'data'  =>  $data['htmlObj']->getHtml()
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		MODULES_LEX_PATH . '/js/jquery.jeditable.js',
		JQUERY_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		MODULES_LEX_PATH . '/js/jquery.selectric.min.js',
		MODULES_LEX_PATH . '/js/jquery.fancytree.js',
		MODULES_LEX_PATH . '/js/jquery.fancytree.childcounter.js',
		ROOT_DIR . '/js/include/jquery/ui/i18n/datepickerLang.php',
		JQUERY_NO_CONFLICT,
		ROOT_DIR . '/js/include/jquery/pekeUpload/pekeUpload.js'		
);

$maxFileSize = (int) (ADA_FILE_UPLOAD_MAX_FILESIZE / (1024*1024));
// $optionsAr['onload_func'] = 'initDoc('.$maxFileSize.','. $userObj->getId().','.intval($userObj->getType()!=AMA_TYPE_STUDENT).');';

$avatar = CDOMElement::create('img','class:img_user_avatar,src:'.$userObj->getAvatar());
$content_dataAr['user_avatar'] = $avatar->getHtml();
$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
