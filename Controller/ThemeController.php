<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Controller;

use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Theme controller
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ThemeController
{
    protected $activeTheme;

    /**
     * Available themes
     * 
     * @var array
     */
    protected $themes;

    /**
     * Options of the cookie to store active theme
     * 
     * @var array
     */
    protected $cookieOptions;

    /**
     * Theme controller construct
     * 
     * @param ActiveTheme $activeTheme   active theme instance
     * @param array       $themes        Available themes
     * @param array       $cookieOptions The options of the cookie we look for the theme to set
     */
    public function __construct(ActiveTheme $activeTheme, array $themes, array $cookieOptions)
    {
        $this->activeTheme    = $activeTheme;
        $this->themes         = $themes;
        $this->cookieOptions  = $cookieOptions;
    }

    /**
     * Switch theme
     *
     * @param Request $request actual request
     *
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException when theme name not exists
     */
    public function switchAction(Request $request)
    {
        $theme = $request->get('theme');

        if (!in_array($theme, $this->themes)) {
            throw new NotFoundHttpException(sprintf('The theme "%s" does not exist', $theme));
        }

        $this->activeTheme->setName($theme);

        $url = $request->headers->get('Referer', '/');

        $cookie = new Cookie(
            $this->cookieOptions['name'],
            $theme,
            time() + $this->cookieOptions['lifetime'],
            $this->cookieOptions['path'],
            $this->cookieOptions['domain'],
            (Boolean) $this->cookieOptions['secure'],
            (Boolean) $this->cookieOptions['http_only']
        );

        $response = new RedirectResponse($url);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
