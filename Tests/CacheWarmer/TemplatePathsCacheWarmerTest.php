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

use Liip\ThemeBundle\CacheWarmer\TemplatePathsCacheWarmer;

/**
 * TemplatePathsCacheWarmerTest.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class TemplatePathsCacheWarmerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface
     */
    private $templateFinder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator
     */
    private $templateLocator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Liip\ThemeBundle\ActiveTheme
     */
    private $activeTheme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Config\FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Templating\TemplateReferenceInterface
     */
    private $templateReference;
    private $cacheDir;
    private $templateCacheFile;

    public function setUp()
    {
        parent::setUp();
        $this->cacheDir = __DIR__.'/../Fixtures/cachedir';
        $this->templateCacheFile = $this->cacheDir.'/templates.php';
        @unlink($this->templateCacheFile);

        $this->templateReference = $this->getMockForAbstractClass('\Symfony\Component\Templating\TemplateReferenceInterface');
        $this->templateFinder = $this->getMockForAbstractClass('\Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface');
        $this->templateLocator = $this->getMockBuilder('\Liip\ThemeBundle\Locator\TemplateLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileLocator = $this->getMockForAbstractClass('\Symfony\Component\Config\FileLocatorInterface');
        $this->activeTheme = $this->getMockBuilder('\Liip\ThemeBundle\ActiveTheme')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
        @unlink($this->templateCacheFile);
    }

    public function testWarmUpWithoutActiveTheme()
    {
        $warmer = new TemplatePathsCacheWarmer($this->templateFinder, $this->templateLocator);
        $this->assertNull($warmer->warmUp('.'));
    }

    public function testWarmUp()
    {
        $themes = array('foo', 'bar');
        $templates = array();
        $cacheDir = __DIR__.'/../Fixtures/cachedir';

        foreach (array('template1', 'template2') as $templateName) {
            $templates[$templateName] = $template = clone $this->templateReference;
            $template->expects($this->exactly(count($themes)))->method('getLogicalName')->willReturn($templateName);
            $template->expects($this->exactly(count($themes)))->method('getPath')->willReturn('./'.$templateName);
        }

        $warmer = new TemplatePathsCacheWarmer($this->templateFinder, $this->templateLocator, $this->activeTheme);
        $this->templateLocator->expects($this->once())->method('getLocator')->willReturn($this->fileLocator);
        $this->templateFinder->expects($this->once())->method('findAllTemplates')->willReturn(array_values($templates));
        $this->activeTheme->expects($this->once())->method('getThemes')->willReturn($themes);

        $i = 1;
        $y = 0;
        foreach ($themes as $i1 => $theme) {
            $this->activeTheme->expects($this->at($i++))->method('setName')->with($theme);
            foreach (array_keys($templates) as $i2 => $templateName) {
                $invocation = $this->fileLocator->expects($this->at($y++))->method('locate')->with('./'.$templateName, null, true);
                if ($i1 == $i2) {
                    $invocation->willThrowException(new \InvalidArgumentException());
                }
            }
        }

        $warmer->warmUp($cacheDir);
        $templatePaths = include_once $cacheDir.'/templates.php';

        $this->assertEquals(
            array(
                'template1|bar' => null,
                'template2|foo' => null,
            ),
            $templatePaths
        );
    }
}
