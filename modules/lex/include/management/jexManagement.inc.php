<?php
/**
 * JEX Management Class for lex module
 *
 * @package 	lex
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	    0.1
 */

/**
 * class for managing JEX
 *
 * @author giorgio
 */
		
class jexManagement
{
    /**
     * name constructor
     */
    public function __construct() {
    	
    }
    
    public static function getTabTitle() {
    	return translateFN('Importa File JEX');
    	
    }
    
	public static function getImportForm() {
		$htmlObj = CDOMElement::create('div','id:jexContainer');
		
		$title = CDOMElement::create('span', 'class:importTitle');
		$title->addChild (new CText(translateFN('Importa da JEX')));
		
		$form = new FormUploadImportFile('jex');
		
		$htmlObj->addChild($title);
		$htmlObj->addChild(new CText($form->getHtml()));
		return $htmlObj;
	}
} // class ends here