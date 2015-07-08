<?php
/**
 * UserDisabilitaForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2015, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';

class UserDisabilitaForm extends FForm
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
    	$theForm->addTextInput('TIPO_HAND_DES', translateFN('Tipo disabilità'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);
    	
    	$theForm->addTextInput('PERC_HAND', translateFN('Percentuale'))
    	->setValidator(FormValidator::DEFAULT_VALIDATOR);

    	// add an extra field if we're embedding the controls
    	// in the standard edit_user form
    	if (!isset($this))
    	{
    		$theForm->addHidden('forceSaveExtra')->withData(true);
    	}
    }
}
