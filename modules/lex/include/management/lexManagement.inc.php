<?php
/**
 * Lex Management Class
 *
 * @package 	lex
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	    0.1
 */

/**
 * class for managing the Lex Module
 *
 * @author giorgio
 */

require_once MODULES_LEX_PATH . '/include/management/eurovocManagement.inc.php';
require_once MODULES_LEX_PATH . '/include/management/jexManagement.inc.php';

class lexManagement
{
	private $_userObj;
	private $_canDO;
	private $_tabNeeded;
	
    /**
     * name constructor
     */
    public function __construct(ADALoggableUser $userObj=null) {
    	if (!is_null($userObj)) {
    		$this->_userObj = $userObj;
    		$this->_canDO = $GLOBALS['canDO'][$this->_userObj->getType()];
    	}
    }
    
    public function runSourceAssetZoom ($sourceID, $assetID) {
    	/* @var $html string holds html code to be retuned */
    	$htmlObj = null;
    	/* @var $path   string  path var to render in the help message */
    	$help = translateFN('Benvenuto nel modulo GIUR');
    	/* @var $status string status var to render in the breadcrumbs */
    	$title= translateFN('GIUR');
    	
    	$assetOK = true;
    	
    	if (is_array($assetID) && count($assetID)>0) {
			/**
			 * the datahandler is needed here
			 */
			$pointer = (!is_null($_SESSION['sess_selected_tester'])) ? $_SESSION['sess_selected_tester'] : MODULES_LEX_PROVIDER_POINTER;
			if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();					
			$dh = AMALexDataHandler::instance(MultiPort::getDSN($pointer));
			/**
			 * get the source associated with the first asset and
			 * assume all other assets are from the same source.
			 * Should this assumption not be true, the asset that are
			 * not from the same source as the first one will not be shown.
			 */
			$assetObj = $dh->asset_get(reset($assetID));
			
			if ($assetObj === false) {
				$assetOK = false;
			} else if (is_object($assetObj)) {
				if(is_null($sourceID)) $sourceID = $assetObj->module_lex_fonti_id;
			}
		}
		
		if ($assetOK) {
			$data = $this->runSourceZoom($sourceID);
			$htmlObj = $data['htmlObj'];
		} else {
    		$htmlObj = CDOMElement::create('div','class:no-permissions');
    		$htmlObj->addChild (new CText(translateFN('Asset inesistente o non valido')));		
		}
		
		return array(
				'htmlObj'   => $htmlObj,
				'help'      => $help,
				'title'     => $title,
		);
    }
    
    /**
     * build, manage and display the index.php?op=zoom page
     * 
     * @param number  $sourceID the id of the source to be displayed
     * 
     * @return multitype:string Ambigous <NULL, CBaseElement, unknown> Ambigous <string, string>
     * 
     * @access public
     */
    public function runSourceZoom ($sourceID) {
    	/* @var $html string holds html code to be retuned */
    	$htmlObj = null;
    	/* @var $path   string  path var to render in the help message */
    	$help = translateFN('Benvenuto nel modulo GIUR');
    	/* @var $status string status var to render in the breadcrumbs */
    	$title= translateFN('GIUR');
    	
    	if (is_null($sourceID)) {
    		$htmlObj = CDOMElement::create('div','class:no-permissions');
    		$htmlObj->addChild (new CText(translateFN('Non hai specificato una fonte')));
    	} else if ($this->canDo(ZOOM_SOURCE)) {
    		
    		$jexObj = new jexManagement($this->_userObj);
    		$sourceArr = $jexObj->getSource($sourceID);
    		
    		$htmlObj = CDOMElement::create('div','id:sourceZoom');
    		$htmlObj->addChild (jexManagement::getSourceZoomContent($sourceArr['titolo'],$sourceArr[AMALexDataHandler::$PREFIX.'fonti_id'], $this->canDo(EDIT_SOURCE), $sourceArr['attachedFile']));
    		
    	} else {
			// user cannot do anything, report it as a message
			$htmlObj = CDOMElement::create('div','class:no-permissions');
			$htmlObj->addChild (new CText(translateFN('Non hai permessi sufficienti per fare nessuna azione')));			
		}

		return array(
			'htmlObj'   => $htmlObj,
			'help'      => $help,
			'title'     => $title,
		);
    	
    }
    
    /**
     * build, manage and display the module's pages
     * 
     * @return array
     * 
     * @access public
     */
	public function run() {
		/* @var $html string holds html code to be retuned */
		$htmlObj = null;		
		/* @var $path   string  path var to render in the help message */
		$help = translateFN('Benvenuto nel modulo GIUR');
		/* @var $status string status var to render in the breadcrumbs */
		$title= translateFN('GIUR');
		
		if (isset ($this->_canDO) && count ($this->_canDO)>0) {
			// user has permissions to do something
			// generate the main lexmenu div
			$htmlObj = CDOMElement::create('div','id:lexmenu');
			// generate the ul holding the li(s) for the tabs
			$ul = CDOMElement::create('ul');
			// generate the container for the tabs content
			$tabsContainerDIV = CDOMElement::create('div','class:tabscontainer');
			
			foreach ($this->_canDO as $currentTab => $actionCode) {

				if  (!in_array($actionCode,$GLOBALS['tabNeeded'])) continue;
				
				// generate the link for the current tab, in a li element
				$li = CDOMElement::create('li');
				$a  = CDOMElement::create('a','href:#tabs-'.$currentTab);					
				// holds the content of the current tab
				$div = CDOMElement::create('div','id:tabs-'.$currentTab);
				
				switch ($actionCode) {
					case IMPEXPORT_EUROVOC:
						$a->addChild (new CText(eurovocManagement::getTabTitle($actionCode)));
						$div->addChild (eurovocManagement::getImpExportForm());
						break;
					case EDIT_EUROVOC:
						$a->addChild (new CText(eurovocManagement::getTabTitle($actionCode)));
						$div->addChild (eurovocManagement::getEditPage());
						break;
					case IMPORT_JEX:
						$a->addChild (new CText(jexManagement::getTabTitle($actionCode)));
						$div->addChild (jexManagement::getImportForm());
						break;
					case EDIT_SOURCE:
					case VIEW_SOURCE:
						$a->addChild (new CText(jexManagement::getTabTitle($actionCode)));
						$div->addChild (jexManagement::getEditContent());
						break;
					case SEARCH_SOURCE:
						if (defined('MODULES_HOLISSEARCH') && MODULES_HOLISSEARCH) {
							$a->addChild (new CText(translateFN('Cerca')));
							$a->setAttribute('href', MODULES_HOLISSEARCH_HTTP);
							$div->addChild (CDOMElement::create('div'));
						} else {
							unset($a);
						}
						break;
					default:
						break;
				}
				
				if (isset($a)) {
					$li->addChild ($a);
					$ul->addChild ($li);
					$tabsContainerDIV->addChild($div);
				}
			}			
			$htmlObj->addChild($ul);
			$htmlObj->addChild($tabsContainerDIV);
			
		} else {
			// user cannot do anything, report it as a message
			$htmlObj = CDOMElement::create('div','class:no-permissions');
			$htmlObj->addChild (new CText(translateFN('Non hai permessi sufficienti per fare nessuna azione')));			
		}

		return array(
			'htmlObj'   => $htmlObj,
			'help'      => $help,
			'title'     => $title,
		);
	}
	
	/**
	 * checks if the user can do an action
	 * 
	 * @param string $action the action constant name to check
	 * 
	 * @return boolean true if user is allowed to perform the action
	 * 
	 * @access public
	 */
	public function canDo($action=null) {
		if (!is_null($action) && isset ($this->_canDO) && 
		    count ($this->_canDO)>0 && in_array($action, $this->_canDO)) {
			return true;
		}
		return false;
	}
} // class ends here