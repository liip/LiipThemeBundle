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

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Liip\ThemeBundle\ActiveTheme;

/**
 * FileLocator uses the HttpKernel FileLocator to locate resources in bundles
 * and follow a configurable file path.
 *
 * @author Tobias Ebnöther <ebi@liip.ch>
 * @author Roland Schilter <roland.schilter@liip.ch>
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class FileLocator extends BaseFileLocator
{
    protected $kernel;
    protected $path;
    protected $basePaths = array();
    protected $pathPatterns;

    /**
     * @var ActiveTheme
     */
    protected $activeTheme;

    /**
     * @var string
     */
    protected $lastTheme;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel       A KernelInterface instance
     * @param ActiveTheme     $activeTheme  A ActiveTheme instance
     * @param string|null     $path         Path
     * @param array           $paths        Base paths
     * @param array           $pathPatterns Fallback paths pattern
     */
    public function __construct(
        KernelInterface $kernel,
        ActiveTheme $activeTheme,
        $path = null,
        array $paths = array(),
        array $pathPatterns = array()
    ) {
        $this->kernel = $kernel;
        $this->activeTheme = $activeTheme;
        $this->path = $path;
        $this->basePaths = $paths;

        $defaultPathPatterns = array(
            'app_resource' => array(
                '%app_path%/themes/%current_theme%/%template%',
                '%app_path%/views/%template%',
            ),
            'bundle_resource' => array(
                '%bundle_path%/Resources/themes/%current_theme%/%template%',
            ),
            'bundle_resource_dir' => array(
                '%dir%/themes/%current_theme%/%bundle_name%/%template%',
                '%dir%/%bundle_name%/%override_path%',
            ),
        );

        $this->pathPatterns = array_merge_recursive(array_filter($pathPatterns), $defaultPathPatterns);

        $this->lastTheme = $this->activeTheme->getName();

        parent::__construct(array());
    }

    /**
     * Set the active theme.
     *
     * @param string $theme
     * @param string $deviceType
     */
    public function setCurrentTheme($theme, $deviceType)
    {
        $this->lastTheme = $theme;

        $paths = $this->basePaths;

        // add active theme as Resources/themes/views folder as well.
        $paths[] = $this->path.'/themes/'.$theme;
        $paths[] = $this->path;

        $this->paths = $paths;
    }

    /**
     * Returns the file path for a given resource for the first directory it
     * has a match.
     *
     * The resource name must follow the following pattern:
     *
     *     "@BundleName/path/to/a/file.something"
     *
     * where BundleName is the name of the bundle
     * and the remaining part is the relative path in the bundle.
     *
     * @param string $name  A resource name to locate
     * @param string $dir   A directory where to look for the resource first
     * @param bool   $first Whether to return the first path or paths for all matching bundles
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locate($name, $dir = null, $first = true)
    {
        // update the paths if the theme changed since the last lookup
        $theme = $this->activeTheme->getName();

        if ($this->lastTheme !== $theme) {
            $this->setCurrentTheme($theme, $this->activeTheme->getDeviceType());
        }

        if ('@' === $name[0]) {
            return $this->locateBundleResource($name, $this->path, $first);
        }

        if (0 === strpos($name, 'views/')) {
            if ($res = $this->locateAppResource($name, $this->path, $first)) {
                return $res;
            }
        }

        return parent::locate($name, $dir, $first);
    }

    /**
     * Locate Resource Theme aware. Only working for bundle resources!
     *
     * Method inlined from Symfony\Component\Http\Kernel
     *
     * @param string $name
     * @param string $dir
     * @param bool   $first
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function locateBundleResource($name, $dir = null, $first = true)
    {
        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        if (!preg_match('/(Bundle)$/i', $bundleName)) {
            $bundleName .= 'Bundle';
            if (0 !== strpos($path, 'Resources')) {
                $path = 'Resources/views/'.$path;
            }
        }

        if (0 !== strpos($path, 'Resources')) {
            throw new \RuntimeException('Template files have to be in Resources.');
        }

        $resourceBundle = null;
        $bundles = $this->kernel->getBundle($bundleName, false, true);
        // Symfony 4+ no longer supports inheritance and so we only get a single bundle
        if (!is_array($bundles)) {
            $bundles = array($bundles);
        }
        $files = array();

        $parameters = array(
            '%app_path%' => $this->path,
            '%dir%' => $dir,
            '%override_path%' => substr($path, strlen('Resources/')),
            '%current_theme%' => $this->lastTheme,
            '%current_device%' => $this->activeTheme->getDeviceType(),
            '%template%' => substr($path, strlen('Resources/views/')),
        );

        foreach ($bundles as $bundle) {
            $parameters = array_merge($parameters, array(
                '%bundle_path%' => $bundle->getPath(),
                '%bundle_name%' => $bundle->getName(),
            ));

            $checkPaths = $this->getPathsForBundleResource($parameters);

            foreach ($checkPaths as $checkPath) {
                if (file_exists($checkPath)) {
                    if (null !== $resourceBundle) {
                        throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                            $path,
                            $resourceBundle,
                            $checkPath
                        ));
                    }

                    if ($first) {
                        return $checkPath;
                    }
                    $files[] = $checkPath;
                }
            }

            $file = $bundle->getPath().'/'.$path;
            if (file_exists($file)) {
                if ($first) {
                    return $file;
                }
                $files[] = $file;
                $resourceBundle = $bundle->getName();
            }
        }

        if (count($files) > 0) {
            return $first ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    /**
     * Locate Resource Theme aware. Only working for app/Resources.
     *
     * @param string $name
     * @param string $dir
     * @param bool   $first
     *
     * @return string|array
     */
    protected function locateAppResource($name, $dir = null, $first = true)
    {
        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $files = array();
        $parameters = array(
            '%app_path%' => $this->path,
            '%current_theme%' => $this->lastTheme,
            '%current_device%' => $this->activeTheme->getDeviceType(),
            '%template%' => substr($name, strlen('views/')),
        );

        foreach ($this->getPathsForAppResource($parameters) as $checkPaths) {
            if (file_exists($checkPaths)) {
                if ($first) {
                    return $checkPaths;
                }
                $files[] = $checkPaths;
            }
        }

        return $files;
    }

    protected function getPathsForBundleResource($parameters)
    {
        $pathPatterns = array();
        $paths = array();

        if (!empty($parameters['%dir%'])) {
            $pathPatterns = array_merge($pathPatterns, $this->pathPatterns['bundle_resource_dir']);
        }

        $pathPatterns = array_merge($pathPatterns, $this->pathPatterns['bundle_resource']);

        foreach ($pathPatterns as $pattern) {
            $paths[] = strtr($pattern, $parameters);
        }

        return $paths;
    }

    protected function getPathsForAppResource($parameters)
    {
        $paths = array();

        foreach ($this->pathPatterns['app_resource'] as $pattern) {
            $paths[] = strtr($pattern, $parameters);
        }

        return $paths;
    }
}
