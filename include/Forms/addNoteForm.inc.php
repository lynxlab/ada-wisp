<?php
/**
 * CourseModelForm file
 *
 * PHP version 5
 *
 * @package   user
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 *
 */
class AddNoteForm extends FForm {
    public function  __construct($userId, $instanceId, $parentNodeId, $subject='') {
        parent::__construct();
        //$authors = array_merge(array(0 => translateFN('Scegli un autore per il corso')), $authors);
        //$languages = array_merge(array(0 => translateFN('Scegli una lingua per il corso')), $languages);

        //$services[0] = translateFN('I need help for');
        $action = HTTP_ROOT_DIR. "/browsing/add_note.php";
        $this->setAction($action);
        
        $this->addHidden('userId')->withData($userId);
        $this->addHidden('instanceId')->withData($instanceId);
        $this->addHidden('parentNodeId')->withData($parentNodeId);

        if ($subject== '') {
            $this->addTextInput('subject', translateFN('Oggetto'))
                 ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);
        } else {
            $this->addHidden('subject')->withData($subject);
        }
        $this->addTextarea('text', translateFN(''))
             ->setValidator(FormValidator::MULTILINE_TEXT_VALIDATOR);
        
    }
}