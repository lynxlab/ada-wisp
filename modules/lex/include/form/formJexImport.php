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
			 ->setAttribute('class', 'dontuniform')
		     ->setRequired()
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('titolo_fonte', translateFN('Titolo Fonte'))
			 ->setAttribute('class', 'dontuniform')
		     ->setRequired()
		     ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->addTextInput('data_pubblicazione', translateFN('Data Pubblicazione'))
			 ->setAttribute('class', 'dontuniform')
			 ->setRequired()
		     ->setValidator(FormValidator::DATE_VALIDATOR);
		
		$sel_tipologia = FormControl::create(FormControl::SELECT, 'tipologia', translateFN('tipologia'));
		$sel_tipologia->setRequired();
		$sel_tipologia->setAttribute('class', 'dontuniform');
		$firstTipology = reset(array_keys($typologiesArr));
		$sel_tipologia->withData($typologiesArr,$firstTipology);
		
		$categoriesArr = sourceTypologyManagement::getTypologyChildren($firstTipology);

		$sel_categoria = FormControl::create(FormControl::SELECT, 'categoria', translateFN('categoria'));
		$sel_categoria->setAttribute('class', 'dontuniform');
		$firstCategory = reset(array_keys($categoriesArr));
		$sel_categoria->withData($categoriesArr,$firstCategory);
		
		$classesArr = sourceTypologyManagement::getCategoryChildren($firstTipology, $firstCategory);
		
		$sel_classe = FormControl::create(FormControl::SELECT, 'classe', translateFN('classe(fonte)'));
		$sel_classe->setAttribute('class', 'dontuniform');
		$sel_classe->withData($classesArr,reset(array_keys($classesArr)));
		
		$force_tipologia = FormControl::create(FormControl::SELECT, 'forcetipologia', translateFN('Queste scelte sovrascrivono quelle del file importato'));
		$force_tipologia->setAttribute('class', 'dontuniform');
		$force_tipologia->withData(array(0=>translateFN('No'),1=>translateFN('Si')),0);		
		
		$this->addFieldset('','set_tipologia')->withData(array ($sel_tipologia, $sel_categoria, $sel_classe, $force_tipologia));
		
// 		$add_tipologia = FormControl::create(FormControl::INPUT_TEXT,'nuova_tipologia','&nbsp;');
// 		$add_tipologia->setAttribute('class', 'dontuniform');
		
// 		$add_btn = FormControl::create(FormControl::INPUT_BUTTON,'nuova_tipologia_btn',translateFN('aggiungi tipologia'));
// 		$add_btn->setAttribute('class', 'dontuniform');
// 		$add_btn->setAttribute('onClick', 'javascript:addTipologia();');
		
// 		$this->addFieldset('','set_tipologia')->withData(array ($sel_tipologia,$add_tipologia,$add_btn));
		
		$control = FormControl::create(FormControl::INPUT_FILE, 'importfile-'.$formName, translateFN ('Seleziona un file .zip da importare'));
		$control->setAttribute('class', 'doPeke');
		
		$this->addControl($control);
	}
}
