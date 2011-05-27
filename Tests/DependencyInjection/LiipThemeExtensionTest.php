<?php

namespace Liip\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Liip\ThemeBundle\DependencyInjection\LiipThemeExtension;

class LiipThemeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\ThemeBundle\LiipThemeBundle
     * @covers Liip\ThemeBundle\DependencyInjection\LiipThemeExtension::load
     * @covers Liip\ThemeBundle\DependencyInjection\Configuration::getConfigTree
     */
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $loader = new LiipThemeExtension();
        $config = $this->getConfig();
        $loader->load(array($config), $container);

        $this->assertEquals(array('web', 'tablet', 'mobile'), $container->getParameter('liip_theme.themes'));
        $this->assertEquals('tablet', $container->getParameter('liip_theme.active_theme'));
    }

    protected function getConfig()
    {
        return array(
            'themes' => array('web', 'tablet', 'mobile'),
            'active_theme' => 'tablet',
        );
    }

}
