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
class AskServiceForm extends FForm {
    public function  __construct($services, $user_provider_id=NULL) {
        parent::__construct();
        //$authors = array_merge(array(0 => translateFN('Scegli un autore per il corso')), $authors);
        //$languages = array_merge(array(0 => translateFN('Scegli una lingua per il corso')), $languages);

        //$services[0] = translateFN('I need help for');

        $this->addSelect('id_service',translateFN('I need help for'),$services,0)
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addHidden('user_provider_id')->withData($user_provider_id);
        $this->addHidden('op')->withData('subscribe');

        $this->addTextarea('question', translateFN('your question'))
             ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
    }
}