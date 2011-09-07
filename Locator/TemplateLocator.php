<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Locator;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as BaseTemplateLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TemplateLocator extends BaseTemplateLocator
{
    protected $theme;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface $locator  A FileLocatorInterface instance
     * @param string               $cacheDir The cache path
     * @param ContainerInterface   $container The container
     */
    public function __construct(FileLocatorInterface $locator, $cacheDir = null, ContainerInterface $container = null)
    {
        $this->theme = $container->get('liip_theme.active_theme')->getName();

        parent::__construct($locator, $cacheDir);
    }

    /**
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template     A template
     *
     * @return string The full path for the file
     */
    protected function getCacheKey($template)
    {
        return $template->getLogicalName().'|'.$this->theme;
    }

    /**
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template     A template
     * @param string                     $currentPath  Unused
     * @param Boolean                    $first        Unused
     *
     * @return string The full path for the file
     *
     * @throws \InvalidArgumentException When the template is not an instance of TemplateReferenceInterface
     * @throws \InvalidArgumentException When the template file can not be found
     */
    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof TemplateReferenceInterface) {
            throw new \InvalidArgumentException("The template must be an instance of TemplateReferenceInterface.");
        }

        $key = $this->getCacheKey($template);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        try {
            return $this->cache[$key] = $this->locator->locate($template->getPath(), $currentPath);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s" in "%s".', $template, $this->path), 0, $e);
        }
    }
}
