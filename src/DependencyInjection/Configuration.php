<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration.
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		if (method_exists(TreeBuilder::class, 'getRootNode')) {
			$treeBuilder = new TreeBuilder('rocksolid_frontend_helper');
			$rootNode = $treeBuilder->getRootNode();
		}
		else {
			// Backwards compatibility
			$treeBuilder = new TreeBuilder();
			$rootNode = $treeBuilder->root('rocksolid_frontend_helper');
		}

		$rootNode
			->children()
				->arrayNode('backend_modules')
					->useAttributeAsKey('name')
					->prototype('array')
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('do')->end()
							->scalarNode('table')->end()
							->scalarNode('act')->end()
							->scalarNode('column')->end()
							->enumNode('column_type')
								->values(['plain', 'serialized'])
								->defaultValue('plain')
							->end()
							->scalarNode('ce_column')->end()
							->enumNode('ce_column_type')
								->values(['plain', 'serialized'])
								->defaultValue('plain')
							->end()
							->scalarNode('icon')->end()
							->arrayNode('fe_modules')
								->prototype('scalar')->end()
							->end()
							->arrayNode('content_elements')
								->prototype('scalar')->end()
							->end()
						->end()
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}
}
