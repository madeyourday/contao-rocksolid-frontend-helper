<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\Contao;

/**
 * RockSolid Frontend Helper
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class FrontendHelper extends \Controller
{
	/**
	 * @var array backend to frontend modules linking
	 */
	protected static $backendModules = array(
		'news' => array(
			'table' => 'tl_news',
			'column' => 'news_archives',
			'columnType' => 'serialized',
			'feModules' => array(
				'newslist',
				'newsreader',
				'newsarchive',
				'newsmenu',
			),
		),
		'calendar' => array(
			'table' => 'tl_calendar_events',
			'column' => 'cal_calendar',
			'columnType' => 'serialized',
			'feModules' => array(
				'calendar',
				'eventreader',
				'eventlist',
				'eventmenu',
			),
		),
		'newsletter' => array(
			'table' => 'tl_newsletter',
			'column' => 'nl_channels',
			'columnType' => 'serialized',
			'feModules' => array(
				'nl_list',
				'nl_reader',
			),
		),
		'faq' => array(
			'table' => 'tl_faq',
			'column' => 'faq_categories',
			'columnType' => 'serialized',
			'feModules' => array(
				'faqlist',
				'faqreader',
				'faqpage',
			),
		),
		'form' => array(
			'table' => 'tl_form_field',
			'column' => 'form',
			'columnType' => 'plain',
			'feModules' => array(
				'form',
			),
		),
		'rocksolid_slider' => array(
			'table' => 'tl_rocksolid_slide',
			'column' => 'rsts_id',
			'columnType' => 'plain',
			'feModules' => array(
				'rocksolid_slider',
			),
		),
	);

	/**
	 * parseFrontendTemplate hook
	 *
	 * @param  string $content  html content
	 * @param  string $template template name
	 * @return string           modified $content
	 */
	public function parseFrontendTemplate($content, $template)
	{
		if (! static::checkLogin()) {
			return $content;
		}

		$data = array(
			'template' => $template,
			'templatePath' => substr($this->getTemplate($template), strlen(TL_ROOT) + 1),
		);

		if (substr($template, 0, 3) === 'fe_') {
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/rocksolid-frontend-helper/assets/js/main.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/rocksolid-frontend-helper/assets/css/main.css';
			$content = explode('<body', $content, 2);
			return $content[0] . static::insertData('<body' . $content[1], $data);
		}

		return static::insertData($content, $data);
	}

	/**
	 * generateFrontendModule hook
	 *
	 * @param  string  $content html content
	 * @param  \Module $module  module object
	 * @return string           modified $content
	 */
	public static function generateFrontendModule($content, $module)
	{
		if (! static::checkLogin()) {
			return $content;
		}

		if ($module->type === 'article') {
			$data['articleURL'] = static::getBackendURL('article', 'tl_article', $module->id);
			\System::loadLanguageFile('tl_article');
			$data['articleLabel'] = $GLOBALS['TL_LANG']['tl_article']['editheader'][0];
		}
		else {
			$data['feModuleURL'] = static::getBackendURL('themes', 'tl_module', $module->id);
			\System::loadLanguageFile('tl_module');
			$data['feModuleLabel'] = $GLOBALS['TL_LANG']['tl_module']['edit'][0];
		}

		foreach (static::$backendModules as $do => $config) {
			if (in_array($module->type, $config['feModules'])) {
				$id = $module->{$config['column']};
				if ($config['columnType'] === 'serialized') {
					$id = deserialize($id, true);
					$id = $id[0];
				}
				if ($id) {
					$data['beModuleURL'] = static::getBackendURL($do, $config['table'], $id, false);
					\System::loadLanguageFile($config['table']);
					$data['beModuleLabel'] = $GLOBALS['TL_LANG'][$config['table']]['editheader'][0];
					$data['beModuleType'] = $do;
				}
			}

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
	public static function getContentElementHook($element, $content)
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

		\System::loadLanguageFile('tl_content');
		$data = array(
			'editURL' => static::getBackendURL($do, 'tl_content', $element->id),
			'editLabel' => $GLOBALS['TL_LANG']['tl_content']['edit'][0],
		);

		if ($element->type === 'module' && $element->module) {
			$data['feModuleURL'] = static::getBackendURL('themes', 'tl_module', $element->module);
			\System::loadLanguageFile('tl_module');
			$data['feModuleLabel'] = $GLOBALS['TL_LANG']['tl_module']['edit'][0];
		}

		if ($element->type === 'form' && $element->form) {
			$data['beModuleURL'] = static::getBackendURL('form', 'tl_form_field', $element->form, false);
			\System::loadLanguageFile('tl_form_field');
			$data['beModuleLabel'] = $GLOBALS['TL_LANG']['tl_form_field']['editheader'][0];
			$data['beModuleType'] = 'form';
		}

		return static::insertData($content, $data);
	}

	/**
	 * checks if a Backend User is logged in
	 *
	 * @return boolean true if user is logged in
	 */
	public static function checkLogin()
	{
		$User = FrontendHelperUser::getInstance();

		return $User->authenticate();
	}

	/**
	 * create backend edit URL
	 *
	 * @param  string $do
	 * @param  string $table
	 * @param  string $id
	 * @return string
	 */
	protected static function getBackendURL($do, $table, $id, $act = 'edit')
	{
		return 'contao/main.php?do=' . $do . '&table=' . $table . ($act ? '&act=' . $act : '') . '&id=' .  $id . '&rt=' . REQUEST_TOKEN;
	}

	/**
	 * inserts data into the first tag in $content as data-frontend-helper
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

			if (preg_match('(\\sdata-frontend-helper="([^"]*)")is', $matches[0], $matches2)) {
				$data = array_merge(json_decode(html_entity_decode($matches2[1]), true), $data);
				$matches[0] = preg_replace('(\\sdata-frontend-helper="([^"]*)")is', '', $matches[0]);
			}

			return $matches[0] . ' data-frontend-helper="' . htmlspecialchars(json_encode($data)) . '"' . $content;
		}

		return $content;
	}
}
