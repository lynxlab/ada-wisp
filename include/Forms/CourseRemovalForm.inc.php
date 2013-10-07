<?php
/**
 * CourseRemovalForm file
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
class CourseRemovalForm extends FForm {
    public function  __construct($courseObj,$notice='') {
        parent::__construct();
        if ($notice != '') {
            $noticeText = $notice . ' ' .
                sprintf(translateFN('Vuoi davvero eliminare il corso: "%s"?'), $courseObj->getTitle());
        } else {
            $noticeText = sprintf(translateFN('Vuoi davvero eliminare il corso: "%s"?'), $courseObj->getTitle());
        }
        $this->addRadios(
                'deleteCourse',
                $noticeText,
                array(0 => translateFN('No'), 1 => translateFN('Si')),
                0);
        $this->addHidden('id_course')
             ->withData($courseObj->getId());
    }
}