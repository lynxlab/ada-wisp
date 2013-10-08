<?php
/**
 * CourseModelForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 *
 */
class ServiceModelForm extends FForm {
    public function  __construct($authors, $languages) {
        parent::__construct();
        //$authors = array_merge(array(0 => translateFN('Scegli un autore per il corso')), $authors);
        //$languages = array_merge(array(0 => translateFN('Scegli una lingua per il corso')), $languages);
        $this->setName('formEditService');
        $this->setSubmitValue(translateFN('Salva'));
        
        
        $authors[0] = translateFN('Scegli un autore per i contenuti del servizio');
        $languages[0] = translateFN('Scegli una lingua per i contenuti del servizio');


        $this->addSelect('id_utente_autore',translateFN('Autore'),$authors,0)
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

        $this->addSelect('id_lingua', translateFN('Lingua per i contenuti del servizio'),$languages,0)
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

        $this->addHidden('id_corso');

        $this->addHidden('id_layout')->withData(0);

        $this->addTextInput('nome', translateFN('Codice servizio'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('titolo', translateFN('Titolo'))
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        //$this->addTextInput('data_creazione', translateFN('Data creazione'));

        //$this->addTextInput('data_pubblicazione', translateFN('Data pubblicazione'));

        $this->addTextarea('descrizione', translateFN('Descrizione'))
             ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);

//        $this->addTextInput('id_nodo_iniziale', translateFN('Id nodo iniziale'))
//             ->withData(0)
//             ->setRequired()
//             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);
//
//        $this->addTextInput('id_nodo_toc', translateFN('Id nodo toc'));
//
//        $this->addTextInput('media_path', translateFN('Media path'))
//             ->withData(MEDIA_PATH_DEFAULT);
//
//        $this->addTextInput('static_mode', translateFN('Static mode'));

        /*
        $this->addTextInput('crediti', translateFN('Crediti corso'))
             ->setRequired()
             ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);
         * 
         */
        
        $desc = translateFN('Livello servizio:');
        $this->addRadios(
                'common_area',
                $desc,
                array(0 => translateFN('Help per studente'), 1 => translateFN('Area di interazione per utenti registrati')),
                0);
        
        $this->addHidden('id_nodo_iniziale')->withData(0);
        $this->addHidden('crediti')->withData(0);
        $this->addHidden('id_nodo_toc');
        $this->addHidden('media_path');
        $this->addHidden('static_mode');
    }
}