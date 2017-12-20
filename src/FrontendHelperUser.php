<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * RockSolid Frontend Helper User
 *
 * This User object is used to check if a backend user is logged in while
 * accessing the frontend
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class FrontendHelperUser extends \BackendUser
{
	/**
	 * @var FrontendHelperUser Singelton instance
	 */
	protected static $objInstance;

	/**
	 * @var boolean|null Caches the result of \User::authenticate()
	 */
	protected $frontendHelperUserAuthenticated = null;

	/**
	 * Disable BackendUser authentication redirect and cache the result
	 */
	public function authenticate()
	{
		// Backwards compatibility for Contao 4.4
		if (!$this instanceof UserInterface) {
			if ($this->frontendHelperUserAuthenticated === null) {
				$this->frontendHelperUserAuthenticated = \User::authenticate();
			}
			return $this->frontendHelperUserAuthenticated;
		}

		return $this->username && $this->intId;
	}

	/**
	 * disable session saving
	 */
	public function __destruct(){}

	/**
	 * set all user properties from a database record
	 */
	protected function setUserFromDb()
	{
		$this->intId = $this->id;

		foreach ($this->arrData as $key => $value) {
			if (! is_numeric($value)) {
				$this->$key = \StringUtil::deserialize($value);
			}
		}

		$always = array('alexf');
		$depends = array();

		if (is_array($GLOBALS['TL_PERMISSIONS']) && ! empty($GLOBALS['TL_PERMISSIONS'])) {
			$depends = array_merge($depends, $GLOBALS['TL_PERMISSIONS']);
		}

		if ($this->inherit == 'group') {
			foreach ($depends as $field) {
				$this->$field = array();
			}
		}

		$inherit = in_array($this->inherit, array('group', 'extend')) ? array_merge($always, $depends) : $always;
		$time = time();

		foreach ((array) $this->groups as $id) {

			$objGroup = $this->Database
				->prepare("SELECT * FROM tl_user_group WHERE id=? AND disable!=1 AND (start='' OR start<$time) AND (stop='' OR stop>$time)")
				->limit(1)
				->execute($id);

			if ($objGroup->numRows > 0) {
				foreach ($inherit as $field) {
					$value = \StringUtil::deserialize($objGroup->$field, true);
					if (!empty($value)) {
						$this->$field = array_merge((is_array($this->$field) ? $this->$field : (($this->$field != '') ? array($this->$field) : array())), $value);
						$this->$field = array_unique($this->$field);
					}
				}
			}

		}
	}
}
