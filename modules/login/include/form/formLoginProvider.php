<?php
/**
 * LOGIN MODULE - login provider edit form class
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling LDAP config
 *
 * @author giorgio
 */
class FormLoginProvider extends FForm {
	/**
	 * input field max length as defined in the module_login_options.value DB field
	 */
	private $maxlength = 255;

	public function __construct($data, $formName=null, $action=null, $selectOptions = null) {
		parent::__construct();
		$this->doNotUniform();
		if (!is_null($formName)) $this->setName($formName);
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('provider_id');
		
		$this->addSelect('className',
				translateFN('Classe PHP'),
				$selectOptions,
				reset($selectOptions) )
				->setRequired()
				->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$this->addTextInput('name', translateFN('Nome'))->setRequired()
			  ->setAttribute('maxlength', $this->maxlength)
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$this->addTextInput('buttonLabel', translateFN('Testo per il bottone'))->setRequired()
			 ->setAttribute('maxlength', $this->maxlength)
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		
		$this->fillWithArrayData($data);
	}
} // class ends here