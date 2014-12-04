<?php
/**
 * UserRegistrationForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';
include_once ('nationList.inc.php');

/**
 * Description of UserRegistrationForm
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserRegistrationForm extends FForm
{
    public function  __construct($cod=FALSE, $action=NULL) {
        parent::__construct();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName('registration');

        $this->addTextInput('nome', translateFN('Nome'))
             ->setRequired()
             ->setValidator(FormValidator::FIRSTNAME_VALIDATOR);

        $this->addTextInput('cognome', translateFN('Cognome'))
             ->setRequired()
             ->setValidator(FormValidator::LASTNAME_VALIDATOR);

        $this->addTextInput('birthdate', translateFN('Data di nascita'))
        	 ->setRequired()
             ->setValidator(FormValidator::DATE_VALIDATOR);
        
        $this->addTextInput('birthcity', translateFN('Comune o stato estero di nascita'))
        ->setRequired()
        ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        
        $this->addTextInput('birthprovince', translateFN('Provincia di nascita'));

        $this->addTextInput('email', translateFN('Email'))
             ->setRequired()
             ->setValidator(FormValidator::EMAIL_VALIDATOR);

        $this->addSelect(
            'sesso',
             translateFN('Genere'),
             array(
                 '0' => translateFN('Scegli un genere'),
                 'M' => translateFN('Maschio'),
                 'F' => translateFN('Femmina')
             ),
             '0');

        $this->addTextInput('matricola', translateFN('Numero di matricola'))
        	->withData(AMA_TYPE_USER_GENERIC)->setHidden();
        
        $this->addTextInput('codice_fiscale', translateFN('Codice Fiscale'))
        	->setRequired()->setValidator(FormValidator::ITALIAN_FISCALCODE_VALIDATOR);
                
        $accetto = translateFN ('accetto i');
        $termini = translateFN('termini di servizio e le norme sulla privacy');
        
        if (defined('PRIVACY_DOC'))
        {
	        $privacyPath = ROOT_DIR;
	        
	        if (!MULTIPROVIDER) {
	        	$privacyPath .= '/clients/'.$GLOBALS ['user_provider'];
	        }	        
	        $privacyPath .= '/docs/'.PRIVACY_DOC;
	        if (is_file($privacyPath)) {
	        	$privacyLbl = '<a href="'.str_replace(ROOT_DIR, HTTP_ROOT_DIR, $privacyPath).'" target="_blank">'.$termini.'</a>';
	        }
	        else {
	        	$privacyLbl = $termini;
	        }
        } else $privacyLbl = $termini;
        
        if ($_SESSION['sess_userObj'] instanceof ADAGuest)
        {
	        $this->addSelect ('privacy', $accetto.' '.$privacyLbl,
	        		array (0=>translateFN('No'), 1=>translateFN('Sì')), 0 )
	        		->setRequired()
	        		->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
        }
        
/*
 * 
        if ($cod) {
            $this->addTextInput('codice', translateFN('Codice'))
                 ->setRequired()
                 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        }

 * 
 */
    }
}
