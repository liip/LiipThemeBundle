<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Liip\ThemeBundle\DependencyInjection\Compiler\AsseticTwigFormulaPass;

class AsseticTwigFormulaPassTest extends \PHPUnit\Framework\TestCase
{
    private $container;
    private $definition;
    private $pass;

    public function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->pass = new AsseticTwigFormulaPass();
    }

    /**
     * @covers Liip\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass::process
     */
    public function testProcess()
    {
        $this->container->expects($this->once())
            ->method('hasParameter')
            ->with('liip_theme.assetic_integration')
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with('kernel.bundles')
            ->will($this->returnValue(array('AsseticBundle' => true)));
        $this->container->expects($this->once())
            ->method('getDefinition')
            ->with('assetic.twig_formula_loader.real')
            ->will($this->returnValue($this->definition));
        $this->definition->expects($this->once())
            ->method('setClass')
            ->with('Liip\ThemeBundle\Assetic\TwigFormulaLoader');
        $this->definition->expects($this->once())
            ->method('addArgument');

        $this->pass->process($this->container);
    }

    /**
     * @expectedException Symfony\Component\DependencyInjection\Exception\RuntimeException
     */
    public function testNoBundle()
    {
        $this->container->expects($this->once())
            ->method('hasParameter')
            ->with('liip_theme.assetic_integration')
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with('kernel.bundles')
            ->will($this->returnValue(array()));
        $this->pass->process($this->container);
    }
}
