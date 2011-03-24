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

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Config\FileLocator as HttpKernelFileLocator;

/**
 * FileLocator uses the HttpKernel FileLocator to locate resources in bundles
 * and follow a configurable file path.
 *
 * @author Tobias Ebnöther <ebi@liip.ch>
 * @author Roland Schilter <roland.schilter@liip.ch>
 */
class FileLocator extends HttpKernelFileLocator
{

    protected $kernel;
    protected $themes;
    protected $activeTheme;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     * @param array $themes Theme fallback chain
     * @param string $activeTheme The currenctly selected theme
     *
     * @throws \InvalidArgumentException if the active theme is not in the themes list
     */
    public function __construct(KernelInterface $kernel)
    {
        $container = $kernel->getContainer();
        $this->themes = array_merge(array(''), $container->getParameter('liip_theme.themes'));
        $this->activeTheme = $container->getParameter('liip_theme.activeTheme');
        if (! in_array($this->activeTheme, $this->themes)) {
            throw new \InvalidArgumentException(sprintf('The active theme must be in the themes list.'));
        }
        parent::__construct($kernel);
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
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $name = substr($name, 1);
        list($bundle, $path) = explode(DIRECTORY_SEPARATOR, $name, 2);

        $isResource = 0 === strpos($path, 'Resources');

        $files = array();
        if (true === $isResource && null !== $dir && file_exists($file = $dir.DIRECTORY_SEPARATOR.$bundle.DIRECTORY_SEPARATOR.substr($path, 10))) {
            if ($first) {
                return $file;
            }

            $files[] = $file;
        }

        foreach ($this->kernel->getBundle($bundle, false) as $bundle) {
            for ($i = array_search($this->activeTheme, $this->themes); $i >= 0 ;$i--) {
                if ('' !== $this->themes[$i]) {
                    $theme = 'Resources'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$this->themes[$i];
                } else {
                    $theme = 'Resources'.DIRECTORY_SEPARATOR.'views';
                }
                $tmpPath = $theme . substr($path, 15);
                if (file_exists($file = $bundle->getPath().DIRECTORY_SEPARATOR.$tmpPath)) {
                    if ($first) {
                        return $file;
                    }
                    $files[] = $file;
                }
            }
        }

        if ($files) {
            return $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "@%s".', $name));
    }
}
