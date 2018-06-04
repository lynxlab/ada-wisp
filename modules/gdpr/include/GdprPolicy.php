<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

/**
 * Class for a GDPR privacy policy
 *
 * @author giorgio
 */
if (!defined('GdprPolicyClassTable')) define('GdprPolicyClassTable', AMAGdprDataHandler::PREFIX . 'policy_content');

class GdprPolicy extends GdprBase {

	/**
	 * table name for this class
	 *
	 * @var string
	 */
	const table =  GdprPolicyClassTable;

	const editButtonLabel = 'modifica';

	/**
	 * page to which the user must be redirected when
	 * an accept mandatory policies action is required
	 *
	 * @var string
	 */
	const acceptPoliciesPage = 'acceptPolicies.php';

	/**
	 * string to be used as the key to save and access session variables
	 *
	 * @var string
	 */
	const sessionKey = 'gdpr-policy-sess';

	protected $policy_content_id;
	protected $title;
	protected $content;
	protected $tester_pointer;
	protected $mandatory;
	protected $isPublished;
	protected $version;
	protected $lastEditTS;

	/**
	 * Gets the action button object
	 *
	 * @param boolean $isClose
	 * @return NULL|\CBaseElement
	 */
	public function getActionButton() {
		$button = \CDOMElement::create('a','class:ui tiny button');
		$button->addChild(new \CText(translateFN(self::editButtonLabel)));
		$button->setAttribute('href', 'editPolicy.php?id='.$this->getPolicy_content_id());
		return $button;
	}

	/**
	 * Gets the header array for the policies html table
	 *
	 * @return array
	 */
	public static function getTableHeader($withActions = false) {
		$headerArr = array(
			'ID',
			'Titolo',
			'Obbligatoria',
			'Pubblicata',
			'Ultima modifica'
		);

		if ($withActions) $headerArr[] = 'Azioni';

		return array_map(function ($el) {
			return ucwords(strtolower(translateFN($el)));
		}, $headerArr);
	}

	/**
	 * @return mixed
	 */
	public function getPolicy_content_id() {
		return $this->policy_content_id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return mixed
	 */
	public function getTester_pointer() {
		return $this->tester_pointer;
	}

	/**
	 * @return mixed
	 */
	public function getMandatory() {
		return $this->mandatory;
	}

	/**
	 * @return mixed
	 */
	public function getIsPublished() {
		return $this->isPublished;
	}

	/**
	 * @return mixed
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return mixed
	 */
	public function getLastEditTS() {
		return $this->lastEditTS;
	}

	/**
	 * @param mixed $policy_content_id
	 */
	public function setPolicy_content_id($policy_content_id) {
		$this->policy_content_id = $policy_content_id;
		return $this;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @param mixed $content
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	/**
	 * @param mixed $tester_pointer
	 */
	public function setTester_pointer($tester_pointer) {
		$this->tester_pointer = $tester_pointer;
		return $this;
	}

	/**
	 * @param mixed $mandatory
	 */
	public function setMandatory($mandatory) {
		$this->mandatory = $mandatory;
		return $this;
	}

	/**
	 * @param mixed $isPublished
	 */
	public function setIsPublished($isPublished) {
		$this->isPublished = $isPublished;
		return $this;
	}

	/**
	 * @param mixed $version
	 */
	public function setVersion($version) {
		$this->version = intval($version);
		return $this;
	}

	/**
	 * @param mixed $lastEditTS
	 */
	public function setLastEditTS($lastEditTS) {
		$this->lastEditTS = $lastEditTS;
		return $this;
	}

}
