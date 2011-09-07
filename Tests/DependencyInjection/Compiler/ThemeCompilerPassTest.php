<?php

namespace Liip\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Liip\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass;

class ThemeCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass::process
     */
    public function testProcess()
    {
        $definitionMock = $this->getMock('Symfony\Component\DependencyInjection\Definition');

        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerMock->expects($this->exactly(2))
            ->method('setAlias')
        ;

        $themeCompiler = new ThemeCompilerPass();
        $themeCompiler->process($containerMock);
    }
}
