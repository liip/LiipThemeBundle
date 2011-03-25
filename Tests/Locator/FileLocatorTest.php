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
        return $kernel;
    }

    public function testConstructor()
    {
        $kernel =  $this->getKernelMock(array('foo', 'bar', 'foobar'), 'bar');
        $fileLocator = new FileLocator($kernel);
    }
}
