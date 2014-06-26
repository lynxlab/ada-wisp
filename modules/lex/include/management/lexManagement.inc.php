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
    	$help = translateFN('Benvenuto nel modulo LEX');
    	/* @var $status string status var to render in the breadcrumbs */
    	$title= translateFN('lex');
    	
    	if (isset ($this->_canDO) && count ($this->_canDO)>0 && in_array(ZOOM_SOURCE, $this->_canDO)) {
    		
    		$jexObj = new jexManagement($this->_userObj);
    		$sourceArr = $jexObj->getSource($sourceID);
    		
    		$htmlObj = CDOMElement::create('div','id:sourceZoom');
    		$htmlObj->addChild (jexManagement::getSourceZoomContent($sourceArr['titolo'],$sourceArr[AMALexDataHandler::$PREFIX.'fonti_id']));
    		
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
		$help = translateFN('Benvenuto nel modulo LEX');
		/* @var $status string status var to render in the breadcrumbs */
		$title= translateFN('lex');
		
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
					case IMPORT_EUROVOC:
						$a->addChild (new CText(eurovocManagement::getTabTitle()));
						$div->addChild (eurovocManagement::getImportForm());
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
					default:
						break;
				}
				
				$li->addChild ($a);
				$ul->addChild ($li);
				$tabsContainerDIV->addChild($div);
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
} // class ends here