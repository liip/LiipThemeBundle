Theme Bundle
============

This bundle provides you the possibility to add themes to each bundle. In your
bundle directory it will look under `Resources/themes/<themename>` or fall back
to the normal Resources/views if no matching file was found.

[![Build Status](https://secure.travis-ci.org/liip/LiipThemeBundle.png)](http://travis-ci.org/liip/LiipThemeBundle)

## Installation

Installation is a quick (I promise!) 4 step process:

1. Download LiipThemeBundle
2. Configure the Autoloader
3. Enable the Bundle
4. Import LiipThemeBundle routing

### Step 1: Download LiipThemeBundle

Ultimately, the LiipThemeBundle files should be downloaded to the
`vendor/bundles/Liip/ThemeBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

```
[LiipThemeBundle]
    git=git://github.com/liip/LiipThemeBundle.git
    target=bundles/Liip/ThemeBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, the run the following:

``` bash
$ git submodule add git://github.com/liip/LiipThemeBundle.git vendor/bundles/Liip/ThemeBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the `Liip` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Liip' => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Liip\ThemeBundle\LiipThemeBundle(),
    );
}
```

### Step 4: Import LiipThemeBundle routing files

Now that you have activated and configured the bundle, all that is left to do is
import the LiipThemeBundle routing files.

In YAML:

``` yaml
# app/config/routing.yml
liip_theme:
    resource: "@LiipThemeBundle/Resources/config/routing.xml"
    prefix: /theme
```

Or if you prefer XML:

``` xml
<!-- app/config/routing.xml -->
<import resource="@LiipThemeBundle/Resources/config/routing.xml" prefix="/theme" />
```

## Configuration

You will have to set your possible themes and the currently active theme. It
is required that the active theme is part of the themes list.

``` yaml
# app/config/config.yml
liip_theme:
    themes: ['web', 'tablet', 'phone']
    active_theme: 'web'
```

### Optional

If you want to select the active theme based on a cookie you can add

``` yaml
# app/config/config.yml
liip_theme:
    cookie:
        name: NameOfTheCookie
        lifetime: 31536000 # 1 year in seconds
        path: /
        domain: ~
        secure: false
        http_only: false
```

It is also possible to automate setting the theme cookie based on the user agent:

``` yaml
# app/config/config.yml
liip_theme:
    autodetect_theme: true
```

Optionally ``autodetect_theme`` can also be set to a DIC service id that implements
the ``Liip\ThemeBundle\Helper\DeviceDetectionInterface`` interface.

### Theme Cascading Order

The following order is applied when checking for templates that live in a bundle, for example `@BundleName/Resources/template.html.twig`
with theme name ``phone`` is located at:

1. Override themes directory: `app/Resources/themes/phone/BundleName/template.html.twig`
2. Override view directory: `app/Resources/BundleName/views/template.html.twig`
3. Bundle theme directory: `src/BundleName/Resources/themes/phone/template.html.twig`
4. Bundle view directory: `src/BundleName/Resources/views/template.html.twig`

The following order is applied when checking for application-wide base templates, for example `::template.html.twig`
with theme name ``phone`` is located at:

1. Override themes directory: `app/Resources/themes/phone/template.html.twig`
2. Override view directory: `app/Resources/views/template.html.twig`

### Change Active Theme

For that matter have a look at the ThemeRequestListener.

If you are early in the request cycle and no template has been rendered you
can still change the theme without problems. For this the theme service
exists at:

``` php
$activeTheme = $container->get('liip_theme.active_theme');
echo $activeTheme->getName();
$activeTheme->setName("phone");
```

## Contribution

Active contribution and patches are very welcome. To keep things in shape we
have quite a bunch of unit tests. If you're submitting pull requests please
make sure that they are still passing and if you add functionality please
take a look at the coverage as well it should be pretty high :)

First initial vendors:

    php vendor/vendors.php

This will give you proper results:

``` bash
phpunit --coverage-html reports
```
