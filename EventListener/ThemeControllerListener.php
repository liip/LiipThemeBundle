<?php

namespace Liip\ThemeBundle\EventListener;

use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listener to rewrite controllers when configured.
 *
 * This can make sense e.g. when using themes to do A/B testing.
 *
 * @author David Buchmann <david@liip.ch>
 */
class ThemeControllerListener
{
    /**
     * @var ActiveTheme Information about the active theme
     */
    private $activeTheme;

    const ATTRIBUTE_KEY = 'theme_controllers';

    /**
     * @param ActiveTheme $activeTheme to know which theme is active.
     */
    public function __construct(ActiveTheme $activeTheme)
    {
        $this->activeTheme = $activeTheme;
    }

    /**
     * Kick in right after routing and check if the route defines options to
     * use a different controller for the new theme.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $theme = $this->activeTheme->getName();
        $controllers = $request->attributes->get(static::ATTRIBUTE_KEY);
        if (isset($controllers[$theme])) {
            $request->attributes->set('_controller', $controllers[$theme]);
        }

        // Get rid of the attribute in the request to avoid it showing up in generated URLs
        $request->attributes->remove(static::ATTRIBUTE_KEY);
        $routeParams = $request->attributes->get('_route_params');
        if (null !== $routeParams) {
            unset($routeParams[static::ATTRIBUTE_KEY]);
            $request->attributes->set('_route_params', $routeParams);
        }
    }
}
