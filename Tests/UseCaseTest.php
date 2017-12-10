<?php


/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Tests\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Liip\ThemeBundle\EventListener\ThemeRequestListener;
use Liip\ThemeBundle\Controller\ThemeController;
use Liip\ThemeBundle\ActiveTheme;

/**
 * Bundle Functional tests.
 *
 * @author Giulio De Donato <liuggio@gmail.com>
 */
class UseCaseTest extends \PHPUnit\Framework\TestCase
{
    protected $testCookieName = 'LiipThemeRequestCookieTestName';

    protected function getDeviceDetectionMock($getType)
    {
        $device = $this->getMockBuilder('Liip\ThemeBundle\Helper\DeviceDetectionInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $device->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($getType));
        $device->expects($this->any())
            ->method('setUserAgent');

        return $device;
    }

    /**
     * Get the mock of the GetResponseEvent and FilterResponseEvent.
     *
     * @param \Symfony\Component\HttpFoundation\Request       $request
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @param string                                          $type     could be Symfony\Component\HttpKernel\Event\GetResponseEvent or 'Symfony\Component\HttpKernel\Event\FilterResponseEvent'
     *
     * @return mixed
     */
    protected function getEventMock($request, $response, $type = 'Symfony\Component\HttpKernel\Event\GetResponseEvent')
    {
        $event = $this->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        return $event;
    }

    protected function getMockRequest($theme, $cookieReturnValue = 'cookie', $userAgent = 'autodetect')
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->any())
            ->method('get')
            ->will($this->returnValue($theme));
        $request->cookies = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->cookies->expects($this->any())
            ->method('get')
            ->will($this->returnValue($cookieReturnValue));
        $request->headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->headers->expects($this->any())
            ->method('get')
            ->will($this->returnValue('/'));
        $request->headers->expects($this->any())
            ->method('get')
            ->will($this->returnValue($cookieReturnValue));

        $request->server = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->server->expects($this->any())
            ->method('get')
            ->will($this->returnValue($userAgent));

        return $request;
    }

    private function getCookieValueFromResponse($response)
    {
        $cookies = $response->headers->getCookies();

        return $cookies[0]->getValue();
    }

    private function assertCookieValue($response, $cookieValue)
    {
        $this->assertEquals($this->getCookieValueFromResponse($response), $cookieValue);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testThemeAction($config, $assertion, $hasAlreadyACookie = true)
    {
        $activeTheme = new ActiveTheme($config['active_theme'], $config['themes']);

        $device = null;
        if ($config['autodetect_theme']) {
            $device = $this->getDeviceDetectionMock('autodetect');
        }
        $response = new \Symfony\Component\HttpFoundation\Response();
        $request = new \Symfony\Component\HttpFoundation\Request();

        if ($hasAlreadyACookie) {
            $request = $this->getMockRequest('cookie');
        }

        $controller = false;
        if ($config['load_controllers']) {
            $controller = new ThemeController($activeTheme, $config['themes'], $config['cookie']);
        }

        $listener = new ThemeRequestListener($activeTheme, $config['cookie'], $device);
        $listener->onKernelRequest(
            $this->getEventMock($request, $response, 'Symfony\Component\HttpKernel\Event\GetResponseEvent')
        );
        $this->assertEquals($activeTheme->getName(), $assertion['themeAfterKernelRequest']);

        if ($controller) {
            $response = $controller->switchAction(
                $this->getMockRequest($assertion['themeAfterController'])
            );
            $this->assertCookieValue($response, $assertion['themeAfterController']);
        }

        // onResponse will not set the cookie if the controller modified it
        $listener->onKernelResponse(
            $this->getEventMock($request, $response, 'Symfony\Component\HttpKernel\Event\FilterResponseEvent')
        );

        $this->assertCookieValue($response, $assertion['themeAfterKernelResponse']);
    }

    private function getCookieOptions()
    {
        return array(
            'name' => 'cookieName',
            'lifetime' => 1000,
            'path' => '',
            'domain' => '',
            'secure' => false,
            'http_only' => false,
        );
    }

    public function dataProvider()
    {
        return array(
            array(
                // all-in Controller wins over Cookie and Autodetection
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'active_theme' => 'default',
                    'autodetect_theme' => true,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'cookie',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                ),
                true,
            ),
            // autodetect if no cookie exists, but at the end controller wins
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'active_theme' => 'default',
                    'autodetect_theme' => true,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'autodetect',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                ),
                false,
            ),
            // no cookie pre-esist, so set autodect value into cookie if the controller is not called
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'active_theme' => 'default',
                    'autodetect_theme' => true,
                    'load_controllers' => false,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'autodetect',
                    'themeAfterController' => 'autodetect',
                    'themeAfterKernelResponse' => 'autodetect',
                ),
                false,
            ),
            // a cookie don't pre-esist, autodetection is disabled, controller is not called, set the cookie
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'active_theme' => 'default',
                    'autodetect_theme' => false,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'default',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                ),
                false,
            ),
            // a cookie pre-esist, autodetection is disabled, controller is not called, set the cookie
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'active_theme' => 'default',
                    'autodetect_theme' => false,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'cookie',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                ),
                true,
            ),

        );
    }
}
