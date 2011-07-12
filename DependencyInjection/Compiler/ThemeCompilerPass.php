<?php

namespace Liip\ThemeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ThemeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Replace templating.
        $container->getDefinition('templating.locator')
            ->replaceArgument(0, new Reference('liip_theme.file_locator'))
        ;
    }
}
