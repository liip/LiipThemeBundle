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
use Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition;
use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler\TemplateResourcesPass as BaseTemplateResourcesPass;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * This pass adds directory resources to scan for assetic assets.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class TemplateResourcesPass extends BaseTemplateResourcesPass
{
    protected function setBundleDirectoryResources(ContainerBuilder $container, $engine, $bundleDirName, $bundleName)
    {
        if (!$container->hasDefinition('assetic.'.$engine.'_directory_resource.'.$bundleName)) {
            throw new LogicException('The LiipThemeBundle must be registered after the AsseticBundle in the application Kernel.');
        }

        $resources = $container->getDefinition('assetic.'.$engine.'_directory_resource.'.$bundleName)->getArgument(0);
        $themes = $container->getParameter('liip_theme.themes');
        foreach ($themes as $theme) {
            $resources[] = new DirectoryResourceDefinition(
                $bundleName,
                $engine,
                array(
                    $container->getParameter('kernel.root_dir').'/Resources/'.$bundleName.'/themes/'.$theme,
                    $bundleDirName.'/Resources/themes/'.$theme,
                )
            );
        }

        $container->getDefinition('assetic.'.$engine.'_directory_resource.'.$bundleName)->replaceArgument(0, $resources);
    }

    protected function setAppDirectoryResources(ContainerBuilder $container, $engine)
    {
        if (!$container->hasDefinition('assetic.'.$engine.'_directory_resource.kernel')) {
            throw new LogicException('The LiipThemeBundle must be registered after the AsseticBundle in the application Kernel.');
        }

        $themes = $container->getParameter('liip_theme.themes');
        foreach ($themes as $key => $theme) {
            $themes[$key] = $container->getParameter('kernel.root_dir').'/Resources/themes/'.$theme;
        }
        $themes[] = $container->getParameter('kernel.root_dir').'/Resources/views';

        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.kernel',
            new DirectoryResourceDefinition('', $engine, $themes)
        );
    }
}
