<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Tests\Helper;

use Liip\ThemeBundle\Helper\DeviceDetection;

class DeviceDetectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::__construct
     */
    public function testConstructWithoutUserAgent()
    {
        $device = new DeviceDetection('');
        $this->assertFalse($device->isPhone(), 'No user agent defined, should not get recognized as mobile');
        $this->assertFalse($device->isTablet(), 'No user agent defined, should not get recognized as tablet');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::__call
     */
    public function testCall()
    {
        $device = new DeviceDetection('Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $this->assertTrue($device->isIpad(), 'Call function returns true, isIpad');
        $this->assertFalse($device->isIphone(), 'Call function returns false, is not an iPhone');
    }

    public function testCallError()
    {
        if (class_exists('\PHPUnit\Framework\Error\Warning')) {
            $this->expectException('\PHPUnit\Framework\Error\Warning');
        } else {
            $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        }
        $device = new DeviceDetection('Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $device->nonExistentMethod();
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isTablet
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testConstructIpad()
    {
        $device = new DeviceDetection('Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $this->assertEquals('tablet', $device->getType(), 'iPad type is set to tablet');
        $this->assertEquals('ipad', $device->getDevice(), 'ipad should get recognized as ipad');
        $this->assertTrue($device->isTablet(), 'Ipad should get set as tablet');
        $this->assertTrue($device->isIpad(), 'Ipad should get recognized as ipad');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testConstructIphone()
    {
        $device = new DeviceDetection('Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5');
        $this->assertEquals('phone', $device->getType(), 'iPhone type is set to phone');
        $this->assertEquals('iphone', $device->getDevice(), 'iphone should get recognized as iphone');
        $this->assertTrue($device->isPhone(), 'Iphone should get set as phone');
        $this->assertTrue($device->isIphone(), 'Iphone should get recognized as iphone');
        $this->assertFalse($device->isAndroid(), 'Iphone should not get recognized as Android');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDeviceAndroid()
    {
        $device = new DeviceDetection('Mozilla/5.0 (Linux; U; Android 2.3.3; en-us; GT-I9100 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        $this->assertEquals('phone', $device->getType(), 'Galaxy s2  type is set to phone');
        $this->assertEquals('android', $device->getDevice(), 'Galaxy s2 should get recognized as Android');
        $this->assertTrue($device->isPhone(), 'Galaxy s2 should get recognized as phone');
        $this->assertTrue($device->isAndroid(), 'Galaxy s2 should get recognized as Android');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDeviceBlackberry()
    {
        $device = new DeviceDetection('Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+');
        $this->assertEquals('blackberry', $device->getDevice(), 'BlackBerry  type is set to phone');
        $this->assertEquals('phone', $device->getType(), 'BlackBerry  type is set to phone');
        $this->assertTrue($device->isPhone(), 'BlackBerry should get recognized as phone');
        $this->assertTrue($device->isBlackberry(), 'BlackBerry should get recognized as BlackBerry');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDeviceWindowsPhone()
    {
        $device = new DeviceDetection('Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; HTC; 7 Trophy)');
        $this->assertEquals('windowsphone', $device->getDevice(), 'windowsphone  type is set to windowsphone');
        $this->assertEquals('phone', $device->getType(), 'windowsphone  type is set to phone');
        $this->assertTrue($device->isPhone(), 'windowsphone should get recognized as phone');
        $this->assertTrue($device->isWindowsphone(), 'windowsphone should get recognized as windowsphone');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDevicePalm()
    {
        $device = new DeviceDetection('Mozialla/5.0 (webOS/1.4.5; U; en-US) AppleWebKit/532.2 (KHTML, like Gecko) Version/1.0 Safari/532.2 Pre/1.0');
        $this->assertEquals('palm', $device->getDevice(), 'palm  type is set to palm');
        $this->assertEquals('phone', $device->getType(), 'palm  type is set to phone');
        $this->assertTrue($device->isPhone(), 'palm should get recognized as phone');
        $this->assertTrue($device->isPalm(), 'palm should get recognized as palm');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDeviceNokiaN8()
    {
        $device = new DeviceDetection('Mozilla/5.0 (Symbian/3; Series60/5.2 NokiaN8-00/011.012; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.2.7.2 3gpp-gba');
        $this->assertEquals('generic', $device->getDevice(), 'nokia  type is set to generic');
        $this->assertEquals('phone', $device->getType(), 'nokia  type is set to phone');
        $this->assertTrue($device->isPhone(), 'nokia should get recognized as phone');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isTablet
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDeviceGalaxyTab()
    {
        /*
        * Galaxy Tab gets recognized as Phone
        */
        $device = new DeviceDetection('Mozilla/5.0 (Linux; U; Android 2.2; de-de; GT-P1000 Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        $this->assertEquals('android', $device->getDevice(), 'Galaxy Tab  type is set to android');
        $this->assertEquals('phone', $device->getType(), 'Galaxy Tab  type is set to phone');
        $this->assertTrue($device->isPhone(), 'Galaxy Tab should get recognized as phone');
        $this->assertTrue($device->isAndroid(), 'Galaxy Tab should get recognized as android');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isDesktop
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckDeviceDesktop()
    {
        $device = new DeviceDetection('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:8.0.1) Gecko/20100101 Firefox/8.0.1 FirePHP/0.6');
        $this->assertEquals('osx', $device->getDevice(), 'Firefox type is set to desktop');
        $this->assertEquals('desktop', $device->getType(), 'Firefox type is set to desktop');
        $this->assertTrue($device->isDesktop(), 'Firefox should get recognized as desktop');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckOperaClassicMobile()
    {
        $device = new DeviceDetection('Opera/9.80 (Android 2.3.7; Linux; Opera Mobi/46154) Presto/2.11.355 Version/12.10');
        $this->assertEquals('phone', $device->getType(), 'Opera Mobile android is set to phone');
        $this->assertEquals('android', $device->getDevice(), 'Opera mobile android should get recognized as Android');
        $this->assertTrue($device->isPhone(), 'Opera Mobile Android should get recognized as phone');
        $this->assertTrue($device->isAndroid(), 'Opera Mobile Android should get recognized as Android');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckOperaClassicTablet()
    {
        $device = new DeviceDetection('Opera/9.80 (Android 2.3.7; Linux; Opera Tablet/46154) Presto/2.11.355 Version/12.10');
        $this->assertEquals('tablet', $device->getType(), 'Opera Mobile android is set to tablet');
        $this->assertEquals('androidtablet', $device->getDevice(), 'Opera mobile android should get recognized as Android');
        $this->assertTrue($device->isTablet(), 'Opera Mobile Android should get recognized as tablet');
        $this->assertTrue($device->isAndroidTablet(), 'Opera Mobile Android should get recognized as Android');
    }

    /**
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::determineDevice
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::isPhone
     * @covers Liip\ThemeBundle\Helper\DeviceDetection::getDevice
     */
    public function testCheckOperaMobile()
    {
        $device = new DeviceDetection('Opera/9.80 (Android; Opera Mini/16.0.1212/31.1475; U; fr) Presto/2.8.119 Version/11.10)');
        $this->assertEquals('phone', $device->getType(), 'Opera Mobile android is set to phone');
        $this->assertEquals('android', $device->getDevice(), 'Opera mobile android should get recognized as Android');
        $this->assertTrue($device->isPhone(), 'Opera Mobile Android should get recognized as phone');
        $this->assertTrue($device->isAndroid(), 'Opera Mobile Android should get recognized as Android');
    }
}
