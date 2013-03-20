<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\Contao;

/**
 * RockSolid Frontend Guide
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class FrontendGuide
{
	/**
	 * parseFrontendTemplate hook
	 *
	 * @param  string $content  html content
	 * @param  string $template template name
	 * @return string           modified $content
	 */
	public static function parseFrontendTemplate($content, $template)
	{
		if (! static::checkLogin()) {
			return $content;
		}

		if (substr($template, 0, 3) === 'fe_') {
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/rocksolid-frontend-guide/assets/js/main.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/rocksolid-frontend-guide/assets/css/main.css';
		}

		return static::insertData($content, array(
			'template' => $template,
		));
	}

	/**
	 * generateFrontendModule hook
	 *
	 * @param  string $content      html content
	 * @param  string $templatePath template path
	 * @param  Object $model        model object
	 * @return string               modified $content
	 */
	public static function generateFrontendModule($content, $templatePath, $model)
	{
		if (! static::checkLogin()) {
			return $content;
		}

		$data = array(
			'templatePath' => substr($templatePath, strlen(TL_ROOT) + 1),
		);

		if ($model instanceof \ArticleModel) {
			$data['articleURL'] = static::getEditURL('article', 'tl_article', $model->id);
		}
		else {
			$data['editURL'] = static::getEditURL('themes', 'tl_module', $model->id);
		}

		return static::insertData($content, $data);
	}

	/**
	 * getContentElement hook
	 *
	 * @param  Object $element content element
	 * @param  string $content html content
	 * @return string          modified $content
	 */
	public static function getContentElement($element, $content)
	{
		if (! static::checkLogin()) {
			return $content;
		}

		$do = 'article';
		if ($element->ptable) {
			foreach ($GLOBALS['BE_MOD'] as $category) {
				foreach ($category as $moduleName => $module) {
					if (
						! empty($module['tables']) &&
						in_array($element->ptable, $module['tables']) &&
						in_array('tl_content', $module['tables'])
					) {
						$do = $moduleName;
						break 2;
					}
				}
			}
		}

		$data = array(
			'editURL' => static::getEditURL($do, 'tl_content', $element->id),
		);

		return static::insertData($content, $data);
	}

	/**
	 * checks if a Backend User is logged in
	 *
	 * @return boolean true if user is logged in
	 */
	public static function checkLogin()
	{
		if (! \Input::cookie('BE_USER_AUTH')) {
			return false;
		}

		$hash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? \Environment::get('ip') : '') . 'BE_USER_AUTH');
		if (
			\Input::cookie('BE_USER_AUTH') == $hash &&
			($objSession = \SessionModel::findByHashAndName($hash, 'BE_USER_AUTH')) &&
			$objSession->sessionID == session_id() &&
			($GLOBALS['TL_CONFIG']['disableIpCheck'] || $objSession->ip == \Environment::get('ip')) &&
			($objSession->tstamp + $GLOBALS['TL_CONFIG']['sessionTimeout']) > time()
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * create backend edit URL
	 *
	 * @param  string $do
	 * @param  string $table
	 * @param  string $id
	 * @return string
	 */
	protected static function getEditURL($do, $table, $id)
	{
		return 'contao/main.php?do=' . $do . '&table=' . $table . '&act=edit&id=' .  $id . '&rt=' . REQUEST_TOKEN;
	}

	/**
	 * inserts data into the first tag in $content as data-frontend-guide
	 * attribute in json format and merges existing data
	 *
	 * @param  string $content
	 * @param  array  $data
	 * @return string
	 */
	protected static function insertData($content, $data)
	{
		if (preg_match('(^.*?<[a-z0-9]+(?:\\s[^>]+|))is', $content, $matches)) {

			$content = substr($content, strlen($matches[0]));

			if (preg_match('(\\sdata-frontend-guide="([^"]*)"$)is', $matches[0], $matches2)) {
				$matches[0] = substr($matches[0], 0, - strlen($matches2[0]));
				$data = array_merge(json_decode(html_entity_decode($matches2[1]), true), $data);
			}

			return $matches[0] . ' data-frontend-guide="' . htmlspecialchars(json_encode($data)) . '"' . $content;
		}

		return $content;
	}
}
