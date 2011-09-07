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

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

class TemplatePathsCacheWarmer extends CacheWarmer
{
    protected $themes;

    /**
     * Constructor.
     *
     * @param array $themes  List of themes
     */
    public function __construct(array $themes)
    {
        $this->themes = $themes;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if (null !== $cacheDir && file_exists($cache = $cacheDir.'/templates.php')) {
            $cache = require $cache;

            $themes = $this->themes;
            $themes[] = '';

            $templates = array();
            foreach ($themes as $theme) {
                foreach ($cache as $key => $template) {
                    $templates[$key.'|'.$theme] = $template;
                }
            }

            $this->writeCacheFile($cacheDir.'/templates.php', sprintf('<?php return %s;', var_export($templates, true)));
        }
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always true
     */
    public function isOptional()
    {
        return true;
    }
}
