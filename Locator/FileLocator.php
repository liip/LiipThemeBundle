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

/**
 * FileLocator uses the HttpKernel FileLocator to locate resources in bundles
 * and follow a configurable file path.
 *
 * @author Tobias Ebnöther <ebi@liip.ch>
 * @author Roland Schilter <roland.schilter@liip.ch>
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class FileLocator extends BaseFileLocator
{
    protected $kernel;
    protected $path;
    protected $basePaths = array();
    protected $themes;
    protected $activeTheme;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     * @param string $path The path the global resource directory
     *
     * @throws \InvalidArgumentException if the active theme is not in the themes list
     */
    public function __construct(KernelInterface $kernel, $path = null, array $paths = array())
    {
        $this->kernel = $kernel;
        $this->path = $path;
        $container = $kernel->getContainer();
        $this->themes = $container->getParameter('liip_theme.themes');
        $this->basePaths = $paths;
        $this->setActiveTheme($container->getParameter('liip_theme.activeTheme'));
    }
    
    /**
     * Set the active theme.
     * 
     * @param string $theme 
     */
    public function setActiveTheme($theme)
    {
        $paths = $this->basePaths;
        $this->activeTheme = $theme;
        if (! in_array($this->activeTheme, $this->themes)) {
            throw new \InvalidArgumentException(sprintf('The active theme must be in the themes list.'));
        }
        $paths[] = $this->path . "/themes/" . $this->activeTheme; // add active theme as Resources/themes/views folder aswell.
        $paths[] = $this->path;
        
        $this->paths = $paths;
    }
    
    /**
     * Returns the file path for a given resource for the first directory it
     * has a match.
     *
     * The resource name must follow the following pattern:
     *
     *     @BundleName/path/to/a/file.something
     *
     * where BundleName is the name of the bundle
     * and the remaining part is the relative path in the bundle.
     *
     * @param string  $name  A resource name to locate
     * @param string  $dir   A directory where to look for the resource first
     * @param Boolean $first Whether to return the first path or paths for all matching bundles
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locate($name, $dir = null, $first = true)
    {
        if ('@' === $name[0]) {
            return $this->locateResource($name, $this->path, $first);
        }
        return parent::locate($name, $dir, $first);
    }
    
    /**
     * Locate Resource Theme aware
     * 
     * Method inlined from Symfony\Component\Http\Kernel
     * 
     * @param string $name
     * @param string $dir
     * @param bool $first
     * @return string 
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath = substr($path, 9);
        $resourceBundle = null;
        $bundles = $this->kernel->getBundle($bundleName, false);
        $files = array();

        foreach ($bundles as $bundle) {
            if ($isResource) {
                if (file_exists($file = $dir.'/themes/'.$this->activeTheme.' /' .$bundle->getName().$overridePath)) {
                    if (null !== $resourceBundle) {
                        throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                            $file,
                            $resourceBundle,
                            $dir.'/'.$bundles[0]->getName().$overridePath
                        ));
                    }

                    if ($first) {
                        return $file;
                    }
                    $files[] = $file;
                } else if (file_exists($file = $dir.'/'.$bundle->getName().$overridePath)) {
                    if (null !== $resourceBundle) {
                        throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.',
                            $file,
                            $resourceBundle,
                            $dir.'/'.$bundles[0]->getName().$overridePath
                        ));
                    }

                    if ($first) {
                        return $file;
                    }
                    $files[] = $file;
                }
            }

            if (file_exists($file = $bundle->getPath().'/'.$path)) {
                if ($first && !$isResource) {
                    return $file;
                }
                $files[] = $file;
                $resourceBundle = $bundle->getName();
            }
        }
        

        if (count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }
}
