<?php
/**
 * UserTipoIscrizioneForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2015, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';

class UserTipoIscrizioneForm extends FForm
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
    	$theForm->addTextInput('DATA_ISCR', translateFN('Data Iscrizione'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('ANNO_CORSO', translateFN('Anno di Corso'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('AA_ISCR_DESC', translateFN('A.A. di iscrizione'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('TASSE_IN_REGOLA_OGGI', translateFN('Tasse in regola'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('TIPO_ISCR_DESC', translateFN('Tipo Iscrizione'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('PT_DESC', translateFN('Tempo'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('TIPO_DID_DECODE', translateFN('Tipo didattica'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('STA_OCCUP_DECODE', translateFN('Situazione occupazionale'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);

    	// add an extra field if we're embedding the controls
    	// in the standard edit_user form
    	if (!isset($this))
    	{
    		$theForm->addHidden('forceSaveExtra')->withData(true);
    	}
    }
}
