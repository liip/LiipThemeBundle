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

    public function testConstructor()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'bar');
        $fileLocator = new FileLocator($kernel);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorWithInvalidTheme()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'non existant');
        $fileLocator = new FileLocator($kernel);
    }

    public function testLocate()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'foo');
        $fileLocator = new FileLocator($kernel);

        $file = $fileLocator->locate('@ThemeBundle/Resources/views/template', $this->getFixturePath(), true);
        $this->assertEquals($this->getFixturePath().'/Resources/themes/foo/template', $file);
    }
}
