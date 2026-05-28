<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class AddAssetsListener
{
	private ScopeMatcher $scopeMatcher;

	public function __construct(ScopeMatcher $scopeMatcher)
	{
		$this->scopeMatcher = $scopeMatcher;
	}

	public function __invoke(RequestEvent $event): void
	{
		if ($this->scopeMatcher->isBackendMainRequest($event)) {
			$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/rocksolidfrontendhelper/js/be_main.js';
		}
	}
}
