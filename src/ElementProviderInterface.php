<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

/**
 * @author Martin Auswöger <martin@madeyourday.net>
 */
interface ElementProviderInterface
{
	/**
	 * Get elements configuration arrays indexed by type
	 *
	 * Each configuration array consists of the following elements:
	 *
	 * - label       string  Label of the element
	 * - group       string  Label of the elements group
	 * - insert      boolean If the element can be inserted via drag and drop
	 * - showToolbar boolean If an edit toolbar should be shown in the frontend
	 * - liveReload  boolean If the element can be rerenderd without a page reload
	 *
	 * @param string $table
	 *
	 * @return array
	 */
	public function getElements($table);

	/**
	 * Get default values for new element by table and type
	 *
	 * @param string $table
	 * @param string $type
	 *
	 * @return array
	 */
	public function getDefaultValues($table, $type);
}
