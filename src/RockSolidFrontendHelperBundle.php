<?php
/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MadeYourDay\RockSolidFrontendHelper;

use MadeYourDay\RockSolidFrontendHelper\DependencyInjection\RockSolidFrontendHelperExtension;
use MadeYourDay\RockSolidFrontendHelper\DependencyInjection\Compiler\ElementProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Configures the RockSolid Frontend Helper bundle.
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class RockSolidFrontendHelperBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
	public function getContainerExtension(): ?ExtensionInterface
    {
        return new RockSolidFrontendHelperExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ElementProviderPass());
    }
}
