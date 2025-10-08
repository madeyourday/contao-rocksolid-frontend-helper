<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

use Contao\BackendUser;
use Contao\Database;
use Contao\StringUtil;
use Contao\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * RockSolid Frontend Helper User.
 *
 * This User object is used to check if a backend user is logged in while
 * accessing the frontend
 */
class FrontendHelperUser extends BackendUser
{
    /**
     * @var FrontendHelperUser Singelton instance
     */
    protected static $objInstance;

    /**
     * @var bool|null Caches the result of User::authenticate()
     */
    protected $frontendHelperUserAuthenticated;

    /**
     * Disable BackendUser authentication redirect and cache the result.
     */
    public function authenticate()
    {
        // Backwards compatibility for Contao 4.4
        if (!$this instanceof UserInterface) {
            if (null === $this->frontendHelperUserAuthenticated) {
                $this->frontendHelperUserAuthenticated = User::authenticate();
            }

            return $this->frontendHelperUserAuthenticated;
        }

        return $this->username && $this->intId;
    }

    /**
     * set all user properties from a database record.
     */
    protected function setUserFromDb(): void
    {
        $this->intId = $this->id;

        foreach ($this->arrData as $key => $value) {
            if (!is_numeric($value)) {
                $this->$key = StringUtil::deserialize($value);
            }
        }

        $always = ['alexf'];
        $depends = [];

        if (!empty($GLOBALS['TL_PERMISSIONS']) && \is_array($GLOBALS['TL_PERMISSIONS'])) {
            $depends = array_merge($depends, $GLOBALS['TL_PERMISSIONS']);
        }

        if ('group' === $this->inherit) {
            foreach ($depends as $field) {
                $this->$field = [];
            }
        }

        $inherit = \in_array($this->inherit, ['group', 'extend'], true) ? array_merge($always, $depends) : $always;
        $time = time();

        foreach ((array) $this->groups as $id) {
            $objGroup = Database::getInstance()
                ->prepare("SELECT * FROM tl_user_group WHERE id=? AND disable!=1 AND (start='' OR start<$time) AND (stop='' OR stop>$time)")
                ->limit(1)
                ->execute($id)
            ;

            if ($objGroup->numRows > 0) {
                foreach ($inherit as $field) {
                    $value = StringUtil::deserialize($objGroup->$field, true);
                    if (!empty($value)) {
                        $this->$field = array_merge(\is_array($this->$field) ? $this->$field : ($this->$field !== '' ? [$this->$field] : []), $value);
                        $this->$field = array_unique($this->$field);
                    }
                }
            }
        }
    }
}
