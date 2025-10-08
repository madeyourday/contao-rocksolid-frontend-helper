<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * RockSolid Frontend Helper bundle extension.
 */
class RockSolidFrontendHelperExtension extends Extension
{
    public function getAlias(): string
    {
        return 'rocksolid_frontend_helper';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $baseConfig = Yaml::parse(file_get_contents(__DIR__.'/../../config/config.yml'), Yaml::PARSE_CONSTANT);
        $configs = array_merge([$baseConfig['rocksolid_frontend_helper']], $configs);
        $mergedConfig = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config'),
        );

        $loader->load('services.yml');

        $container->setParameter('rocksolid_frontend_helper.backend_modules', $mergedConfig['backend_modules']);
    }
}
