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

use Liip\ThemeBundle\ActiveTheme;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinder as BaseTemplateFinder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Finder\Finder;

/**
 * Finds all templates, including themes.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class TemplateFinder extends BaseTemplateFinder
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var TemplateNameParserInterface
     */
    private $parser;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var array
     */
    private $templates;

    /**
     * @var ActiveTheme
     */
    private $activeTheme;

    /**
     * TemplateFinder constructor.
     *
     * @param KernelInterface             $kernel
     * @param TemplateNameParserInterface $parser
     * @param string                      $rootDir
     * @param ActiveTheme                 $activeTheme
     */
    public function __construct(
        KernelInterface $kernel,
        TemplateNameParserInterface $parser,
        $rootDir,
        ActiveTheme $activeTheme
    ) {
        parent::__construct($kernel, $parser, $rootDir);
        $this->kernel = $kernel;
        $this->parser = $parser;
        $this->rootDir = $rootDir;
        $this->activeTheme = $activeTheme;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllTemplates()
    {
        if (null !== $this->templates) {
            return $this->templates;
        }

        $templates = parent::findAllTemplates();

        $themes = $this->activeTheme->getThemes();
        foreach ($this->kernel->getBundles() as $bundle) {
            foreach ($themes as $theme) {
                $templates = array_merge($templates, $this->findTemplatesInThemes($bundle, $theme));
            }
        }

        return $this->templates = $templates;
    }

    /**
     * Find templates in the given bundle.
     *
     * @param BundleInterface $bundle The bundle where to look for templates
     * @param string          $theme
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    private function findTemplatesInThemes(BundleInterface $bundle, $theme)
    {
        $templates = $this->findTemplatesInFolder($bundle->getPath().'/Resources/themes/'.$theme);
        $name = $bundle->getName();

        foreach ($templates as $i => $template) {
            $templates[$i] = $template->set('bundle', $name);
        }

        return $templates;
    }

    /**
     * Find templates in the given directory.
     *
     * @param string $dir The folder where to look for templates
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    private function findTemplatesInFolder($dir)
    {
        $templates = array();

        if (is_dir($dir)) {
            $finder = new Finder();
            foreach ($finder->files()->followLinks()->in($dir) as $file) {
                $template = $this->parser->parse($file->getRelativePathname());
                if (false !== $template) {
                    $templates[] = $template;
                }
            }
        }

        return $templates;
    }
}
