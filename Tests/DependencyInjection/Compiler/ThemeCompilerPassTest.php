<?php

namespace Liip\ThemeBundle\Tests\DependencyInjection;

use Liip\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass;

class ThemeCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Liip\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass::process
     */
    public function testProcess()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock->expects($this->exactly(2))
            ->method('setAlias')
        ;

        $containerMock->expects($this->exactly(2))
            ->method('getParameter')
            ->will($this->returnValueMap(
                    array(
                        array('liip_theme.cache_warming', true),
                        array('liip_theme.filesystem_loader.class', 'Liip\ThemeBundle\Twig\Loader\FilesystemLoader'),
                    )
                )
            )
        ;

        $containerMock->expects($this->once())
            ->method('findDefinition')
            ->with('twig.loader.filesystem')
            ->willReturn(
                $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        ;

        $themeCompiler = new ThemeCompilerPass();
        $themeCompiler->process($containerMock);
    }
}
