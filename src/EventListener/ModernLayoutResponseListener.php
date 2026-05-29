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
use Contao\LayoutModel;
use Contao\PageModel;
use MadeYourDay\RockSolidFrontendHelper\FrontendHooks;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ModernLayoutResponseListener
{
	public function __construct(
		private ScopeMatcher $scopeMatcher,
		private ContaoFramework $framework,
		private FrontendHooks $frontendHooks,
	) {
	}

	public function __invoke(ResponseEvent $event): void
	{
		if (
			$this->scopeMatcher->isFrontendMainRequest($event) &&
			($pageModel = $event->getRequest()->attributes->get('pageModel')) instanceof PageModel &&
			($layout = $this->framework->getAdapter(LayoutModel::class)->findById($pageModel->layout)) &&
			$layout->type === 'modern' &&
			($content = $event->getResponse()->getContent()) &&
			stripos($content, '<html') !== false
		) {
			$content = $this->frontendHooks->outputFrontendTemplateHook($content, $layout->template);
			$event->getResponse()->setContent($content);
		}
	}
}
