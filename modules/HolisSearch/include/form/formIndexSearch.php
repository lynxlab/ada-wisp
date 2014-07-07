<?php
/**
 * HOLISSEARCH MODULE.
 *
 * @package        HolisSearch module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           HolisSearch
 * @version		   0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling file upload module form
 *
 * @author giorgio
 */
class FormIndexSearch extends FForm {

	public function __construct() {
		parent::__construct();
		$this->setName('searchForm');
		$this->setId('searchForm');
		$this->setMethod('GET');
		$this->addTextInput('searchtext', translateFN('Cosa stai cercando ?'));
	}
}
