Theme Bundle
============

This bundle provides you the possibility to add themes to each bundle. In your
bundle directory it will look under `Resources/themes/<themename>` or fall back
to the normal Resources/views if no matching file was found.

[![Build Status](https://secure.travis-ci.org/liip/LiipThemeBundle.png)](http://travis-ci.org/liip/LiipThemeBundle)

## Installation

Installation is a quick (I promise!) 3 step process:

1. Download LiipThemeBundle
2. Enable the Bundle
3. Import LiipThemeBundle routing

### Step 1: Install LiipThemeBundle with composer

Run the following composer require command:

``` bash
$ php composer.phar require liip/theme-bundle

```

### Step 2: Enable the bundle

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

### Step 3: Import LiipThemeBundle routing files

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
    themes: ['standardTheme', 'winter_theme', 'weekend']
    active_theme: 'standardTheme'
```

### Device specific themes/templates

You can provide specific themes or even templates for different devices (like: desktop, tablet, phone, plain). Set option ```autodetect_theme``` to true for setting ```current_device``` parameter based on the user agent:

``` yaml
# app/config/config.yml
liip_theme:
    autodetect_theme: true
```

Then in ```path_patterns``` you can use ```%%current_device%%``` parameter (with your device type as value)

``` yaml
# app/config/config.yml
liip_theme:
    path_patterns:
        app_resource:
            - %%app_path%%/themes/%%current_theme%%/%%current_device%%/%%template%%
            - %%app_path%%/themes/fallback_theme/%%current_device%%/%%template%%
            - %%app_path%%/views/%%current_device%%/%%template%%
```

Optionally ``autodetect_theme`` can also be set to a DIC service id that implements
the ``Liip\ThemeBundle\Helper\DeviceDetectionInterface`` interface.

### Get active theme information from cookie

If you want to select the active theme based on a cookie you can add:

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

### Disable controller based theme switching

If your application doesn't allow the user to switch theme, you can deactivate
the controller shipped with the bundle:

``` yaml
# app/config/config.yml
liip_theme:
    load_controllers: false
```

### Theme Cascading Order

The following order is applied when checking for templates that live in a bundle, for example `@BundleName/Resources/template.html.twig`
with theme name ``phone`` is located at:

1. Override themes directory: `app/Resources/themes/phone/BundleName/template.html.twig`
2. Override view directory: `app/Resources/BundleName/views/template.html.twig`
3. Bundle theme directory: `src/BundleName/Resources/themes/phone/template.html.twig`
4. Bundle view directory: `src/BundleName/Resources/views/template.html.twig`

For example, if you want to integrate some TwigBundle custom error pages regarding your theme
architecture, you will have to use this directory structure :
`app/Resources/themes/phone/TwigBundle/Exception/error404.html.twig`

The following order is applied when checking for application-wide base templates, for example `::template.html.twig`
with theme name ``phone`` is located at:

1. Override themes directory: `app/Resources/themes/phone/template.html.twig`
2. Override view directory: `app/Resources/views/template.html.twig`

#### Change Theme Cascading Order

You able change cascading order via configurations directives: `path_patterns.app_resource`, `path_patterns.bundle_resource`, `path_patterns.bundle_resource_dir`. For example:

``` yaml
# app/config/config.yml
liip_theme:
    path_patterns:
        app_resource:
            - %%app_path%%/themes/%%current_theme%%/%%template%%
            - %%app_path%%/themes/fallback_theme/%%template%%
            - %%app_path%%/views/%%template%%
        bundle_resource:
            - %%bundle_path%%/Resources/themes/%%current_theme%%_%%current_device%%/%%template%%
            - %%bundle_path%%/Resources/themes/%%current_theme%%/%%template%%
            - %%bundle_path%%/Resources/themes/fallback_theme/%%template%%
        bundle_resource_dir:
            - %%dir%%/themes/%%current_theme%%/%%bundle_name%%/%%template%%
            - %%dir%%/themes/fallback_theme/%%bundle_name%%/%%template%%
            - %%dir%%/%%bundle_name%%/%%override_path%%
```

##### Cascading Order Patterns Placeholders

<table>
  <tr>
    <th>Placeholder</th>
  <th>Representation</th>
  <th>Example</th>
  </tr>
  <tr>
    <td><code>%app_path%</code></td>
  <td>Path where application resources are located</td>
  <td><code>app/Resources</code></td>
  </tr>
  <tr>
    <td><code>%bundle_path%</code></td>
  <td>Path where bundle located, for example</td>
  <td><code>src/Vendor/CoolBundle/VendorCoolBundle</code></td>
  </tr>
  <tr>
    <td><code>%bundle_name%</code></td>
  <td>Name of the bundle</td>
  <td><code>VendorCoolBundle</code></td>
  </tr>
  <tr>
    <td><code>%dir%</code></td>
  <td>Directory, where resource should looking first</td>
  <td></td>
  </tr>
  <tr>
    <td><code>%current_theme%</code></td>
  <td>Name of the current active theme</td>
  <td></td>
  </tr>
  <tr>
      <td><code>%current_device%</code></td>
    <td>Name of the current device type</td>
    <td>desktop, phone, tablet, plain</td>
    </tr>
  <tr>
    <td><code>%template%</code></td>
  <td>Template name</td>
  <td><code>view.html.twig</code></td>
  </tr>
  <tr>
    <td><code>%override_path%</code></td>
  <td>Like template, but with views directory</td>
  <td><code>views/list.html.twig</code></td>
  </tr>
</table>


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

### Theme Specific Controllers

In some situations, a different template is not enough and you need a different 
controller for a specific theme. We encountered this with A/B testing. Do not 
abuse this feature and check whether your use case is still to be considered a 
theme.

This feature is not active by default as there is an additional request 
listener involved. Enable it by setting `theme_specific_controllers` in your 
configuration:


```yaml
# app/config/config.yml
liip_theme:
    # ...
    theme_specific_controllers: true
```

Now you can configure controllers per theme in your routing file:

```yaml
my_route:
    path: /x/y
    defaults:
        _controller: my_service:fooAction
        theme_controllers:
            a: my_other_service:fooAction
            b: App:Other:foo
```

As usual, you can use both the service notation or the namespace notation for
the controllers. Just specify the controller by theme under the key 
`theme_controllers`.

### Assetic integration

Because of the way the LiipThemeBundle overrides the template locator service,
assetic will only dump the assets of the active theme.

In order to dump the assets of all themes enable the ``assetic_integration``
option:

````yaml
# app/config/config.yml
liip_theme:
    # ...
    assetic_integration: true
````

This will override the Twig formula loader and iterate over all of the themes,
ensuring that all of the assets are dumped.

Note that this only works with AsseticBundle 2.1 or higher.

### Override the Device Detection

It is possible to override the service used for the device detection. Make sure to either
extend `DeviceDetection` or implement `DeviceDetectionInterface`:

````yaml
# app/config/config.yml
services:
    my_devcice_detection:
        class: SomeClass

liip_theme:
    # ...
    device_detection: my_devcice_detection
````

## Contribution

Active contribution and patches are very welcome. To keep things in shape we
have quite a bunch of unit tests. If you're submitting pull requests please
make sure that they are still passing and if you add functionality please
take a look at the coverage as well it should be pretty high :)

First install dependencies:

```bash
   composer.phar install --dev
```

This will give you proper results:

``` bash
phpunit --coverage-text
```
