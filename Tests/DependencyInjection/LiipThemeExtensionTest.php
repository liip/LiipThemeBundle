<?php

namespace Liip\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Liip\ThemeBundle\DependencyInjection\LiipThemeExtension;

class LiipThemeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\ThemeBundle\DependencyInjection\LiipThemeExtension::load
     * @covers Liip\ThemeBundle\LiipThemeBundle
     * @expectedException \RuntimeException
     */
    public function testLoadFailure()
    {
        $loader = new LiipThemeExtension();
        $config = $this->getConfig();
        unset($config['themes']);
        $loader->load(array($config), new ContainerBuilder());
    }

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
        $this->assertEquals('tablet', $container->getParameter('liip_theme.activeTheme'));
    }

    protected function getConfig()
    {
        $yaml = <<<EOF
themes: ['web', 'tablet', 'mobile']
activeTheme: 'tablet'
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

}
