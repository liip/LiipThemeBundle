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

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouterMock()
    {
        $router = $this->getMockBuilder('Symfony\Component\Routing\Router')
                     ->disableOriginalConstructor()
                     ->setMethods(['generate', 'supports', 'exists'])
                     ->getMock();

        $router->expects($this->any())
             ->method('generate')
             ->with('test_route')
             ->will($this->returnValue('/test_route'));

        return $router;
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
            $request->query->set('theme', 'cookie');
            $request->cookies->set('cookieName', 'cookie');
            $request->server->set('HTTP_USER_AGENT', 'autodetect');
        }

        $router = $this->getRouterMock();

        $controller = false;
        if ($config['load_controllers']) {
            $controller = new ThemeController($activeTheme, $config['themes'], $config['cookie'], $router, $config['redirect_fallback']);
        }

        $listener = new ThemeRequestListener($activeTheme, $config['cookie'], $device);
        $listener->onKernelRequest(
            $this->getEventMock($request, $response, 'Symfony\Component\HttpKernel\Event\GetResponseEvent')
        );
        $this->assertEquals($activeTheme->getName(), $assertion['themeAfterKernelRequest']);

        if ($controller) {
            $request->query->set('theme', $assertion['themeAfterController']);

            $response = $controller->switchAction(
                $request
            );
            $this->assertCookieValue($response, $assertion['themeAfterController']);
            $this->assertEquals($response->getTargetUrl(), $assertion['redirect']);
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
                    'redirect_fallback' => 'test_route',
                    'active_theme' => 'default',
                    'autodetect_theme' => true,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'cookie',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                    'redirect' => '/test_route',
                ),
                true,
            ),
            // autodetect if no cookie exists, but at the end controller wins
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'redirect_fallback' => 'test_route',
                    'active_theme' => 'default',
                    'autodetect_theme' => true,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'autodetect',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                    'redirect' => '/test_route',
                ),
                false,
            ),
            // no cookie pre-esist, so set autodect value into cookie if the controller is not called
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'redirect_fallback' => 'test_route',
                    'active_theme' => 'default',
                    'autodetect_theme' => true,
                    'load_controllers' => false,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'autodetect',
                    'themeAfterController' => 'autodetect',
                    'themeAfterKernelResponse' => 'autodetect',
                    'redirect' => '/test_route',
                ),
                false,
            ),
            // a cookie don't pre-esist, autodetection is disabled, controller is not called, set the cookie
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'redirect_fallback' => 'test_route',
                    'active_theme' => 'default',
                    'autodetect_theme' => false,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'default',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                    'redirect' => '/test_route',
                ),
                false,
            ),
            // a cookie pre-esist, autodetection is disabled, controller is not called, set the cookie
            array(
                array(
                    'themes' => array('default', 'controller', 'cookie', 'autodetect'),
                    'redirect_fallback' => 'test_route',
                    'active_theme' => 'default',
                    'autodetect_theme' => false,
                    'load_controllers' => true,
                    'cookie' => $this->getCookieOptions(),
                ),
                array(
                    'themeAfterKernelRequest' => 'cookie',
                    'themeAfterController' => 'controller',
                    'themeAfterKernelResponse' => 'controller',
                    'redirect' => '/test_route',
                ),
                true,
            ),
        );
    }
}
