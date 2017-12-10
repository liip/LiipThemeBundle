<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\ThemeBundle\Tests;

use Liip\ThemeBundle\Helper\DeviceDetection;
use Liip\ThemeBundle\ActiveTheme;

class ActiveThemeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Liip\ThemeBundle\ActiveTheme::__construct
     * @covers Liip\ThemeBundle\ActiveTheme::getName
     */
    public function testGetName()
    {
        $theme = new ActiveTheme('foo', array('foo'));

        $this->assertEquals('foo', $theme->getName());
    }

    /**
     * @covers Liip\ThemeBundle\ActiveTheme::__construct
     * @covers Liip\ThemeBundle\ActiveTheme::getName
     */
    public function testDeviceDesktop()
    {
        $theme = new ActiveTheme('foo', array('foo'), new DeviceDetection('Mac OS X'));
        $this->assertEquals('desktop', $theme->getDeviceType());
    }

    /**
     * @covers Liip\ThemeBundle\ActiveTheme::__construct
     * @covers Liip\ThemeBundle\ActiveTheme::getName
     */
    public function testDevicePhone()
    {
        $theme = new ActiveTheme('foo', array('foo'), new DeviceDetection('iphone'));
        $this->assertEquals('phone', $theme->getDeviceType());
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers Liip\ThemeBundle\ActiveTheme::getName
     */
    public function testConstructInvalidName()
    {
        $theme = new ActiveTheme('foo', array('bar'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers Liip\ThemeBundle\ActiveTheme::setName
     */
    public function testSetInvalidName()
    {
        $theme = new ActiveTheme('foo', array('foo'));
        $theme->setName('bar');
    }
}
