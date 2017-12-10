<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\ThemeBundle\Tests;

use Liip\ThemeBundle\LiipThemeBundle;

class LiipThemeBundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Liip\ThemeBundle\LiipThemeBundle::build
     */
    public function testBuild()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $containerMock->expects($this->exactly(3))
            ->method('addCompilerPass')
        ;

        $bundle = new LiipThemeBundle();
        $bundle->build($containerMock);
    }
}
