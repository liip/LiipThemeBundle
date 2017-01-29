<?php

namespace Liip\ThemeBundle\Twig\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Liip\ThemeBundle\ActiveTheme;

class FilesystemLoader extends \Twig_Loader_Filesystem
{
    protected $locator;
    protected $parser;

    /**
     * @var ActiveTheme|null
     */
    protected $activeTheme;

    /**
     * Constructor.
     *
     * @see TwigBundle own FilesystemLoader
     *
     * @param FileLocatorInterface        $locator  A FileLocatorInterface instance
     * @param TemplateNameParserInterface $parser   A TemplateNameParserInterface instance
     * @param string|null                 $rootPath The root path common to all relative paths (null for getcwd())
     */
    public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser, $rootPath = null)
    {
        parent::__construct(array(), $rootPath);

        $this->locator = $locator;
        $this->parser = $parser;
    }

    /**
     * Define the active theme
     *
     * @param ActiveTheme $activeTheme
     */
    public function setActiveTheme(ActiveTheme $activeTheme = null)
    {
        $this->activeTheme = $activeTheme;
    }

    /**
     * Returns the path to the template file.
     *
     * The file locator is used to locate the template when the naming convention
     * is the symfony one (i.e. the name can be parsed).
     * Otherwise the template is located using the locator from the twig library.
     *
     * @param string|TemplateReferenceInterface $template The template
     * @param bool                              $throw    When true, a \Twig_Error_Loader exception will be thrown if a template could not be found
     *
     * @return string The path to the template file
     *
     * @throws \Twig_Error_Loader if the template could not be found
     */
    protected function findTemplate($template, $throw = true)
    {
        $logicalName = (string) $template;

        if ($this->activeTheme) {
            $logicalName .= '|'.$this->activeTheme->getName();
        }

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $file = null;
        $previous = null;

        try {
            $templateReference = $this->parser->parse($template);
            $file = $this->locator->locate($templateReference);
        } catch (\Exception $e) {
            $previous = $e;

            // for BC
            try {
                $file = parent::findTemplate((string) $template);
            } catch (\Twig_Error_Loader $e) {
                $previous = $e;
            }
        }

        if (false === $file || null === $file) {
            if ($throw) {
                throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName), -1, null, $previous);
            }
            
            return false;
        }

        return $this->cache[$logicalName] = $file;
    }
}
