Theme Bundle
==========

This bundle provides you the possibility to add themes to each bundle. In your
bundle directory it will look under Resources/themes/<themename> or fall back
to the normal Resources/views if no matching file was found.



Installation
============

With bin/vendors.sh
-------------

  1. Add this bundle to your project inside bin/vendors.sh:

          # Liip ThemeBundle
          mkdir -p $BUNDLES/Liip
          cd $BUNDLES/Liip
          install_git ThemeBundle git://github.com/liip/LiipThemeBundle.git

As submodule
-------------

  1. Add this bundle as submodule
          $ git submodule add http://github.com/liip/LiipThemeBundle.git vendor/bundles/Liip/ThemeBundle

  2. Add the Liip namespace to your autoloader:

          // app/autoload.php
          $loader->registerNamespaces(array(
                'Liip' => __DIR__.'/../vendor/bundles',
                // your other namespaces
          ));

  3. Add this bundle to your application's kernel:

          // app/AppKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Liip\ThemeBundle\LiipThemeBundle(),
                  // ...
              );
          }

Configuration
=============

You will have to set your possible themes and the currently active theme. It
is required that the active theme is part of the themes list.

    # app/config/config.yml
        liip_theme:
            themes: ['web', 'tablet', 'mobile']
            active_theme: 'web'

Optional
--------

If you want to select the active theme based on a cookie you can add

    # app/config/config.yml
        liip_theme:
            theme_cookie: cookieName

Theme Cascading Order
---------------------

The following order is applied when checking for templates, for example "@BundleName/Resources/template.html.twig"
is located at:

1. Override themes directory: app/Resources/themes/BundleName/template.html.twig
2. Override view directory: app/Resources/BundleName/views/template.html.twig
3. Bundle theme directory: src/BundleName/Resources/themes/template.html.twig
4. Bundle view directory: src/BundleName/Resources/views/template.html.twig

Change Active Theme
-------------------

For that matter have a look at the ThemeRequestListener.

If you are early in the request cycle and no template has been rendered you
can still change the theme without problems. For this the theme service
exists at:

    $activeTheme = $container->get('liip_theme.active_theme');
    echo $activeTheme->getName();
    $activeTheme->setName("mobile");

Contribution
==========
Active contribution and patches are very welcome. To keep things in shape we
have quite a bunch of unit tests. If you're submitting pull requests please
make sure that they are still passing and if you add functionality please
take a look at the coverage as well it should be pretty high :)

This will give you proper results:

    phpunit --coverage-html reports

Now you can open reports/index.html to see the coverage.
=======
