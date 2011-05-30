<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Tests;

use Liip\ThemeBundle\LiipThemeBundle;

class LiipThemeBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\ThemeBundle\LiipThemeBundle::build
     */
    public function testBuild()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerMock->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass'))
        ;

        $bundle = new LiipThemeBundle();
        $bundle->build($containerMock);
    }
}
