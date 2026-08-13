<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use MadeYourDay\RockSolidFrontendHelper\FrontendHooks;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class AddAssetsListener
{
	private const ICONS = [
		'edit' => 'edit.svg',
		'new' => 'new.svg',
		'delete' => 'delete.svg',
		'close' => 'close--dark.svg',
		'children' => 'children.svg',
		'exit-dark' => 'exit--dark.svg',
		'article-dark' => 'article--dark.svg',
		'modules' => 'modules.svg',
		'regular-dark' => 'regular--dark.svg',
		'layout' => 'layout.svg',
		'sizes' => 'sizes.svg',
		'css' => 'code.svg',
		'visible-dark' => 'visible--dark.svg',
		'invisible-dark' => 'invisible--dark.svg',
		'ok' => 'ok.svg',
		'show' => 'show.svg',
		'drag' => 'drag.svg',
		'loading' => 'loading.svg',
	];

	public function __construct(
		private ScopeMatcher $scopeMatcher,
		private ContaoFramework $framework,
		private ParameterBagInterface $parameters,
	) {
	}

	public function __invoke(RequestEvent $event): void
	{
		if ($this->scopeMatcher->isBackendMainRequest($event)) {
			$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/rocksolidfrontendhelper/js/be_main.js';
		}
		if ($this->scopeMatcher->isFrontendMainRequest($event)) {
			$this->framework->initialize();
			if (FrontendHooks::checkLogin()) {
				$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/rocksolidfrontendhelper/js/main.js';
				$GLOBALS['TL_CSS'][] = 'bundles/rocksolidfrontendhelper/css/main.css';

				// Generates the custom properties for icons if a manifest file exists
				$this->addIconPropertiesFromManifest();
			}
		}
	}

	private function addIconPropertiesFromManifest(): void
	{
		if (!$this->parameters->has('contao.backend.icons')) {
			return;
		}

		$properties = '';

		$icons = $this->parameters->get('contao.backend.icons');

		foreach (self::ICONS as $key => $name) {
			if (null !== ($path = $icons[$name]['path'] ?? null)) {
				$properties .= \sprintf('--rsfh-icon-%s:url("%s");', $key, $path);
			}
		}

		if ('' !== $properties) {
			$GLOBALS['TL_HEAD'][] = '<style>:root{'.$properties.'}</style>';
		}
	}
}
