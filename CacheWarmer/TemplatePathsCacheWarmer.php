<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\CacheWarmer;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplatePathsCacheWarmer as BaseTemplatePathsCacheWarmer;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplatePathsCacheWarmer extends BaseTemplatePathsCacheWarmer
{
    /**
     * @var ActiveTheme
     */
    protected $activeTheme;

    /**
     * @var \Liip\ThemeBundle\Locator\TemplateLocator
     */
    protected $locator;

    /**
     * Constructor.
     *
     * @param TemplateFinderInterface $finder      A template finder
     * @param TemplateLocator         $locator     The template locator
     * @param ActiveTheme             $activeTheme
     */
    public function __construct(TemplateFinderInterface $finder, TemplateLocator $locator, ActiveTheme $activeTheme = null)
    {
        $this->activeTheme = $activeTheme;
        parent::__construct($finder, $locator);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if (empty($this->activeTheme)) {
            return;
        }

        $locator = $this->locator->getLocator();

        /** @var TemplateReferenceInterface[] $allTemplates */
        $allTemplates = $this->finder->findAllTemplates();

        $templates = array();
        foreach ($this->activeTheme->getThemes() as $theme) {
            $this->activeTheme->setName($theme);
            foreach ($allTemplates as $template) {
                try {
                    $templates[$template->getLogicalName().'|'.$theme] = $locator->locate($template->getPath());
                } catch (\InvalidArgumentException $e) {
                }
            }
        }

        $this->writeCacheFile($cacheDir.'/templates.php', sprintf('<?php return %s;', var_export($templates, true)));
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return bool always true
     */
    public function isOptional()
    {
        return true;
    }
}
