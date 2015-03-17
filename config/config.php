<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RockSolid Frontend Helper configuration
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */

$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'parseFrontendTemplateHook');
$GLOBALS['TL_HOOKS']['parseTemplate'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'parseTemplateHook');
$GLOBALS['TL_HOOKS']['parseWidget'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'parseWidgetHook');
$GLOBALS['TL_HOOKS']['parseArticles'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'parseArticlesHook');
$GLOBALS['TL_HOOKS']['getAllEvents'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'getAllEventsHook');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'getContentElementHook');
$GLOBALS['TL_HOOKS']['getFrontendModule'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'getFrontendModuleHook');
$GLOBALS['TL_HOOKS']['getArticle'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'getArticleHook');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('MadeYourDay\\Contao\\FrontendHelper', 'loadDataContainerHook');

$GLOBALS['TL_PERMISSIONS'][] = 'rocksolidFrontendHelperOperations';

/* backend modules configuration
// use the same name as in $GLOBALS['BE_MOD']
$GLOBALS['TL_RSFH']['backendModules']['myextension'] = array(
	'table' => 'tl_my_extension',   // table to edit
	'act' => 'edit',                // act parameter for backend link
	'column' => 'my_extension_id',  // the column of tl_module that holds the id of the record to edit
	'columnType' => 'plain',        // 'serialized' or 'plain'
	'ceColumn' => 'my_extensions',  // the column of tl_content that holds the id of the record to edit
	'ceColumnType' => 'serialized', // 'serialized' or 'plain'
	'icon' => 'path/to/icon.gif',   // path to edit icon (e.g. system/modules/dlh_googlemaps/assets/icon.gif)
	'feModules' => array(           // all frontend modules that should be editable
		'my_extension',
	),
	'contentElements' => array(     // all content elements that should be editable
		'my_extension',
	),
);
 */
$GLOBALS['TL_RSFH']['backendModules']['news'] = array(
	'table' => 'tl_news',
	'column' => 'news_archives',
	'columnType' => 'serialized',
	'icon' => 'system/themes/default/images/news.gif',
	'feModules' => array(
		'newslist',
		'newsreader',
		'newsarchive',
		'newsmenu',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['calendar'] = array(
	'table' => 'tl_calendar_events',
	'column' => 'cal_calendar',
	'columnType' => 'serialized',
	'icon' => 'system/modules/calendar/assets/icon.gif',
	'feModules' => array(
		'calendar',
		'eventreader',
		'eventlist',
		'eventmenu',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['newsletter'] = array(
	'table' => 'tl_newsletter',
	'column' => 'nl_channels',
	'columnType' => 'serialized',
	'icon' => 'system/modules/newsletter/assets/icon.gif',
	'feModules' => array(
		'nl_list',
		'nl_reader',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['newsletter_recipients'] = array(
	'do' => 'newsletter',
	'table' => 'tl_newsletter_recipients',
	'column' => 'nl_channels',
	'columnType' => 'serialized',
	'icon' => 'system/themes/default/images/mgroup.gif',
	'feModules' => array(
		'subscribe',
		'unsubscribe',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['faq'] = array(
	'table' => 'tl_faq',
	'column' => 'faq_categories',
	'columnType' => 'serialized',
	'icon' => 'system/modules/faq/assets/icon.gif',
	'feModules' => array(
		'faqlist',
		'faqreader',
		'faqpage',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['form'] = array(
	'table' => 'tl_form_field',
	'column' => 'form',
	'columnType' => 'plain',
	'ceColumn' => 'form',
	'ceColumnType' => 'plain',
	'icon' => 'system/themes/default/images/form.gif',
	'feModules' => array(
		'form',
	),
	'contentElements' => array(
		'form',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['mgroup'] = array(
	'table' => 'tl_member_group',
	'act' => 'edit',
	'column' => 'ml_groups',
	'columnType' => 'serialized',
	'icon' => 'system/themes/default/images/mgroup.gif',
	'feModules' => array(
		'memberlist',
	),
);

// third party extensions
$GLOBALS['TL_RSFH']['backendModules']['dlh_googlemaps'] = array(
	'table' => 'tl_dlh_googlemaps_elements',
	'column' => 'dlh_googlemap',
	'columnType' => 'plain',
	'ceColumn' => 'dlh_googlemap',
	'ceColumnType' => 'plain',
	'icon' => 'system/modules/dlh_googlemaps/assets/icon.gif',
	'feModules' => array(
		'dlh_googlemaps',
	),
	'contentElements' => array(
		'dlh_googlemaps',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['gallery_creator'] = array(
	'table' => 'tl_gallery_creator_pictures',
	'ceColumn' => 'gc_publish_albums',
	'ceColumnType' => 'serialized',
	'icon' => 'system/modules/gallery_creator/assets/images/photo.png',
	'feModules' => array(
		'gallery_creator',
	),
	'contentElements' => array(
		'gallery_creator',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['ticker'] = array(
	'table' => 'tl_ticker',
	'column' => 'ticker_categories',
	'columnType' => 'serialized',
	'icon' => 'system/modules/ticker/icon.gif',
	'feModules' => array(
		'ticker',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['survey'] = array(
	'table' => 'tl_survey_page',
	'ceColumn' => 'survey',
	'ceColumnType' => 'plain',
	'icon' => 'system/modules/survey_ce/assets/survey.png',
	'contentElements' => array(
		'survey',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['pageimages'] = array(
	'table' => 'tl_pageimages_items',
	'column' => 'pageimages',
	'columnType' => 'plain',
	'icon' => 'system/modules/pageimages/html/icon.gif',
	'feModules' => array(
		'pageimages',
	),
);
$GLOBALS['TL_RSFH']['backendModules']['rocksolid_slider'] = array(
	'table' => 'tl_rocksolid_slide',
	'column' => 'rsts_id',
	'columnType' => 'plain',
	'ceColumn' => 'rsts_id',
	'ceColumnType' => 'plain',
	'icon' => 'system/modules/rocksolid-slider/assets/img/icon.png',
	'feModules' => array(
		'rocksolid_slider',
	),
	'contentElements' => array(
		'rocksolid_slider',
	),
);
