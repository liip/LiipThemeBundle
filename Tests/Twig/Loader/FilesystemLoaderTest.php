<?php

namespace Tests\Twig\Loader;

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Twig\Loader\FilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

class FilesystemLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSourceContextWithLocator()
    {
        $activeTheme = new ActiveTheme('test', ['test']);
        $parser = $this->getMockBuilder('Symfony\Component\Templating\TemplateNameParserInterface')->getMock();
        $locator = $this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')->getMock();

        $locator
            ->expects($this->once())
            ->method('locate')
            ->will($this->returnValue(__DIR__.'/Fixtures/Resources/views/layout.html.twig'));


        $loader = new FilesystemLoader($locator, $parser);
        $loader->setActiveTheme($activeTheme);

        // Symfony-style
        $this->assertEquals("This is a layout\n", $loader->getSourceContext('TwigBundle::layout.html.twig')->getCode());
    }

    public function testGetSourceContextWithFallback()
    {
        $activeTheme = new ActiveTheme('test', ['test']);
        $parser = $this->getMockBuilder('Symfony\Component\Templating\TemplateNameParserInterface')->getMock();
        $locator = $this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')->getMock();

        $locator
            ->expects($this->once())
            ->method('locate')
            ->willThrowException(new \RuntimeException());

        $loader = new FilesystemLoader($locator, $parser);
        $loader->addPath(__DIR__.'/Fixtures/Resources/views', 'namespace');
        $loader->setActiveTheme($activeTheme);

        // Twig-style
        $this->assertEquals("This is a layout\n", $loader->getSourceContext('@namespace/layout.html.twig')->getCode());
    }

    /**
     * @expectedException \Twig_Error_Loader
     */
    public function testTwigErrorIfLocatorThrowsInvalid()
    {
        $parser = $this->getMockBuilder('Symfony\Component\Templating\TemplateNameParserInterface')->getMock();
        $parser
            ->expects($this->once())
            ->method('parse')
            ->with('name.format.engine')
            ->will($this->returnValue(new TemplateReference('', '', 'name', 'format', 'engine')))
        ;

        $locator = $this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')->getMock();
        $locator
            ->expects($this->once())
            ->method('locate')
            ->will($this->throwException(new \InvalidArgumentException('Unable to find template "NonExistent".')))
        ;

        $loader = new FilesystemLoader($locator, $parser);
        $loader->getCacheKey('name.format.engine');
    }
}
