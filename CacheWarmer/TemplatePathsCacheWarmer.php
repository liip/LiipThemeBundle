<?php

namespace Liip\ThemeBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

/**
 * For now this just deletes the templates.php cache file
 * Eventually it should maybe try and generate a proper cache file
 */
class TemplatePathsCacheWarmer extends CacheWarmer
{
    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
var_dump(__LINE__);
        if (null !== $cacheDir && file_exists($cache = $cacheDir.'/templates.php')) {
            unlink($cache);
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
