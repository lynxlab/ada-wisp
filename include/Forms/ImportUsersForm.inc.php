<?php
/**
 * IMPORT USERS FORM
 *
 * @package		switcher/import_users.php
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once(ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php');

/**
 * class for handling file upload of switcher/import_users.php
 *
 * @author giorgio
 */
class FormImportUsers extends FForm {

	public function __construct( $formName ) {
		parent::__construct();
		$this->setName($formName);
		$this->addFileInput('importfile', translateFN ('Seleziona un file .csv da importare'));
	}
}
