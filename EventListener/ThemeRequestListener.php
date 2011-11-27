<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Cookie;

use Liip\ThemeBundle\Helper\MobileDetection;
use Liip\ThemeBundle\ActiveTheme;

/**
 * Listens to the request and changes the active theme based on a cookie.
 *
 * @author Tobias Ebnöther <ebi@liip.ch>
 * @author Pascal Helfenstein <pascal@liip.ch>
 */
class ThemeRequestListener
{
    /**
     * @var ActiveTheme
     */
    protected $activeTheme;

    /**
     * @var string
     */
    protected $cookieName;

    /**
     * @var Boolean
     */
    protected $autoDetect;

    /**
     * @param ActiveTheme $activeTheme
     * @param string $cookieName The name of the cookie we look for the theme to set
     * @param Boolean $autoDetect If to auto detect the theme based on the device
     */
    public function __construct($activeTheme, $cookieName, $autoDetect)
    {
        $this->activeTheme = $activeTheme;
        $this->cookieName = $cookieName;
        $this->autoDetect = $autoDetect;
    }

    /**
     * @param GetResponseEvent $event
     * @param ContainerBuilder $container
     */
     public function onKernelRequest(GetResponseEvent $event)
     {
         if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {

             $activeCookie = $event->getRequest()->cookies->get($this->cookieName);
             if (!$activeCookie && $this->autoDetect) {
                 $userAgent = $event->getRequest()->server->get('HTTP_USER_AGENT');

                 $detection = new MobileDetection($userAgent);
                 $cookie = new Cookie($this->cookieName, $detection->getType(), time()+60*60*24*365, '/', null, false, false);
                 $event->getResponse()->headers->setCookie($cookie);
                 $activeCookie = $detection->getType();
             }

             if ($activeCookie && $activeCookie !== $this->activeTheme->getName() && in_array($activeCookie, $this->activeTheme->getThemes())) {
                 $this->activeTheme->setName($activeCookie);
             }
         }
     }
}
