<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

/**
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class ElementProvider implements ElementProviderInterface
{
	/**
	 * @var ContaoFrameworkInterface
	 */
	private $framework;

	/**
	 * @var array
	 */
	private static $defaultValues = [
		'text' => [
			'text' => '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam.</p>',
		],
		'headline' => [
			'headline' => [
				'value' => 'Headline',
				'unit' => 'h1',
			],
		],
		'html' => [
			'html' => '<div><code>HTML Code</code></div>',
		],
		'list' => [
			'listtype' => 'unordered',
			'listitems' => ['List Item'],
		],
		'table' => [
			'tableitems' => [
				['Table', '&nbsp;'],
				['&nbsp;', '&nbsp;'],
			],
		],
		'code' => [
			'code' => '<code>',
		],
		'markdown' => [
			'code' => "## Markdown\n\nLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam.",
		],
		'accordionSingle' => [
			'mooHeadline' => 'Section',
			'text' => '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam.</p>',
		],
		'hyperlink' => [
			'url' => '{{link_url::1}}',
			'linkTitle' => 'Link',
		],
		'toplink' => [],
		'image' => [],
		'gallery' => [],
		'player' => [],
		'youtube' => [
			'youtube' => 'HRKxcgelfzQ',
		],
		'vimeo' => [
			'vimeo' => '95674578',
		],
		'download' => [],
		'downloads' => [],
	];

	/**
	 * @param ContaoFrameworkInterface $framework
	 */
	public function __construct(ContaoFrameworkInterface $framework)
	{
		$this->framework = $framework;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getElements($table)
	{
		if ($table !== 'tl_content') {
			return [];
		}

		$this->framework->initialize();

		$elements = [];

		foreach ($GLOBALS['TL_CTE'] as $group => $groupElements) {
			foreach ($groupElements as $type => $class) {
				if (!isset(static::$defaultValues[$type])) {
					continue;
				}
				$elements[$type] = [
					'group' => $this->getLabel($group),
					'label' => $this->getLabel($type),
				];
			}
		}

		return $elements;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValues($table, $type)
	{
		if ($table !== 'tl_content' || !isset(static::$defaultValues[$type])) {
			return [];
		}

		$values = static::$defaultValues[$type];

		$values = $this->addDynamicDefaultValues($type, $values);

		foreach ($values as $field => $value) {
			if (is_array($value)) {
				$values[$field] = serialize($value);
			}
		}

		return $values;
	}

	/**
	 * Add dynamic default values for fields that reference other entities
	 *
	 * @param string $type
	 * @param array  $values
	 *
	 * @return array Updated values
	 */
	private function addDynamicDefaultValues($type, array $values)
	{
		if ($type === 'image') {
			$values['singleSRC'] = $this->getUuidByExtensions(
				$this->framework->getAdapter('Config')->get('validImageTypes')
			);
		}
		elseif ($type === 'gallery') {
			$values['multiSRC'] = [$this->getUuidByExtensions(
				$this->framework->getAdapter('Config')->get('validImageTypes')
			)];
		}
		elseif ($type === 'player') {
			$values['playerSRC'] = [$this->getUuidByExtensions(
				['mp4', 'webm', 'ogv', 'ogg', 'mp3', 'wav', 'aac']
			)];
		}
		elseif ($type === 'download') {
			$values['singleSRC'] = $this->getUuidByExtensions(
				$this->framework->getAdapter('Config')->get('allowedDownload')
			);
		}
		elseif ($type === 'downloads') {
			$values['multiSRC'] = [$this->getUuidByExtensions(
				$this->framework->getAdapter('Config')->get('allowedDownload')
			)];
		}

		return $values;
	}

	/**
	 * Get the UUID of the first file that has one of the specified extensions
	 *
	 * @param array|string $extensions
	 *
	 * @return string|null Binary UUID
	 */
	private function getUuidByExtensions($extensions)
	{
		$adapter = $this->framework->getAdapter('FilesModel');

		if (is_array($extensions)) {
			$extensions = implode(',', $extensions);
		}

		$file = $adapter->findOneBy(
			['FIND_IN_SET(' . $adapter->getTable() . '.extension, ?)'],
			$extensions,
			['order' => 'id']
		);

		if ($file) {
			return $file->uuid;
		}

		return null;
	}

	/**
	 * Get label for content element
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function getLabel($key)
	{
		$this->framework->getAdapter('System')->loadLanguageFile('default', 'en');

		if (isset($GLOBALS['TL_LANG']['CTE'][$key])) {
			return $GLOBALS['TL_LANG']['CTE'][$key];
		}

		return [$key, ''];
	}
}
