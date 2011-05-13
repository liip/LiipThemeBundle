<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Tests\Locator;

use Liip\ThemeBundle\Locator\FileLocator;

class FileLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected function getKernelMock($themes, $activeTheme)
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\Bundle')
            ->disableOriginalConstructor()
            ->getMock();
        $bundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($this->getFixturePath()));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('liip_theme.themes'))
            ->will($this->returnValue($themes));
        $container->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('liip_theme.activeTheme'))
            ->will($this->returnValue($activeTheme));

        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $kernel->expects($this->once())
            ->method('getContainer')
            ->will($this->returnValue($container));
        $kernel->expects($this->any())
            ->method('getBundle')
            ->will($this->returnValue(array($bundle)));
        return $kernel;
    }

    protected function getFixturePath()
    {
        return __DIR__ . '/../Fixtures';
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::__construct
     */
    public function testConstructor()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'bar');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructorWithInvalidTheme()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'non existant');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocate()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foo');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/themes/foo/template', $file);
    }

    /**
     * This verifies that the parent theme gets used if the currently active
     * one doesn't contain a matching file.
     *
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateStepupTheme()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'bar');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/themes/foo/template', $file);
    }

    /**
     * This verifies that the default view gets used if the currently active
     * one doesn't contain a matching file.
     *
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateViewFallback()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'bar');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/defaultTemplate', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/views/defaultTemplate', $file);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateAllFiles() {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foobar');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $expectedFiles = array(
            $this->getFixturePath().'/Resources/themes/foobar/template',
            $this->getFixturePath().'/Resources/themes/foo/template',
            $this->getFixturePath().'/Resources/views/template',
        );

        $files = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), false);
        $this->assertEquals($expectedFiles, $files);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateParentDelegation()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foo');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('Resources/themes/foo/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/themes/foo/template', $file);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     */
    public function testLocateRootDirectory()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foo');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/rootTemplate', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/rootdir/Resources/views/rootTemplate', $file);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @expectedException RuntimeException
     */
    public function testLocateInvalidCharacter()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foo');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/Resources/../views/template', $this->getFixturePath(), true);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @expectedException RuntimeException
     */
    public function testLocateNoResource()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foo');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/bogus', $this->getFixturePath(), true);
    }

    /**
     * @covers Liip\ThemeBundle\Locator\FileLocator::locate
     * @expectedException InvalidArgumentException
     */
    public function testLocateNotFound()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'bar');
        $fileLocator = new FileLocator($kernel, $this->getFixturePath() . '/rootdir');

        $file = $fileLocator->locate('@ThemeBundle/Resources/nonExistant', $this->getFixturePath(), true);
    }
}
