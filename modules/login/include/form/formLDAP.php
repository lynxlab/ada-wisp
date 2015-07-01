<?php
/**
 * LOGIN MODULE - ldap login provider options edit form class
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
class FormLDAP extends FForm {
	/**
	 * input field max length as defined in the module_login_options.value DB field
	 */
	private $maxlength = 255;

	public function __construct($data, $formName=null, $action=null) {
		parent::__construct();
		$this->doNotUniform();
		if (!is_null($formName)) $this->setName($formName);
		if (!is_null($action)) $this->setAction($action);
		
		$this->addHidden('option_id');
		$this->addTextInput('name', translateFN('Nome'))->setRequired()
			  ->setAttribute('maxlength', $this->maxlength)
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$this->addTextInput('host', translateFN('Host'))->setRequired()
			 ->setAttribute('maxlength', $this->maxlength)
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$this->addTextInput('authdn', translateFN('DN Autenticazione'))->setRequired()
			 ->setAttribute('maxlength', $this->maxlength)
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		// UNIMC SPECIFIC
		$this->addTextInput('authuser', translateFN('Utente Autenticazione'))->setRequired()
		->setAttribute('maxlength', $this->maxlength)
		->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		$this->addPasswordInput('authpwd', translateFN('Password Autenticazione'))->setRequired()
		->setAttribute('maxlength', $this->maxlength)
		->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
		// END UNIMC SPECIFIC
		$this->addTextInput('basedn', translateFN('DN Ricerca'))->setRequired()
			 ->setAttribute('maxlength', $this->maxlength)
			 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);		
		$this->addTextInput('filter', translateFN('Filtro'))
			 ->setAttribute('maxlength', $this->maxlength);
		$this->addSelect('usertype',
				translateFN('Tipo Utente'),
				array(
						0 => translateFN('Scegli il tipo...'),
						AMA_TYPE_AUTHOR => translateFN('Autore'),
						AMA_TYPE_STUDENT => translateFN('Studente'),
						AMA_TYPE_SWITCHER => translateFN('Switcher'),
						AMA_TYPE_TUTOR => translateFN('Tutor'),
						AMA_TYPE_SUPERTUTOR => translateFN('Super Tutor')
				),
				0)
				->setRequired()
				->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
		
		$this->fillWithArrayData($data);
	}
} // class ends here