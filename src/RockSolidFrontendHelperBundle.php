<?php

declare(strict_types=1);

/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

use MadeYourDay\RockSolidFrontendHelper\DependencyInjection\Compiler\ElementProviderPass;
use MadeYourDay\RockSolidFrontendHelper\DependencyInjection\RockSolidFrontendHelperExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Configures the RockSolid Frontend Helper bundle.
 */
class RockSolidFrontendHelperBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ExtensionInterface|null
    {
        return new RockSolidFrontendHelperExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ElementProviderPass());
    }
}
