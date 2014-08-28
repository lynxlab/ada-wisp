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
 * class for eurovoc export form
 *
 * @author giorgio
 */
class FormExportEurovoc extends FForm {

	public function __construct( $formName, $languages=null ) {
		parent::__construct();
		$this->setName($formName);
		$this->setId($formName.'Form');
		
		if (!is_null($languages)) {
			
			$languages += array ('*'=>translateFN('Tutte'));
			
			$this->addSelect('exportLang', translateFN('Seleziona la lingua da Esportare'), $languages, getLanguageCode())
				 ->setRequired();
		}
		
		// styled submit button!!
		$this->addButton('eurovoc-exportButton', 'Esporta Ontologia')
			 ->setAttribute('onclick', 'javascript:doExportEurovoc();');		
	}
}
