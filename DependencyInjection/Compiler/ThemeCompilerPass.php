<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class ThemeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('templating.locator', 'liip_theme.templating_locator');

        $container->setAlias('templating.cache_warmer.template_paths', 'liip_theme.templating.cache_warmer.template_paths');

        if (!$container->getParameter('liip_theme.cache_warming')) {
            $container->getDefinition('liip_theme.templating.cache_warmer.template_paths')
                ->replaceArgument(2, null);
        }

        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');
        $twigFilesystemLoaderDefinition->setClass($container->getParameter('liip_theme.filesystem_loader.class'));
        $twigFilesystemLoaderDefinition->addArgument(new Reference('liip_theme.active_theme'));
    }
}
