<?php
/**
 * logForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    graffio <graffio@lynxlab.com>
 * @copyright Copyright (c) 2010-2015, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 *
 */
class LogForm extends FForm {
    public function  __construct($log_text) {
        parent::__construct();

        $this->addTextarea('log_today', translateFN('Appunti personali'));
        $this->addHidden('log_text')
              ->withData($log_text);  
    }
}