<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Tests\Assetic;

use Liip\ThemeBundle\Assetic\TwigFormulaLoader;
use Prophecy\Argument;

class TwigFormulaLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $twig;
    private $activeTheme;
    private $logger;

    public function setUp()
    {
        $this->twig = $this->prophesize('Twig_Environment');
        $this->activeTheme = $this->prophesize('Liip\ThemeBundle\ActiveTheme');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
        $this->resource = $this->prophesize('Assetic\Factory\Resource\ResourceInterface');
        $this->resourceContent = 'test';
        $this->resource->getContent()->willReturn($this->resourceContent);
        $this->resource->__toString()->willReturn('foo');

        $this->loader = new TwigFormulaLoader(
            $this->twig->reveal(),
            $this->logger->reveal(),
            $this->activeTheme->reveal()
        );
    }

    public function testLoader()
    {
        $this->activeTheme->getThemes()->willReturn(array(
            'theme1', 'theme2'
        ));

        $this->activeTheme->setName('theme1')->shouldBeCalled();
        $this->activeTheme->setName('theme2')->shouldBeCalled();

        $this->twig->tokenize($this->resourceContent)->shouldBeCalled();
        $this->loader->load($this->resource->reveal());
    }

    public function testExceptions()
    {
        $this->activeTheme->getThemes()->willReturn(array(
            'theme1'
        ));
        $this->activeTheme->setName('theme1')->shouldBeCalled();
        $this->twig->tokenize($this->resourceContent)->willThrow(new \Exception('foobar'));
        $this->logger->error('The template "foo" contains an error: "foobar"')->shouldBeCalled();

        $this->loader->load($this->resource->reveal());
    }
}
