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

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\EventListener\ThemeControllerListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author David Buchmann <david@liip.ch>
 */
class ThemeControllerListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        $this->request = Request::create('/foo');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GetResponseEvent
     */
    private function getResponseEventMock()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        return $event;
    }

    public function testWithController()
    {
        $this->request->attributes->set('theme_controllers', array('a' => 'different_controller:fooAction'));
        $this->request->attributes->set('_route_params', array(
            'theme_controllers' => array('a' => 'different_controller:fooAction'),
            'foo' => 'bar',
        ));

        $activeTheme = new ActiveTheme('a', array('a', 'b'));
        $activeTheme->setName('a');

        $listener = new ThemeControllerListener($activeTheme);
        $listener->onKernelRequest($this->getResponseEventMock());

        $this->assertEquals('different_controller:fooAction', $this->request->attributes->get('_controller'));
        $this->assertFalse($this->request->attributes->has('theme_controllers'));
        $this->assertEquals(array('foo' => 'bar'), $this->request->attributes->get('_route_params'));
    }

    public function testWithoutController()
    {
        $this->request->attributes->set('_controller', 'original_controller:fooAction');
        $this->request->attributes->set('_route_params', array(
            'foo' => 'bar',
        ));

        $activeTheme = new ActiveTheme('b', array('a', 'b'));
        $activeTheme->setName('a');
        $listener = new ThemeControllerListener($activeTheme);
        $listener->onKernelRequest($this->getResponseEventMock());

        $this->assertEquals('original_controller:fooAction', $this->request->attributes->get('_controller'));
        $this->assertEquals(array('foo' => 'bar'), $this->request->attributes->get('_route_params'));
    }

    public function testWithoutParams()
    {
        $activeTheme = new ActiveTheme('b', array('a', 'b'));
        $activeTheme->setName('a');
        $listener = new ThemeControllerListener($activeTheme);
        $listener->onKernelRequest($this->getResponseEventMock());

        $this->assertFalse($this->request->attributes->has('_controller'));
        $this->assertFalse($this->request->attributes->has('_route_params'));
    }
}
