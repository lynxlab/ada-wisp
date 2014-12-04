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

/**
 * class for handling file upload module form
 *
 * @author giorgio
 */
class FormUploadImportFile extends FForm {

	public function __construct( $formName, $action=null ) {
		parent::__construct();
		$this->setName($formName);
		$this->setId($formName.'Form');
		
		if (!is_null($action)) $this->setAction($action);
		
		$control = FormControl::create(FormControl::INPUT_FILE, 'importfile-'.$formName, translateFN ('Seleziona un file .zip da importare'));
		$control->setAttribute('class', 'doPeke');
		
		$this->addControl($control);
	}
}
