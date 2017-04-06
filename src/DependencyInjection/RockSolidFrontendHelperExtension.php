<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * RockSolid Frontend Helper bundle extension.
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class RockSolidFrontendHelperExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function getAlias()
	{
		return 'rocksolid_frontend_helper';
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$baseConfig = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/config.yml'), Yaml::PARSE_CONSTANT);
		$configs = array_merge([$baseConfig['rocksolid_frontend_helper']], $configs);
		$mergedConfig = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../Resources/config')
		);

		$loader->load('services.yml');

		$container->setParameter('rocksolid_frontend_helper.backend_modules', $mergedConfig['backend_modules']);
	}
}
