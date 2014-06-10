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
 * class for handling import of JEX files
 *
 * @author giorgio
 */
class FormJexImport extends FForm {

	public function __construct( $formName, $action=null, $typologiesArr ) {
		parent::__construct();
		$this->setName($formName);
		
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('id_fonte');
		
		$this->addTextInput('numero_fonte', translateFN('Numero Fonte'))
		     ->setRequired()
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('titolo_fonte', translateFN('Titolo Fonte'))
		     ->setRequired()
		     ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('data_pubblicazione', translateFN('Data Pubblicazione in G.U.'))
		     ->setValidator(FormValidator::DATE_VALIDATOR);
		
		$sel_tipologia = FormControl::create(FormControl::SELECT, 'tipologia', translateFN('tipologia'));
		$sel_tipologia->withData($typologiesArr);
		
		$add_tipologia = FormControl::create(FormControl::INPUT_TEXT,'nuova_tipologia','&nbsp;');
		$add_btn = FormControl::create(FormControl::INPUT_BUTTON,'nuova_tipologia_btn',translateFN('aggiungi tipologia'));
		$add_btn->setAttribute('class', 'dontuniform');
		$add_btn->setAttribute('onClick', 'javascript:addTipologia();');
		
		$this->addFieldset('','set_tipologia')->withData(array ($sel_tipologia,$add_tipologia,$add_btn));
		
		$control = FormControl::create(FormControl::INPUT_FILE, 'importfile-'.$formName, translateFN ('Seleziona un file .zip da importare'));
		$control->setAttribute('class', 'doPeke');
		
		$this->addControl($control);
	}
}
