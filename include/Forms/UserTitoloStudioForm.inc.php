<?php
/**
 * UserTitoloStudioForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2015, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';

class UserTitoloStudioForm extends FForm
{
    public function  __construct($action=NULL) {
        parent::__construct();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName('extraDataForm');
        $this->setSubmitValue(translateFN('Salva'));
        /**
         * Following value to be set with a call
         * to fillWithArrayData made by the code
         * who's actually using this form
         */
        $this->addHidden('id_utente')->withData(0);
        
        self::addExtraControls($this);        
    }
    
    public static function addExtraControls (FForm $theForm)
    {
    	$theForm->addTextInput('TIPO_TITOLO_DESC', translateFN('Tipo titolo di studio'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('VOTO', translateFN('Voto'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('VOTO_MAX', translateFN('Voto massimo'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('ANNO_MATURITA', translateFN('Anno di conseguimento'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('SCUOLA_DESC', translateFN('Scuola'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('PROVINCIA_SCUOLA_DESC', translateFN('Provincia scuola'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('REGIONE_SCUOLA_DESC', translateFN('Regione scuola'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);

    	// add an extra field if we're embedding the controls
    	// in the standard edit_user form
    	if (!isset($this))
    	{
    		$theForm->addHidden('forceSaveExtra')->withData(true);
    	}
    }
}
