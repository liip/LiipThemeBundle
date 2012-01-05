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

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Theme controller
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ThemeController extends ContainerAware
{
    /**
     * Switch theme
     *
     * @param string $theme theme name to switch to
     *
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException when theme name not exists
     */
    public function switchAction($theme)
    {
        $activeTheme = $this->container->get('liip_theme.active_theme');
        $themes      = $this->container->getParameter('liip_theme.themes');
        $cookieName  = $this->container->getParameter('liip_theme.theme_cookie');

        if (!in_array($theme, $themes)) {
            throw new NotFoundHttpException(sprintf('The theme "%s" does not exist', $theme));
        }

        $activeTheme->setName($theme);

        $url = $this->container->get('request')->headers->get('Referer');
        $cookie = new Cookie($cookieName, $theme, time()+60*60*24*365, '/', null, false, false);

        $response = new RedirectResponse($url);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
