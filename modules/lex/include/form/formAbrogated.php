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

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');
require_once MODULES_LEX_PATH . '/include/form/controls/abrogatedFieldset.inc.php';

/**
 * class for handling assets abrogation
 *
 * @author giorgio
 */
class FormAbrogated extends FForm {
	
	public function __construct( $formName, $action=null, $assetID, $abrogatedRows=array() ) {
		parent::__construct();
		// completely turn off jQuery uniform plugin for this form
		$this->setUniformJavascript(null, false);
		
		$this->setName($formName);
		$this->setId($formName.'Form');
		
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('assetID')->withData($assetID);
		
		$this->addControl(new abrogatedFieldset($abrogatedRows));
	}
}
