<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\Contao\Core;

/**
 * Frontend module extension
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
abstract class Module extends \Contao\Module
{
	/**
	 * Module generate Hook
	 *
	 * @return string
	 */
	public function generate()
	{
		return \MadeYourDay\Contao\FrontendHelper::generateFrontendModule(
			parent::generate(),
			$this
		);
	}
}
