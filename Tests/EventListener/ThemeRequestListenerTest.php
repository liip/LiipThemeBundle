<?php


/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Liip\Tests\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Liip\ThemeBundle\EventListener\ThemeRequestListener;

/**
 * Listens to the request and changes the active theme based on a cookie.
 *
 * @author Tobias Ebnöther <ebi@liip.ch>
 * @author Pascal Helfenstein <pascal@liip.ch>
 */
class ThemeRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $testCookieName = 'LiipThemeRequestCookieTestName';

    protected function getActiveThemeStub()
    {
        $activeTheme = $this->getMockBuilder('Liip\ThemeBundle\ActiveTheme')
            ->setConstructorArgs(array('desktop', array('desktop', 'tablet', 'mobile')))
            ->getMock();
        $activeTheme->expects($this->any())
            ->method('getThemes')
            ->will($this->returnValue(array('desktop', 'tablet', 'mobile')));
        return $activeTheme;
    }

    protected function getResponseEventMock($cookieReturnValue = null, $userAgent = null)
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->cookies = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->cookies->expects($this->any())
            ->method('get')
            ->will($this->returnValue($cookieReturnValue));
        $request->server = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->server->expects($this->any())
            ->method('get')
            ->will($this->returnValue($cookieReturnValue, $userAgent));

        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\ResponseHeaderBag')
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
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

    public function testWithCookie()
    {
        $activeTheme = $this->getActiveThemeStub();
        $activeTheme->expects($this->once())
            ->method('setName')
            ->with($this->equalTo('tablet'));
        $listener = new ThemeRequestListener($activeTheme, array('name' => $this->testCookieName));
        $listener->onKernelRequest($this->getResponseEventMock('tablet'));
    }

    public function testWithInvalidCookie()
    {
        $activeTheme = $this->getActiveThemeStub();
        $activeTheme->expects($this->never())
            ->method('setName');
        $listener = new ThemeRequestListener($activeTheme, array('name' => $this->testCookieName));
        $listener->onKernelRequest($this->getResponseEventMock('noActualTheme'));
    }
}
