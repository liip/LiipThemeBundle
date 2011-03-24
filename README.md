Theme Bundle
==========

This bundle provides you the possibility to add themes to each bundle. In your
bundle directory it will look under Resources/themes/<themename> or fall back
to the normal Resources/views if no matching file was found.

Installation
============

  1. Add this bundle to your project as Git submodules:

          $ git submodule add git://github.com/liip/ThemeBundle.git vendor/bundles/Liip/ThemeBundle

  2. Add the Liip namespace to your autoloader:

          // app/autoload.php
          $loader->registerNamespaces(array(
                'Liip' => __DIR__.'/../vendor/bundles',
                // your other namespaces
          ));

  3. Add this bundle to your application's kernel:

          // application/ApplicationKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Liip\ViewBundle\LiipThemeBundle(),
                  // ...
              );
          }

Configuration
-------------

You will have to set your possible themes and the currently active theme. It
is required that the active theme is part of the themes list.

# app/config.yml
    liip_theme:
        themes: ['web', 'tablet', 'mobile']
        activeTheme: 'web'
