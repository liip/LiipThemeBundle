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

    protected function getResponseEventMock($cookieReturnValue = null)
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
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        return $event;
    }

    public function testNoCookie()
    {
        $activeTheme = $this->getActiveThemeStub();
        $activeTheme->expects($this->never())
            ->method('setName');
        $listener = new ThemeRequestListener($activeTheme, $this->testCookieName);
        $listener->onCoreRequest($this->getResponseEventMock());
    }

    public function testWithCookie()
    {
        $activeTheme = $this->getActiveThemeStub();
        $activeTheme->expects($this->once())
            ->method('setName')
            ->with($this->equalTo('tablet'));
        $listener = new ThemeRequestListener($activeTheme, $this->testCookieName);
        $listener->onCoreRequest($this->getResponseEventMock('tablet'));
    }

    public function testWithInvalidCookie()
    {
        $activeTheme = $this->getActiveThemeStub();
        $activeTheme->expects($this->never())
            ->method('setName');
        $listener = new ThemeRequestListener($activeTheme, $this->testCookieName);
        $listener->onCoreRequest($this->getResponseEventMock('noActualTheme'));
    }
}
