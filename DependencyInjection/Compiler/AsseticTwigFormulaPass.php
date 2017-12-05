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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This pass replaces the assetic twig formula loader.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AsseticTwigFormulaPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('liip_theme.assetic_integration')) {
            return;
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (!array_key_exists('AsseticBundle', $bundles)) {
            throw new RuntimeException(
                'You have enabled the "assetic_integration" option but the AsseticBundle is not registered'
            );
        }

        $loaderDef = $container->getDefinition('assetic.twig_formula_loader.real');
        $loaderDef->setClass('Liip\ThemeBundle\Assetic\TwigFormulaLoader');
        $args = $loaderDef->getArguments();
        $nbArguments = is_array($args) ? count($loaderDef->getArguments()) : 0;

        // AsseticBundle 1.1.x does not have a logger definition, add it anyway
        // as it will be ignored by the older TwigFormulaLoader
        if ($nbArguments === 1) {
            $loaderDef->addArgument(new Reference('logger'));
        }

        $loaderDef->addArgument(new Reference('liip_theme.active_theme'));
    }
}
