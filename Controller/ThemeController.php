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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Theme controller.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ThemeController
{
    protected $activeTheme;

    /**
     * Available themes.
     *
     * @var array
     */
    protected $themes;

    /**
     * Options of the cookie to store active theme.
     *
     * @var array|null
     */
    protected $cookieOptions;

    /**
     * Theme controller construct.
     *
     * @param ActiveTheme $activeTheme   active theme instance
     * @param array       $themes        Available themes
     * @param array|null  $cookieOptions The options of the cookie we look for the theme to set
     */
    public function __construct(ActiveTheme $activeTheme, array $themes, array $cookieOptions = null)
    {
        $this->activeTheme = $activeTheme;
        $this->themes = $themes;
        $this->cookieOptions = $cookieOptions;
    }

    /**
     * Switch theme.
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

        $response = new RedirectResponse($this->extractUrl($request));

        if (!empty($this->cookieOptions)) {
            $cookie = new Cookie(
                $this->cookieOptions['name'],
                $theme,
                $request->server->get('REQUEST_TIME') + $this->cookieOptions['lifetime'],
                $this->cookieOptions['path'],
                $this->cookieOptions['domain'],
                (bool) $this->cookieOptions['secure'],
                (bool) $this->cookieOptions['http_only']
            );

            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function extractUrl(Request $request)
    {
        $url = $request->headers->get('Referer');

        return empty($url) ? '/' : $url;
    }
}
