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
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

use Liip\ThemeBundle\Helper\DeviceDetectionInterface;
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
     * @var array
     */
    protected $cookieOptions;

    /**
     * @var DeviceDetectionInterface
     */
    protected $autoDetect;

    /**
     * @var string
     */
    protected $newTheme;

    /**
     * @param ActiveTheme              $activeTheme
     * @param array                    $cookieOptions The options of the cookie we look for the theme to set
     * @param DeviceDetectionInterface $autoDetect    If to auto detect the theme based on the user agent
     */
    public function __construct(ActiveTheme $activeTheme, array $cookieOptions = null, DeviceDetectionInterface $autoDetect = null)
    {
        $this->activeTheme = $activeTheme;
        $this->autoDetect = $autoDetect;
        $this->cookieOptions = $cookieOptions;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            if ($this->autoDetect) {
                $this->autoDetect->setUserAgent($event->getRequest()->server->get('HTTP_USER_AGENT'));
            }

            $cookieValue = null;
            if (null !== $this->cookieOptions) {
                $cookieValue = $event->getRequest()->cookies->get($this->cookieOptions['name']);
            }

            if (!$cookieValue && $this->autoDetect instanceof DeviceDetectionInterface) {
                $cookieValue = $this->autoDetect->getType();
            }

            if ($cookieValue && $cookieValue !== $this->activeTheme->getName()
                && in_array($cookieValue, $this->activeTheme->getThemes())
            ) {
                $this->activeTheme->setName($cookieValue);
                // store into cookie
                if ($this->cookieOptions) {
                    $this->newTheme = $cookieValue;
                }
            }
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            // store into the cookie only if the controller did not already change the value
            if ($this->newTheme == $this->activeTheme->getName()) {
                $cookie = new Cookie(
                    $this->cookieOptions['name'],
                    $this->newTheme,
                    time() + $this->cookieOptions['lifetime'],
                    $this->cookieOptions['path'],
                    $this->cookieOptions['domain'],
                    (Boolean)$this->cookieOptions['secure'],
                    (Boolean)$this->cookieOptions['http_only']
                );
                $event->getResponse()->headers->setCookie($cookie);
            }
        }
    }
}
