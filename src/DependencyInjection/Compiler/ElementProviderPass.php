<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers the content element providers.
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class ElementProviderPass implements CompilerPassInterface
{
	use PriorityTaggedServiceTrait;

	/**
	 * {@inheritdoc}
	 */
	public function process(ContainerBuilder $container)
	{
		if (!$container->has('rocksolid_frontend_helper.element_builder')) {
			return;
		}

		$definition = $container->findDefinition('rocksolid_frontend_helper.element_builder');
		$references = $this->findAndSortTaggedServices('rocksolid_frontend_helper.element_provider', $container);

		foreach ($references as $reference) {
			$definition->addMethodCall('addProvider', [$reference]);
		}
	}
}
