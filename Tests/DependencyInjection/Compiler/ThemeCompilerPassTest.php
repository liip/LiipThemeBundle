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
        $containerMock->expects($this->at(0))
            ->method('getDefinition')
            ->with($this->equalTo('templating.locator'))
            ->will($this->returnValue($definitionMock))
        ;

        $definitionMock->expects($this->at(0))
            ->method('replaceArgument')
            ->with($this->equalTo(0), $this->equalTo(new Reference('liip_theme.file_locator')))
        ;

        $themeCompiler = new ThemeCompilerPass();
        $themeCompiler->process($containerMock);
    }
}
