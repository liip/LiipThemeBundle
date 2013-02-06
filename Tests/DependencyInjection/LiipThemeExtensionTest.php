<?php

namespace Liip\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Liip\ThemeBundle\DependencyInjection\LiipThemeExtension;

class LiipThemeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\ThemeBundle\LiipThemeBundle
     * @covers Liip\ThemeBundle\DependencyInjection\LiipThemeExtension::load
     * @covers Liip\ThemeBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $loader = new LiipThemeExtension();
        $config = $this->getConfig();
        $pathPatterns = array(
            'app_resource' => array(
                'app_resource_path'
            ),
            'bundle_resource' => array(
                'bundle_resource_path1',
                'bundle_resource_path2'
            ),
            'bundle_resource_dir' => array(
                'bundle_resource_dir_path'
            ),
        );
        $config['path_patterns'] = $pathPatterns;

        $loader->load(array($config), $container);

        $this->assertEquals(array('web', 'tablet', 'mobile'), $container->getParameter('liip_theme.themes'));
        $this->assertEquals('tablet', $container->getParameter('liip_theme.active_theme'));
        $this->assertEquals($pathPatterns, $container->getParameter('liip_theme.path_patterns'));
    }

    /**
     * @covers Liip\ThemeBundle\LiipThemeBundle
     * @covers Liip\ThemeBundle\DependencyInjection\LiipThemeExtension::load
     * @covers Liip\ThemeBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testLoadWithCookie()
    {
        $container = new ContainerBuilder();
        $loader = new LiipThemeExtension();
        $config = $this->getConfig();
        $config['cookie'] = array('name' => 'themeBundleCookie');
        $config['autodetect_theme'] = false;
        $loader->load(array($config), $container);

        $this->assertEquals(array('web', 'tablet', 'mobile'), $container->getParameter('liip_theme.themes'));
        $this->assertEquals('tablet', $container->getParameter('liip_theme.active_theme'));
        $this->assertEquals(array('name' => 'themeBundleCookie', 'lifetime' => 31536000, 'path' => '/', 'domain' => '', 'secure' => false, 'http_only' => false), $container->getParameter('liip_theme.cookie'));

        $listener = $container->get('liip_theme.theme_request_listener');
        $this->assertInstanceOf('Liip\ThemeBundle\EventListener\ThemeRequestListener', $listener);

        $tagged = $container->findTaggedServiceIds('kernel.event_listener');
        $this->assertArrayHasKey("liip_theme.theme_request_listener", $tagged);

        $foundKernelResponse = false;
        foreach ($tagged["liip_theme.theme_request_listener"] as $tag) {
            if ($tag['event'] == 'kernel.response') {
                $foundKernelResponse = $tag['method'];
                break;
            }
        }
        $this->assertEquals($foundKernelResponse, 'onKernelResponse');
    }

    /**
     * @covers Liip\ThemeBundle\LiipThemeBundle
     * @covers Liip\ThemeBundle\DependencyInjection\LiipThemeExtension::load
     * @covers Liip\ThemeBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testLoadWithAutodetectNoCookie()
    {
        $container = new ContainerBuilder();
        $loader = new LiipThemeExtension();
        $config = $this->getConfig();
        $config['autodetect_theme'] = true;
        $loader->load(array($config), $container);

        $listener = $container->get('liip_theme.theme_request_listener');
        $this->assertInstanceOf('Liip\ThemeBundle\EventListener\ThemeRequestListener', $listener);
        $tagged = $container->findTaggedServiceIds('kernel.event_listener');
        $this->assertArrayHasKey("liip_theme.theme_request_listener", $tagged);
    }


    protected function getConfig()
    {
        return array(
            'themes' => array('web', 'tablet', 'mobile'),
            'active_theme' => 'tablet',
        );
    }

}
