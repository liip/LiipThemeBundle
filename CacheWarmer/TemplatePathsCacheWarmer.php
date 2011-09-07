<?php

namespace Liip\ThemeBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

/**
 * For now this just deletes the templates.php cache file
 * Eventually it should maybe try and generate a proper cache file
 */
class TemplatePathsCacheWarmer extends CacheWarmer
{
    protected $themes;

    /**
     * Constructor.
     *
     * @param array $themes  List of themes
     */
    public function __construct($themes)
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
