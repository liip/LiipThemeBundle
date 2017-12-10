<?php
/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Tests\CacheWarmer;

use Liip\ThemeBundle\CacheWarmer\TemplateFinder;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateFilenameParser;

/**
 * TemplateFinderTest.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class TemplateFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var TemplateFilenameParser
     */
    private $parser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpKernel\Bundle\BundleInterface
     */
    private $bundle;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Liip\ThemeBundle\ActiveTheme
     */
    private $activeTheme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Templating\TemplateReferenceInterface
     */
    private $templateReference;

    public function setUp()
    {
        parent::setUp();
        $this->kernel = $this->getMockForAbstractClass('\Symfony\Component\HttpKernel\KernelInterface');
        $this->parser = new TemplateFilenameParser();
        $this->bundle = $this->getMockForAbstractClass('\Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $this->templateReference = $this->getMockForAbstractClass('\Symfony\Component\Templating\TemplateReferenceInterface');
        $this->activeTheme = $this->getMockBuilder('\Liip\ThemeBundle\ActiveTheme')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testFindAllTemplates()
    {
        $finder = new TemplateFinder($this->kernel, $this->parser, '', $this->activeTheme);
        // mock for \Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinder
        $this->kernel->expects($this->at(0))->method('getBundles')->willReturn(array());

        // mock for \Liip\ThemeBundle\CacheWarmer\TemplateFinder
        $bundles = array('FooBundle' => '', 'BarBundle' => '');
        $themes = array('foo', 'bar');
        foreach ($bundles as $bundleName => $bundle) {
            $bundle = clone $this->bundle;
            $bundle->expects($this->exactly(count($themes)))->method('getName')->willReturn($bundleName);
            $bundle->expects($this->exactly(count($themes)))->method('getPath')->willReturn(realpath(__DIR__.'/../Fixtures/bundles/'.$bundleName));
            $bundles[$bundleName] = $bundle;
        }
        $this->kernel->expects($this->at(1))->method('getBundles')->willReturn(array_values($bundles));
        $this->activeTheme->expects($this->once())->method('getThemes')->willReturn($themes);


        $templates = $finder->findAllTemplates();
        $this->assertCount(2, $templates);

        $templates = $finder->findAllTemplates();
        $this->assertCount(2, $templates);
    }
}
