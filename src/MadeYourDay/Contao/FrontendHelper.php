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
	 * parseFrontendTemplate hook
	 *
	 * @param  string $content  html content
	 * @param  string $template template name
	 * @return string           modified $content
	 */
	public function parseFrontendTemplateHook($content, $template)
	{
		if (! $permissions = static::checkLogin()) {
			return $content;
		}

		$data = array();

		if (in_array('infos', $permissions)) {
			$data = array(
				'template' => $template,
				'templatePath' => substr($this->getTemplate($template), strlen(TL_ROOT) + 1),
			);
		}

		if (substr($template, 0, 3) === 'fe_') {

			$data['toolbar'] = true;

			if (in_array('feModules', $permissions)) {
				$data['layoutURL'] = static::getBackendURL('themes', 'tl_layout', $GLOBALS['objPage']->layout);
				\System::loadLanguageFile('tl_layout');
				$data['layoutLabel'] = sprintf($GLOBALS['TL_LANG']['tl_layout']['edit'][1], $GLOBALS['objPage']->layout);
			}

			if (in_array('pages', $permissions)) {
				$data['pageURL'] = static::getBackendURL('page', null, $GLOBALS['objPage']->id);
				\System::loadLanguageFile('tl_page');
				$data['pageLabel'] = sprintf($GLOBALS['TL_LANG']['tl_page']['edit'][1], $GLOBALS['objPage']->id);
			}

			$data['previewHideLabel'] =
				$GLOBALS['TL_LANG']['MSC']['hiddenElements'] . ' ' .
				$GLOBALS['TL_LANG']['MSC']['hiddenHide'];
			$data['previewShowLabel'] =
				$GLOBALS['TL_LANG']['MSC']['hiddenElements'] . ' ' .
				$GLOBALS['TL_LANG']['MSC']['hiddenShow'];

			\System::loadLanguageFile('rocksolid_frontend_helper');
			$data['activateLabel'] = $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['activateLabel'];
			$data['deactivateLabel'] = $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['deactivateLabel'];


			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/rocksolid-frontend-helper/assets/js/main.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/rocksolid-frontend-helper/assets/css/main.css';

			$content = explode('<body', $content, 2);
			return $content[0] . static::insertData('<body' . $content[1], $data);

		}

		// get the first tag
		if (preg_match('(<[a-z0-9]+\\s[^>]+)is', $content, $matches)) {
			// search for an article id incected by getArticleHook
			if (preg_match('(\\sclass="[^"]*rsfh-article-([0-9]+)[^"]*")is', $matches[0], $matches2)) {
				$data['toolbar'] = true;
				if (in_array('articles', $permissions)) {
					$data['articleURL'] = static::getBackendURL('article', 'tl_content', $matches2[1], false);
					\System::loadLanguageFile('tl_article');
					$data['articleLabel'] = sprintf($GLOBALS['TL_LANG']['tl_article']['edit'][1], $matches2[1]);
				}
			}
		}

		return static::insertData($content, $data);
	}

	/**
	 * parseWidget hook
	 *
	 * @param  string  $content html content
	 * @param  \Widget $widget  widget object
	 * @return string           modified $content
	 */
	public function parseWidgetHook($content, $widget)
	{
		if (!$permissions = static::checkLogin()) {
			return $content;
		}

		global $page;

		$data = array(
			'toolbar' => true,
		);

		if (in_array('contents', $permissions)) {
			\System::loadLanguageFile('tl_form_field');
			$data['editURL'] = static::getBackendURL('form', 'tl_form_field', $widget->id);
			$data['editLabel'] = sprintf($GLOBALS['TL_LANG']['tl_form_field']['edit'][1], $widget->id);
		}

		if (in_array('infos', $permissions)) {
			$data['template'] = $widget->template;
			$data['templatePath'] = substr($widget->getTemplate(
				$widget->template,
				(TL_MODE === 'FE' && $page->outputFormat) ?
					$page->outputFormat :
					'html5'
			), strlen(TL_ROOT) + 1);
		}

		return static::insertData($content, $data);
	}

	/**
	 * Controller::getArticle hook
	 *
	 * @param  \Database_Result $row module database result
	 * @return void
	 */
	public static function getArticleHook($row)
	{
		if (! static::checkLogin()) {
			return;
		}

		$cssId = deserialize($row->cssID, true);
		$cssId[1] = trim($cssId[1] . ' rsfh-article-' . $row->id);
		$row->cssID = serialize($cssId);
	}

	/**
	 * Controller::getFrontendModule hook
	 *
	 * @param  \Database_Result $row     module database result
	 * @param  string           $content html content
	 * @return string                    modified $content
	 */
	public function getFrontendModuleHook($row, $content)
	{
		if (! $permissions = static::checkLogin()) {
			return $content;
		}

		$data = array(
			'toolbar' => true,
		);

		if (in_array('feModules', $permissions)) {
			\System::loadLanguageFile('tl_module');
			$data['feModuleURL'] = static::getBackendURL('themes', 'tl_module', $row->id);
			$data['feModuleLabel'] = sprintf($GLOBALS['TL_LANG']['tl_module']['edit'][1], $row->id . ' (' . $row->name . ')');
		}

		if (in_array('beModules', $permissions)) {
			foreach ($GLOBALS['TL_RSFH']['backendModules'] as $do => $config) {
				if (
					isset($config['feModules']) &&
					is_array($config['feModules']) &&
					in_array($row->type, $config['feModules'])
				) {
					$id = null;
					if (! empty($config['column']) && ! empty($row->{$config['column']})) {
						$id = $row->{$config['column']};
						if (isset($config['columnType']) && $config['columnType'] === 'serialized') {
							$id = deserialize($id, true);
							if (empty($id[1])) {
								$id = $id[0];
							}
							else {
								$id = null;
							}
						}
					}
					$data['beModuleURL'] = static::getBackendURL(
						$do,
						$id ? $config['table'] : null,
						$id,
						!empty($config['act']) && $id ? $config['act'] : false
					);
					$data['beModuleLabel'] = $this->getBackendModuleLabel($config, $id, empty($config['act']) || !$id);
					$data['beModuleIcon'] = ! empty($config['icon']) ? $config['icon'] : '';
				}
			}
		}

		$blockLevelElements = array(
			'div',
			'section',
			'article',
			'aside',
			'figure',
			'footer',
			'header',
			'hgroup',
			'blockquote',
			'ul',
			'ol',
			'dd',
			'dl',
			'p',
			'pre',
			'form',
			'fieldset',
			'address',
		);
		if (preg_match('(^\\s*<(?:' . implode('|', $blockLevelElements) . ')(?:\\s[^>]+|)>\\s*$)is', $content)) {
			// Disable the toolbar for wrapper modules
			unset($data['toolbar']);
		}

		return static::insertData($content, $data);
	}

	/**
	 * getContentElement hook
	 *
	 * @param  Object $row     content element
	 * @param  string $content html content
	 * @return string          modified $content
	 */
	public function getContentElementHook($row, $content, $element)
	{
		if (! $permissions = static::checkLogin()) {
			return $content;
		}

		$data = array(
			'toolbar' => true,
		);

		if (in_array('contents', $permissions)) {

			$do = 'article';
			if ($row->ptable) {
				foreach ($GLOBALS['BE_MOD'] as $category) {
					foreach ($category as $moduleName => $module) {
						if (
							! empty($module['tables']) &&
							in_array($row->ptable, $module['tables']) &&
							in_array('tl_content', $module['tables'])
						) {
							$do = $moduleName;
							break 2;
						}
					}
				}
			}

			\System::loadLanguageFile('tl_content');
			$data['editURL'] = static::getBackendURL($do, 'tl_content', $row->id);
			$data['editLabel'] = sprintf($GLOBALS['TL_LANG']['tl_content']['edit'][1], $row->id);

		}

		if ($row->type === 'module' && $row->module) {
			$moduleRow = \ModuleModel::findByPk($row->module);
			if ($moduleRow) {
				$content = $this->getFrontendModuleHook($moduleRow, $content);
			}
		}
		else if (in_array('beModules', $permissions)) {
			foreach ($GLOBALS['TL_RSFH']['backendModules'] as $do => $config) {
				if (
					isset($config['contentElements']) &&
					is_array($config['contentElements']) &&
					in_array($row->type, $config['contentElements'])
				) {
					$id = null;
					if (! empty($config['ceColumn']) && ! empty($row->{$config['ceColumn']})) {
						$id = $row->{$config['ceColumn']};
						if (isset($config['ceColumnType']) && $config['ceColumnType'] === 'serialized') {
							$id = deserialize($id, true);
							if (empty($id[1])) {
								$id = $id[0];
							}
							else {
								$id = null;
							}
						}
					}
					$data['beModuleURL'] = static::getBackendURL(
						$do,
						$id ? $config['table'] : null,
						$id,
						! empty($config['act']) && $id ? $config['act'] : false
					);
					$data['beModuleLabel'] = $this->getBackendModuleLabel($config, $id, empty($config['act']) || !$id);
					$data['beModuleIcon'] = ! empty($config['icon']) ? $config['icon'] : '';
				}
			}
		}

		return static::insertData($content, $data);
	}

	/**
	 * loadDataContainer hook
	 *
	 * Saves the referrer in the session if it is a frontend URL
	 *
	 * @param  string $table The data container table name
	 * @return void
	 */
	public function loadDataContainerHook($table)
	{
		if (TL_MODE !== 'BE') {
			return;
		}

		$base = \Environment::get('path') . '/contao/';
		$referrer = parse_url(\Environment::get('httpReferer'));
		$referrer = $referrer['path'] . ($referrer['query'] ? '?' . $referrer['query'] : '');

		// Stop if the referrer is a backend URL
		if (substr($referrer, 0, strlen($base)) === $base) {
			return;
		}

		// Make homepage possible as referrer
		if ($referrer === \Environment::get('path') . '/') {
			$referrer .= '?';
		}

		// set the frontend URL as referrer
		$referrerSession = \Session::getInstance()->get('referer');

		if (defined('TL_REFERER_ID') && !\Input::get('ref')) {

			$referrer = substr($referrer, strlen(TL_PATH) + 1);
			$tlRefererId = substr(md5(TL_START - 1), 0, 8);
			$referrerSession[$tlRefererId]['current'] = $referrer;
			\Input::setGet('ref', $tlRefererId);
			$requestUri = \Environment::get('requestUri');
			$requestUri .= (strpos($requestUri, '?') === false ? '?' : '&') . 'ref=' . $tlRefererId;
			\Environment::set('requestUri', $requestUri);

		}
		// Backwards compatibility for Contao 3.0
		else if (!defined('TL_REFERER_ID')) {
			$referrerSession['current'] = $referrer;
		}

		\Session::getInstance()->set('referer', $referrerSession);
	}

	/**
	 * checks if a Backend User is logged in
	 *
	 * @return array|boolean false if the user isn't logged in otherwise the permissions array
	 */
	public static function checkLogin()
	{
		// Do not create a user instance if there is no authentication cookie
		if (! \Input::cookie('BE_USER_AUTH') || TL_MODE !== 'FE') {
			return false;
		}

		$User = FrontendHelperUser::getInstance();

		if (! $User->authenticate()) {
			return false;
		}

		if (! $User->rocksolidFrontendHelper) {
			return false;
		}

		if ($User->isAdmin) {
			return array('feModules', 'beModules', 'pages', 'articles', 'contents', 'infos');
		}

		if (count($User->rocksolidFrontendHelperOperations)) {
			return $User->rocksolidFrontendHelperOperations;
		}

		return false;
	}

	/**
	 * get the label for a backend module link
	 *
	 * @param  array   $config          backend module configuration from $GLOBALS['TL_RSFH']
	 * @param  int     $id              id of the entry to edit
	 * @param  boolean $withParentTable use the edit label from the parent table if possible
	 * @return string                   the label
	 */
	public function getBackendModuleLabel($config, $id = null, $withParentTable = false)
	{
		if ($withParentTable) {
			$this->loadDataContainer($config['table']);
			if (!empty($GLOBALS['TL_DCA'][$config['table']]['config']['ptable'])) {
				$ptable = $GLOBALS['TL_DCA'][$config['table']]['config']['ptable'];
				\System::loadLanguageFile($ptable);
				if ($id && !empty($GLOBALS['TL_LANG'][$ptable]['edit'][1])) {
					return sprintf($GLOBALS['TL_LANG'][$ptable]['edit'][1], $id);
				}
				if (!empty($GLOBALS['TL_LANG'][$ptable]['edit'][0])) {
					return $GLOBALS['TL_LANG'][$ptable]['edit'][0];
				}
				if (!empty($GLOBALS['TL_LANG'][$ptable]['editheader'][0])) {
					return $GLOBALS['TL_LANG'][$ptable]['editheader'][0];
				}
			}
		}
		\System::loadLanguageFile($config['table']);
		if (!empty($GLOBALS['TL_LANG'][$config['table']]['editheader'][0])) {
			return $GLOBALS['TL_LANG'][$config['table']]['editheader'][0];
		}
		if ($id && !empty($GLOBALS['TL_LANG'][$config['table']]['edit'][1])) {
			return sprintf($GLOBALS['TL_LANG'][$config['table']]['edit'][1], $id);
		}
		if (!empty($GLOBALS['TL_LANG'][$config['table']]['edit'][0])) {
			return $GLOBALS['TL_LANG'][$config['table']]['edit'][0];
		}
		if ($id) {
			return sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $id);
		}
		return $GLOBALS['TL_LANG']['MSC']['editElement'];
	}

	/**
	 * create backend edit URL
	 *
	 * @param  string $do
	 * @param  string $table
	 * @param  string $id
	 * @param  string $act
	 * @return string
	 */
	protected static function getBackendURL($do, $table, $id, $act = 'edit')
	{
		return 'contao/main.php'
			. '?do=' . $do
			. ($table ? '&table=' . $table : '')
			. ($act ? '&act=' . $act : '')
			. ($id ? '&id=' .  $id : '')
			. '&rt=' . REQUEST_TOKEN;
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
