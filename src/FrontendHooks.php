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
 * RockSolid Frontend Helper
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class FrontendHooks
{
	/**
	 * @var array
	 */
	private $backendModules = [];

	/**
	 * @param array $backendModules Backend modules configuration array
	 */
	public function __construct(array $backendModules = [])
	{
		$this->backendModules = $backendModules;
	}
	/**
	 * parseFrontendTemplate hook
	 *
	 * @param  string $content  html content
	 * @param  string $template template name
	 * @return string           modified $content
	 */
	public function parseFrontendTemplateHook($content, $template)
	{
		if (!($permissions = static::checkLogin()) || !$template) {
			return $content;
		}

		$data = array();

		if (in_array('infos', $permissions)) {
			$data = array(
				'template' => $template,
				'templatePath' => substr(\Controller::getTemplate($template), strlen(TL_ROOT) + 1),
			);
			if (in_array('tpl_editor', $permissions)) {
				$data = static::addTemplateURL($data);
			}
		}

		// get the first tag
		if (preg_match('(<[a-z0-9]+\\s(?>"[^"]*"|\'[^\']*\'|[^>"\'])+)is', $content, $matches)) {

			// search for an article id injected by getArticleHook
			if (preg_match('(^(.*\\sclass="[^"]*)rsfh-article-([0-9]+)-([0-9a-f]*)(.*)$)is', $matches[0], $matches2)) {

				$data['toolbar'] = true;
				// remove the article id class
				$content = str_replace($matches2[0], $matches2[1] . $matches2[4], $content);

				if (in_array('articles', $permissions)) {
					\System::loadLanguageFile('tl_article');
					$data['links']['article'] = array(
						'url' => static::getBackendURL('article', 'tl_content', $matches2[2], false),
						'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_article']['edit']) ? $GLOBALS['TL_LANG']['tl_article']['edit'][1] : $GLOBALS['TL_LANG']['tl_article']['edit'], $matches2[2]),
					);
				}

				if (in_array('contents', $permissions)) {

					\System::loadLanguageFile('tl_content');
					$data['links']['pastenew'] = array(
						'url' => static::getBackendURL('article', 'tl_content', $matches2[2], 'create', array('mode' => 2, 'pid' => $matches2[2])),
						'label' => $GLOBALS['TL_LANG']['tl_content']['pastenew'][0],
					);

					$lastContentElement = \Database::getInstance()
						->prepare("SELECT id FROM tl_content WHERE pid=? AND (ptable IS NULL OR ptable = '' OR ptable = 'tl_article') ORDER BY sorting DESC")
						->limit(1)
						->execute($matches2[2]);
					if ($lastContentElement && $lastContentElement->id) {
						$data['links']['pastebottom'] = array(
							'url' => static::getBackendURL('article', 'tl_content', $matches2[2], 'create', array('mode' => 1, 'pid' => $lastContentElement->id)),
							'label' => sprintf($GLOBALS['TL_LANG']['tl_content']['pastenew'][1], $lastContentElement->id),
						);
					}

					\System::loadLanguageFile('rocksolid_frontend_helper');
					$data['columnLabel'] = $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['column'];
					$data['column'] = pack("H*" , $matches2[3]);
					if (isset($GLOBALS['TL_LANG']['COLS'][$data['column']])) {
						$data['column'] = $GLOBALS['TL_LANG']['COLS'][$data['column']];
					}

				}

			}

			// search for a news id injected by parseArticlesHook
			if (preg_match('(^(.*\\sclass="[^"]*)rsfh-news-([0-9]+)(.*)$)is', $matches[0], $matches2)) {

				$data['toolbar'] = true;
				// remove the news id class
				$content = str_replace($matches2[0], $matches2[1] . $matches2[3], $content);

				if (in_array('beModules', $permissions)) {
					\System::loadLanguageFile('tl_news');
					$data['links']['be-module'] = array(
						'url' => static::getBackendURL('news', 'tl_content', $matches2[2], false),
						'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_news']['edit']) ? $GLOBALS['TL_LANG']['tl_news']['edit'][1] : $GLOBALS['TL_LANG']['tl_news']['edit'], $matches2[2]),
						'icon' => \Image::getPath('news.svg'),
					);
				}

			}

			// search for an event id injected by getAllEventsHook
			if (preg_match('(^(.*\\sclass="[^"]*)rsfh-event-([0-9]+)(.*)$)is', $matches[0], $matches2)) {

				$data['toolbar'] = true;
				// remove the news id class
				$content = str_replace($matches2[0], $matches2[1] . $matches2[3], $content);

				if (in_array('beModules', $permissions)) {
					\System::loadLanguageFile('tl_calendar');
					$data['links']['be-module'] = array(
						'url' => static::getBackendURL('calendar', 'tl_content', $matches2[2], false),
						'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_calendar']['edit']) ? $GLOBALS['TL_LANG']['tl_calendar']['edit'][1] : $GLOBALS['TL_LANG']['tl_calendar']['edit'], $matches2[2]),
						'icon' => \Image::getPath('settings.svg'),
					);
				}

			}

			// search for a comment id injected by parseTemplateHook
			if (preg_match('(^(.*\\sclass="[^"]*)rsfh-comment-([0-9]+)(.*)$)is', $matches[0], $matches2)) {

				$data['toolbar'] = true;
				// remove the news id class
				$content = str_replace($matches2[0], $matches2[1] . $matches2[3], $content);

				if (in_array('beModules', $permissions)) {
					\System::loadLanguageFile('tl_comments');
					$data['links']['be-module'] = array(
						'url' => static::getBackendURL('comments', null, $matches2[2]),
						'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_comments']['edit']) ? $GLOBALS['TL_LANG']['tl_comments']['edit'][1] : $GLOBALS['TL_LANG']['tl_comments']['edit'], $matches2[2]),
						'icon' => \Image::getPath('settings.svg'),
					);
				}

			}

			// search for a mega-menu id injected by parseTemplateHook
			if (preg_match('(^(.*\\sclass="[^"]*)rsfh-mega-menu-([0-9]+)(.*)$)is', $matches[0], $matches2)) {

				$data['toolbar'] = true;
				// remove the news id class
				$content = str_replace($matches2[0], $matches2[1] . $matches2[3], $content);

				if (in_array('beModules', $permissions)) {
					\System::loadLanguageFile('tl_rocksolid_mega_menu');
					$data['links']['be-module'] = array(
						'url' => static::getBackendURL('rocksolid_mega_menu', 'tl_rocksolid_mega_menu_column', $matches2[2], false),
						'label' => sprintf($GLOBALS['TL_LANG']['tl_rocksolid_mega_menu']['edit'][1], $matches2[2]),
						'icon' => $GLOBALS['BE_MOD']['design']['rocksolid_mega_menu']['icon'],
					);
				}

			}

		}

		return static::insertData($content, $data);
	}

	/**
	 * outputFrontendTemplate hook
	 *
	 * @param  string $content  html content
	 * @param  string $template template name
	 * @return string           modified $content
	 */
	public function outputFrontendTemplateHook($content, $template)
	{
		if (
			!($permissions = static::checkLogin())
			|| !$template
			|| substr($template, 0, 3) !== 'fe_'
		) {
			return $content;
		}

		$data = array(
			'toolbar' => true,
		);

		\System::loadLanguageFile('rocksolid_frontend_helper');

		if (in_array('pages', $permissions)) {
			\System::loadLanguageFile('tl_page');
			$data['links']['page'] = array(
				'url' => static::getBackendURL('page', null, $GLOBALS['objPage']->id),
				'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_page']['edit']) ? $GLOBALS['TL_LANG']['tl_page']['edit'][1] : $GLOBALS['TL_LANG']['tl_page']['edit'], $GLOBALS['objPage']->id),
			);
		}

		if (in_array('articles', $permissions)) {
			\System::loadLanguageFile('tl_page');
			$data['links']['article'] = array(
				'url' => static::getBackendURL('article', null, null, null, array(
					'pn' => $GLOBALS['objPage']->id,
				)),
				'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_page']['articles']) ? $GLOBALS['TL_LANG']['tl_page']['articles'][1] : $GLOBALS['TL_LANG']['tl_page']['articles'], $GLOBALS['objPage']->id),
			);
		}

		if (in_array('feModules', $permissions)) {
			\System::loadLanguageFile('tl_layout');
			$data['links']['layout'] = array(
				'url' => static::getBackendURL('themes', 'tl_layout', $GLOBALS['objPage']->layout),
				'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_layout']['edit']) ? $GLOBALS['TL_LANG']['tl_layout']['edit'][1] : $GLOBALS['TL_LANG']['tl_layout']['edit'], $GLOBALS['objPage']->layout),
			);
			if ($GLOBALS['objPage']->getRelated('layout') && $GLOBALS['objPage']->getRelated('layout')->pid) {
				if ($GLOBALS['objPage']->getRelated('layout')->name) {
					$data['links']['layout']['label'] .= ' (' . $GLOBALS['objPage']->getRelated('layout')->name . ')';
				}
				\System::loadLanguageFile('tl_theme');
				$data['links']['fe-module'] = array(
					'url' => static::getBackendURL('themes', 'tl_module', $GLOBALS['objPage']->getRelated('layout')->pid, null),
					'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_theme']['modules']) ? $GLOBALS['TL_LANG']['tl_theme']['modules'][1] : $GLOBALS['TL_LANG']['tl_theme']['modules'], $GLOBALS['objPage']->getRelated('layout')->pid),
				);
				$data['links']['image-size'] = array(
					'url' => static::getBackendURL('themes', 'tl_image_size', $GLOBALS['objPage']->getRelated('layout')->pid, null),
					'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_theme']['imageSizes']) ? $GLOBALS['TL_LANG']['tl_theme']['imageSizes'][1] : $GLOBALS['TL_LANG']['tl_theme']['imageSizes'], $GLOBALS['objPage']->getRelated('layout')->pid),
				);
				if (
					$GLOBALS['objPage']->getRelated('layout')->stylesheet &&
					count(\StringUtil::deserialize($GLOBALS['objPage']->getRelated('layout')->stylesheet))
				) {
					// Only show a stylesheets link if stylesheets are used
					$data['links']['stylesheet'] = array(
						'url' => static::getBackendURL('themes', 'tl_style_sheet', $GLOBALS['objPage']->getRelated('layout')->pid, null),
						'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_theme']['css']) ? $GLOBALS['TL_LANG']['tl_theme']['css'][1] : $GLOBALS['TL_LANG']['tl_theme']['css'], $GLOBALS['objPage']->getRelated('layout')->pid),
					);
				}
			}
		}

		if (in_array('rstAssistant', $permissions) && $assistantId = static::getThemeAssistantStylesheet()) {
			$data['links']['assistant'] = array(
				'url' => static::getBackendURL('rocksolid_theme_assistant', null, $assistantId),
				'label' => $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['assistantLabel'],
			);
		}

		$data['links']['backend'] = array(
			'url' => static::getBackendURL(null, null, null, null),
			'label' => $GLOBALS['TL_LANG']['MSC']['homeTitle'],
		);

		$data['labels'] = array(
			'activate' => $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['activateLabel'],
			'deactivate' => $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['deactivateLabel'],
			'cancel' => $GLOBALS['TL_LANG']['MSC']['cancelBT'],
		);

		$previewEnabled = \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN;
		$data['config'] = array(
			'lightbox' => (bool)FrontendHelperUser::getInstance()->rocksolidFrontendHelperLightbox,
			'beSwitch' => array(
				'label' => $GLOBALS['TL_LANG']['MSC']['hiddenElements'] . ': ' . $GLOBALS['TL_LANG']['MSC'][$previewEnabled ? 'hiddenHide' : 'hiddenShow'],
				'url' => \System::getContainer()->get('router')->generate('contao_backend_switch'),
				'data' => array(
					'FORM_SUBMIT' => 'tl_switch',
					'REQUEST_TOKEN' => REQUEST_TOKEN,
					'unpublished' => $previewEnabled ? 'hide' : 'show',
				),
			),
		);

		$assetsDir = 'bundles/rocksolidfrontendhelper';

		$GLOBALS['TL_JAVASCRIPT'][] = $assetsDir . '/js/main.js';
		$GLOBALS['TL_CSS'][] = $assetsDir . '/css/main.css';

		// Remove dummy elements inside script tags and insert them before the script tags
		$content = preg_replace_callback('(<script(?>"[^"]*"|\'[^\']*\'|[^>"\'])*>.*?</script>)is', function($matches) {
			preg_match_all('(<span class="rsfh-dummy[^>]*></span>)is', $matches[0], $dummies);
			if (count($dummies[0])) {
				$matches[0] = str_replace($dummies[0], '', $matches[0]);
			}
			preg_match_all('( data-frontend-helper="[^"]*")is', $matches[0], $attributes);
			if (count($attributes[0])) {
				$matches[0] = '<span class="rsfh-dummy"' . implode(
					'></span><span class="rsfh-dummy"',
					$attributes[0]
				) . '></span>' . str_replace($attributes[0], '', $matches[0]);
			}
			if (count($dummies[0])) {
				$matches[0] = implode('', $dummies[0]) . $matches[0];
			}
			return $matches[0];
		}, $content);

		return static::insertData($content, $data);
	}

	/**
	 * parseTemplate hook
	 *
	 * @param  \Template $template
	 * @return void
	 */
	public function parseTemplateHook($template)
	{
		if (!$permissions = static::checkLogin()) {
			return;
		}

		if (in_array('beModules', $permissions)) {

			if (
				substr($template->getName(), 0, 4) === 'com_'
				&& substr($template->id, 0, 1) === 'c'
			) {
				$template->class .= ' rsfh-comment-' . substr($template->id, 1);
			}

			if (
				substr($template->getName(), 0, 5) === 'rsmm_'
				&& !empty($template->id)
			) {
				$template->cssClass .= ' rsfh-mega-menu-' . $template->id;
			}

		}
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

		if (in_array('contents', $permissions) && is_numeric($widget->id) && is_numeric($widget->pid)) {
			\System::loadLanguageFile('tl_form_field');
			$data['links']['edit'] = array(
				'url' => static::getBackendURL('form', 'tl_form_field', $widget->id),
				'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_form_field']['edit']) ? $GLOBALS['TL_LANG']['tl_form_field']['edit'][1] : $GLOBALS['TL_LANG']['tl_form_field']['edit'], $widget->id),
			);
			$data['links']['delete'] = array(
				'url' => static::getBackendURL('form', 'tl_form_field', $widget->id, 'delete'),
				'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_form_field']['delete']) ? $GLOBALS['TL_LANG']['tl_form_field']['delete'][1] : $GLOBALS['TL_LANG']['tl_form_field']['delete'], $widget->id),
				'confirm' => sprintf($GLOBALS['TL_LANG']['MSC']['deleteConfirm'], $widget->id),
			);
			$data['links']['pastenew'] = array(
				'url' => static::getBackendURL('form', 'tl_form_field', $widget->pid, 'create', array('mode' => 1, 'pid' => $widget->id)),
				'label' => sprintf($GLOBALS['TL_LANG']['tl_form_field']['pastenew'][1], $widget->id),
			);
		}

		if (
			in_array('infos', $permissions)
			&& $widget->template !== 'form_rsce_plain'
			&& $widget->template !== 'form_rs_columns_plain'
		) {
			$data['template'] = $widget->template;
			$data['templatePath'] = substr($widget->getTemplate(
				$widget->template,
				(TL_MODE === 'FE' && $page->outputFormat) ?
					$page->outputFormat :
					'html5'
			), strlen(TL_ROOT) + 1);
			if (in_array('tpl_editor', $permissions)) {
				$data = static::addTemplateURL($data);
			}
		}

		return static::insertData($content, $data);
	}

	/**
	 * parseArticles hook
	 *
	 * @param  \FrontendTemplate $template
	 * @param  \Widget           $row
	 * @param  \ModuleNews       $module
	 * @return void
	 */
	public function parseArticlesHook($template, $row, $module)
	{
		if (!($permissions = static::checkLogin()) || !in_array('beModules', $permissions)) {
			return;
		}
		$template->class .= ' rsfh-news-' . $row['id'];
	}

	/**
	 * getAllEvents hook
	 *
	 * @param  array   $allEvents
	 * @param  array   $calendars
	 * @param  int     $start
	 * @param  int     $end
	 * @param  \Events $module
	 * @return array              Modified $allEvents array
	 */
	public function getAllEventsHook($allEvents, $calendars, $start, $end, $module)
	{
		if (!($permissions = static::checkLogin()) || !in_array('beModules', $permissions)) {
			return $allEvents;
		}

		foreach ($allEvents as $key => $days) {
			foreach ($days as $day => $events) {
				foreach ($events as $index => $event) {
					$allEvents[$key][$day][$index]['class'] =
						(empty($event['class']) ? '' : $event['class'])
						. ' rsfh-event-' . $event['id'];
				}
			}
		}

		return $allEvents;
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

		$cssId = \StringUtil::deserialize($row->cssID, true);
		$cssId[1] = trim($cssId[1] . ' rsfh-article-' . $row->id . '-' . bin2hex($row->inColumn ?: ''));
		$row->cssID = serialize($cssId);
	}

	/**
	 * Controller::getFrontendModule hook
	 *
	 * @param  \Database_Result $row     module database result
	 * @param  string           $content html content
	 * @param  \Module          $module  module instance
	 * @return string                    modified $content
	 */
	public function getFrontendModuleHook($row, $content, $module = null)
	{
		if (! $content || ! $permissions = static::checkLogin()) {
			return $content;
		}

		$data = array(
			'toolbar' => true,
		);

		if (
			is_object($module) &&
			isset($module->Template) &&
			is_object($module->Template) &&
			isset($module->Template->inColumn) &&
			$module->Template->inColumn
		) {
			\System::loadLanguageFile('rocksolid_frontend_helper');
			$data['columnLabel'] = $GLOBALS['TL_LANG']['rocksolid_frontend_helper']['column'];
			$data['column'] = $module->Template->inColumn;
			\System::loadLanguageFile('tl_article');
			if (isset($GLOBALS['TL_LANG']['COLS'][$data['column']])) {
				$data['column'] = $GLOBALS['TL_LANG']['COLS'][$data['column']];
			}
		}

		if (in_array('feModules', $permissions)) {
			\System::loadLanguageFile('tl_module');
			$data['links']['fe-module'] = array(
				'url' => static::getBackendURL('themes', 'tl_module', $row->id),
				'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_module']['edit']) ? $GLOBALS['TL_LANG']['tl_module']['edit'][1] : $GLOBALS['TL_LANG']['tl_module']['edit'], $row->id . ' (' . $row->name . ')'),
			);
		}

		if (in_array('beModules', $permissions)) {
			foreach ($this->backendModules as $do => $config) {
				if (!empty($config['do'])) {
					$do = $config['do'];
				}
				if (
					isset($config['fe_modules']) &&
					is_array($config['fe_modules']) &&
					in_array($row->type, $config['fe_modules'])
				) {
					$id = null;
					if (! empty($config['column']) && ! empty($row->{$config['column']})) {
						$id = $row->{$config['column']};
						if (isset($config['column_type']) && $config['column_type'] === 'serialized') {
							$id = \StringUtil::deserialize($id, true);
							if (empty($id[1])) {
								$id = $id[0];
							}
							else {
								$id = null;
							}
						}
					}
					$data['links']['be-module'] = array(
						'url' => static::getBackendURL(
							$do,
							$id ? $config['table'] : null,
							$id,
							!empty($config['act']) && $id ? $config['act'] : false
						),
						'label' => $this->getBackendModuleLabel($config, $id, empty($config['act']) || !$id),
						'icon' => empty($config['icon']) ? '' : \Image::getPath($config['icon']),
					);
				}
			}
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

			// Compatibility for ce-access extension
			$editAllowed = true;
			if (
				class_exists('CeAccess')
				&& !FrontendHelperUser::getInstance()->isAdmin
				&& !in_array($row->type, (array)FrontendHelperUser::getInstance()->elements)
			) {
				$editAllowed = false;
			}

			\System::loadLanguageFile('tl_content');

			if ($editAllowed) {
				$data['links']['edit'] = array(
					'url' => static::getBackendURL($do, 'tl_content', $row->id),
					'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_content']['edit']) ? $GLOBALS['TL_LANG']['tl_content']['edit'][1] : $GLOBALS['TL_LANG']['tl_content']['edit'], $row->id),
				);
				$data['links']['delete'] = array(
					'url' => static::getBackendURL($do, 'tl_content', $row->id, 'delete'),
					'label' => sprintf(is_array($GLOBALS['TL_LANG']['tl_content']['delete']) ? $GLOBALS['TL_LANG']['tl_content']['delete'][1] : $GLOBALS['TL_LANG']['tl_content']['delete'], $row->id),
					'confirm' => sprintf($GLOBALS['TL_LANG']['MSC']['deleteConfirm'], $row->id),
				);
			}

			$data['links']['pastenew'] = array(
				'url' => static::getBackendURL($do, 'tl_content', $row->pid, 'create', array('mode' => 1, 'pid' => $row->id)),
				'label' => sprintf($GLOBALS['TL_LANG']['tl_content']['pastenew'][1], $row->id),
			);

		}

		if ($row->type === 'module' && $row->module) {
			$moduleRow = \ModuleModel::findByPk($row->module);
			if ($moduleRow) {
				$content = $this->getFrontendModuleHook($moduleRow, $content);
			}
		}
		else if (in_array('beModules', $permissions)) {
			foreach ($this->backendModules as $do => $config) {
				if (!empty($config['do'])) {
					$do = $config['do'];
				}
				if (
					isset($config['content_elements']) &&
					is_array($config['content_elements']) &&
					in_array($row->type, $config['content_elements'])
				) {
					$id = null;
					if (! empty($config['ce_column']) && ! empty($row->{$config['ce_column']})) {
						$id = $row->{$config['ce_column']};
						if (isset($config['ce_column_type']) && $config['ce_column_type'] === 'serialized') {
							$id = \StringUtil::deserialize($id, true);
							if (empty($id[1])) {
								$id = $id[0];
							}
							else {
								$id = null;
							}
						}
					}
					$data['links']['be-module'] = array(
						'url' => static::getBackendURL(
							$do,
							$id ? $config['table'] : null,
							$id,
							! empty($config['act']) && $id ? $config['act'] : false
						),
						'label' => $this->getBackendModuleLabel($config, $id, empty($config['act']) || !$id),
						'icon' => empty($config['icon']) ? '' : \Image::getPath($config['icon']),
					);
				}
			}
		}

		return static::insertData($content, $data);
	}

	/**
	 * checks if a Backend User is logged in
	 *
	 * @return array|boolean false if the user isn't logged in otherwise the permissions array
	 */
	public static function checkLogin()
	{
		// Only try to authenticate in the front end
		if (TL_MODE !== 'FE') {
			return false;
		}

		// Do not create a user instance if there is no authentication cookie
		if (! is_subclass_of('BackendUser', UserInterface::class) && ! \Input::cookie('BE_USER_AUTH')) {
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
			return array('feModules', 'beModules', 'pages', 'articles', 'contents', 'infos', 'rstAssistant', 'tpl_editor');
		}

		$permissions = array();
		if (is_array($User->rocksolidFrontendHelperOperations)) {
			$permissions = array_merge($permissions, $User->rocksolidFrontendHelperOperations);
		}
		if ($User->hasAccess('rocksolid_theme_assistant', 'modules')) {
			$permissions[] = 'rstAssistant';
		}
		if ($User->hasAccess('tpl_editor', 'modules')) {
			$permissions[] = 'tpl_editor';
		}

		if (count($permissions)) {
			return $permissions;
		}

		return false;
	}

	/**
	 * get the label for a backend module link
	 *
	 * @param  array   $config          backend module configuration
	 * @param  int     $id              id of the entry to edit
	 * @param  boolean $withParentTable use the edit label from the parent table if possible
	 * @return string                   the label
	 */
	public function getBackendModuleLabel($config, $id = null, $withParentTable = false)
	{
		if ($withParentTable) {
			\Controller::loadDataContainer($config['table']);
			if (!empty($GLOBALS['TL_DCA'][$config['table']]['config']['ptable'])) {
				$ptable = $GLOBALS['TL_DCA'][$config['table']]['config']['ptable'];
				\System::loadLanguageFile($ptable);
				if ($id && !empty($GLOBALS['TL_LANG'][$ptable]['edit'][1])) {
					return sprintf(is_array($GLOBALS['TL_LANG'][$ptable]['edit']) ? $GLOBALS['TL_LANG'][$ptable]['edit'][1] : $GLOBALS['TL_LANG'][$ptable]['edit'], $id);
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
			return sprintf(is_array($GLOBALS['TL_LANG'][$config['table']]['edit']) ? $GLOBALS['TL_LANG'][$config['table']]['edit'][1] : $GLOBALS['TL_LANG'][$config['table']]['edit'], $id);
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
	 * Add templateURL and templateLabel to data array
	 *
	 * @param  array $data data array
	 * @return array       modified data array
	 */
	protected static function addTemplateURL($data)
	{
		if (substr($data['templatePath'], 0, 10) === 'templates/') {

			$data['templateURL'] = static::getBackendURL('tpl_editor', null, $data['templatePath'], 'source');

			\System::loadLanguageFile('tl_files');
			$data['templateLabel'] = sprintf(is_array($GLOBALS['TL_LANG']['tl_files']['source']) ? $GLOBALS['TL_LANG']['tl_files']['source'][1] : $GLOBALS['TL_LANG']['tl_files']['source'], basename($data['templatePath']));

		}
		else {

			$data['templateURL'] = static::getBackendURL('tpl_editor', null, null, null, array(
				'key' => 'new_tpl',
				'original' => $data['templatePath'],
				'target' => $GLOBALS['objPage']->templateGroup ?: 'templates',
			));

			\System::loadLanguageFile('tl_templates');
			$data['templateLabel'] = $GLOBALS['TL_LANG']['tl_templates']['new_tpl'][1];

		}

		return $data;
	}

	/**
	 * Get path to the .css.base file of the current layout
	 *
	 * @return string|null Path to the .css.base file or null
	 */
	protected function getThemeAssistantStylesheet()
	{
		if (!$GLOBALS['objPage'] || !$GLOBALS['objPage']->getRelated('layout')) {
			return null;
		}
		if (!$stylesheets = $GLOBALS['objPage']->getRelated('layout')->external) {
			return null;
		}

		$stylesheets = \StringUtil::deserialize($stylesheets);
		foreach ($stylesheets as $stylesheet) {
			$file = \FilesModel::findByUuid($stylesheet);
			if ($file && $file->path && file_exists(TL_ROOT . '/' . $file->path . '.base')) {
				return $file->path . '.base';
			}
		}

		return null;
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
	protected static function getBackendURL($do, $table, $id, $act = 'edit', array $params = array())
	{
		$addParams = array();
		foreach (array('do', 'table', 'act', 'id') as $key) {
			if ($$key) {
				$addParams[$key] = $$key;
			}
		}

		// This is necessary because Contao wants the parameters to be in the right order.
		// E.g. `?node=2&do=article` doesn’t work while `?do=article&node=2` does.
		$params = array_merge($addParams, $params);

		$params['rt'] = REQUEST_TOKEN;
		$params['rsfhr'] = 1;

		$url = \System::getContainer()->get('router')->generate('contao_backend');

		// Third parameter is required because of arg_separator.output
		$url .= '?' . http_build_query($params, null, '&');

		return $url;
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
		if (
			preg_match('(^.*?(?:<div class="rs-column\\s[^"]*">)?.*?<([a-z0-9]+)(?:\\s(?>"[^"]*"|\'[^\']*\'|[^>"\'])+|))is', $content, $matches)
			&& $matches[1] !== 'esi'
		) {

			if ($matches[1] === 'html' && strpos($content, '<body') !== -1) {
				$content = explode('<body', $content, 2);
				return $content[0] . static::insertData('<body' . $content[1], $data);
			}

			$content = substr($content, strlen($matches[0]));

			if (preg_match('(\\sdata-frontend-helper="([^"]*)")is', $matches[0], $matches2)) {
				$oldData = json_decode(html_entity_decode($matches2[1]), true);
				if (!is_array($oldData)) {
					$oldData = array();
				}
				if (isset($oldData['links']) && isset($data['links'])) {
					$data['links'] = array_merge($oldData['links'], $data['links']);
				}
				$data = array_merge($oldData, $data);
				$matches[0] = preg_replace('(\\sdata-frontend-helper="([^"]*)")is', '', $matches[0]);
			}

			return $matches[0] . ' data-frontend-helper="' . htmlspecialchars(json_encode($data)) . '"' . $content;
		}

		return '<span class="rsfh-dummy" data-frontend-helper="' . htmlspecialchars(json_encode($data)) . '"></span>' . $content;
	}
}
