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
     * @var Liip\ThemeBundle\ActiveTheme
     */
    protected $activeTheme;

    /**
     * @var string
     */
    protected $cookieName;

    /**
     * @param ActiveTheme $activeTheme
     * @param string $cookieName The name of the cookie we look for the theme to set
     */
    public function __construct($activeTheme, $cookieName)
    {
        $this->activeTheme = $activeTheme;
        $this->cookieName = $cookieName;
    }

    /**
     * @param GetResponseEvent $event
     * @param ContainerBuilder $container
     */
     public function onKernelRequest(GetResponseEvent $event)
     {
         $activeCookie = $event->getRequest()->cookies->get($this->cookieName);
         if ($activeCookie && $activeCookie !== $this->activeTheme->getName() && in_array($activeCookie, $this->activeTheme->getThemes())) {
             $this->activeTheme->setName($activeCookie);
         }
     }
}
