<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\ThemeBundle\Tests\Locator;

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Helper\DeviceDetection;
use Liip\ThemeBundle\Locator\FileLocator;

class FileLocatorFake extends FileLocator
{
    public $lastTheme;
}

class FileLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * In Symfony 4+ the $includeDerivedBundle parameter should be removed
     * 
     * @param bool $includeDerivedBundle
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getKernelMock($includeDerivedBundle = false)
    {
        $data = debug_backtrace();
        $bundleName = substr($data[1]['function'], 4);

        $bundles = array();
        $prefixes = array('');
        if ($includeDerivedBundle) {
            array_unshift($prefixes, 'Derived');
        }
        foreach ($prefixes as $prefix) {
            $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\Bundle')
                ->setMockClassName($prefix.'LiipMock'.$bundleName)
                ->disableOriginalConstructor()
                ->getMock();
            $bundle->expects($this->any())
                ->method('getPath')
                ->will($this->returnValue($this->getFixturePath()));
            $bundles[] = $bundle;
        }

        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $kernel->expects($this->any())
            ->method('getBundle')
            ->will($this->returnValue($bundles));

        return $kernel;
    }

    protected function getFixturePath()
    {
        return strtr(__DIR__.'/../Fixtures', '\\', '/');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::__construct
     * @covers Liip\ThemeBundle\Locator\FileLocator::setCurrentTheme
     */
    public function testConstructor()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $locator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');
        $this->assertInstanceOf(FileLocator::class, $locator);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::__construct
     */
    public function testConstructorFallbackPathMerge()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $property = new \ReflectionProperty('Liip\ThemeBundle\Locator\FileLocator', 'pathPatterns');
        $property->setAccessible(true);

        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');
        $this->assertEquals(
            array(
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
            ),
            $property->getValue($fileLocator)
        );

        $fileLocator = new FileLocator(
            $kernel,
            $activeTheme,
            $this->getFixturePath().'/rootdir/Resources',
            array(),
            array(
                'app_resource' => array(
                    '%app_path%/views/themes/%current_theme%/%template',
                    '%app_path%/views/themes/fallback/%template%',
                ),
                'bundle_resource' => array(
                    '%bundle_path%/Resources/views/themes/%current_theme%/%template%',
                    '%bundle_path%/Resources/views/themes/fallback/%template%',

                ),
                'bundle_resource_dir' => array(
                    '%dir%/views/themes/%current_theme%/%bundle_name%/%template%',
                    '%dir%/views/themes/fallback/%bundle_name%/%template%',
                ),
            )
        );

        $this->assertEquals(
            array(
                'app_resource' => array(
                    '%app_path%/views/themes/%current_theme%/%template',
                    '%app_path%/views/themes/fallback/%template%',
                    '%app_path%/themes/%current_theme%/%template%',
                    '%app_path%/views/%template%',
                ),
                'bundle_resource' => array(
                    '%bundle_path%/Resources/views/themes/%current_theme%/%template%',
                    '%bundle_path%/Resources/views/themes/fallback/%template%',
                    '%bundle_path%/Resources/themes/%current_theme%/%template%',
                ),
                'bundle_resource_dir' => array(
                    '%dir%/views/themes/%current_theme%/%bundle_name%/%template%',
                    '%dir%/views/themes/fallback/%bundle_name%/%template%',
                    '%dir%/themes/%current_theme%/%bundle_name%/%template%',
                    '%dir%/%bundle_name%/%override_path%',
                ),
            ),
            $property->getValue($fileLocator)
        );
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocate()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/themes/foo/template', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/template', '@Theme/template');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateWithOverriddenPathPattern()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));

        $pathPatterns = array(
            'bundle_resource' => array(
                '%bundle_path%/Resources/views/themes/%current_theme%/%template%',
                '%bundle_path%/Resources/views/themes/fallback/%template%',
            ),
        );
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources', array(), $pathPatterns);

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/views/themes/foo/template', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/template', '@Theme/template');

        // Fall through user-configured cascade order - /Resources/views/themes/bar will not be found.
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));

        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources', array(), $pathPatterns);

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/views/themes/fallback/template', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/template', '@Theme/template');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateAppThemeOverridesAll()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/foo', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/rootdir/Resources/themes/foo/LiipMockLocateAppThemeOverridesAll/foo', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/foo', '@Theme/foo');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateAppResource
     */
    public function testLocateAppWithTabletDevice()
    {
        $kernel = $this->getKernelMock();
        $device = new DeviceDetection('Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'), $device);
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources', array(), array(
            'app_resource' => array(
                '%app_path%/themes/%current_theme%/%current_device%/%template%',
            ), )
        );

        $file = $fileLocator->locate('views/template2', $this->getFixturePath().'/rootdir', true);
        $this->assertEquals($this->getFixturePath().'/rootdir/Resources/themes/foo/tablet/template2', $file);
    }

    /**
     * This verifies that the default view gets used if the currently active
     * one doesn't contain a matching file.
     *
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateViewWithMobileDevice()
    {
        $kernel = $this->getKernelMock();
        $device = new DeviceDetection('Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'), $device);
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources', array(), array(
            'bundle_resource' => array(
                '%bundle_path%/Resources/views/%current_device%/%template%',
            ),
        ));

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/defaultTemplate', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/views/phone/defaultTemplate', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/defaultTemplate', '@Theme/defaultTemplate');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateAppResource
     */
    public function testLocateApp()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('views/template2', $this->getFixturePath().'/rootdir', true);
        $this->assertEquals($this->getFixturePath().'/rootdir/Resources/themes/foo/template2', $file);
    }

    /**
     * @contain Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateActiveThemeUpdate()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocatorFake($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $this->assertEquals('foo', $fileLocator->lastTheme);
        $activeTheme->setName('bar');
        $fileLocator->locate('Resources/themes/foo/template', $this->getFixturePath(), true);
        $this->assertEquals('bar', $fileLocator->lastTheme);
    }

    /**
     * This verifies that the default view gets used if the currently active
     * one doesn't contain a matching file.
     *
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateViewFallback()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/defaultTemplate', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/views/defaultTemplate', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/defaultTemplate', '@Theme/defaultTemplate');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateAllFiles()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foobar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $expectedFiles = array(
            $this->getFixturePath().'/Resources/themes/foobar/template',
            $this->getFixturePath().'/Resources/views/template',
        );

        $files = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), false);
        $this->assertEquals($expectedFiles, $files);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/template', '@Theme/template');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateAppResource
     */
    public function testLocateAllFilesApp()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $expectedFiles = array(
            $this->getFixturePath().'/rootdir/Resources/themes/foo/template2',
            $this->getFixturePath().'/rootdir/Resources/views/template2',
        );

        $files = $fileLocator->locate('views/template2', null, false);
        $this->assertEquals($expectedFiles, $files);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateParentDelegation()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('Resources/themes/foo/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().DIRECTORY_SEPARATOR.'Resources/themes/foo/template', $file);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateRootDirectory()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('foo', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/rootTemplate', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/rootdir/Resources/themes/foo/LiipMockLocateRootDirectory/rootTemplate', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/rootTemplate', '@Theme/rootTemplate');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     */
    public function testLocateOverrideDirectory()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/override', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/rootdir/Resources/LiipMockLocateOverrideDirectory/views/override', $file);
        $this->assertResourcesEquals($fileLocator, '@ThemeBundle/Resources/views/override', '@Theme/override');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     * @expectedException RuntimeException
     */
    public function testLocateInvalidCharacter()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/../views/template', $this->getFixturePath(), true);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     * @expectedException RuntimeException
     */
    public function testLocateNoResource()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/bogus', $this->getFixturePath(), true);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     * @expectedException InvalidArgumentException
     */
    public function testLocateNotFound()
    {
        $kernel = $this->getKernelMock();
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));
        $fileLocator = new FileLocator($kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources');

        $file = $fileLocator->locate('@ThemeBundle/Resources/nonExistant', $this->getFixturePath(), true);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @covers Liip\ThemeBundle\Locator\FileLocator::locateBundleResource
     * @expectedException InvalidArgumentException
     */
    public function testLocateBundleInheritance()
    {
        $kernel = $this->getKernelMock(true);
        $activeTheme = new ActiveTheme('bar', array('foo', 'bar', 'foobar'));

        $fileLocator = $this->getMockBuilder('Liip\ThemeBundle\Locator\FileLocator')
            ->setConstructorArgs([$kernel, $activeTheme, $this->getFixturePath().'/rootdir/Resources'])
            ->setMethods(['getPathsForBundleResource'])
            ->getMock()
        ;

        $fileLocator->expects($this->at(0))
        ->method('getPathsForBundleResource')
        ->with($this->callback(function ($parameters) {
            return 'DerivedLiipMockLocateBundleInheritance' == $parameters['%bundle_name%'];
        }))
        ->will($this->returnValue(array()));

        $fileLocator->expects($this->at(1))
        ->method('getPathsForBundleResource')
        ->with($this->callback(function ($parameters) {
            return 'LiipMockLocateBundleInheritance' == $parameters['%bundle_name%'];
        }))
        ->will($this->returnValue(array()));

        $file = $fileLocator->locate('@ThemeBundle/Resources/nonExistant', $this->getFixturePath(), true);
    }

    /**
     * Asserts that two resources are equal paths.
     *
     * @param FileLocator $fileLocator
     * @param string      $expected
     * @param string      $actual
     * @param string      $message
     */
    private function assertResourcesEquals(FileLocator $fileLocator, $expected, $actual, $message = '')
    {
        $this->assertEquals($fileLocator->locate($expected, $this->getFixturePath(), true),
            $fileLocator->locate($actual, $this->getFixturePath(), true), $message);
    }
}
