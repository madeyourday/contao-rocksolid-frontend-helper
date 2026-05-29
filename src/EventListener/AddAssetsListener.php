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
use Symfony\Component\HttpKernel\Event\RequestEvent;

class AddAssetsListener
{
	public function __construct(
		private ScopeMatcher $scopeMatcher,
		private ContaoFramework $framework,
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
			}
		}
	}
}
