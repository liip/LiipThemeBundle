<?php

namespace Liip\ThemeBundle\Tests\Common\Comparator;

use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\Factory;
use SebastianBergmann\Comparator\ObjectComparator;
use Symfony\Component\HttpFoundation\Response;

class SymfonyResponse extends Comparator
{
    const PREDEFINED_DATE = 'Tue, 15 Nov 1994 08:12:31 GMT';

    /**
     * @var ObjectComparator
     */
    private $inner;

    public function __construct()
    {
        parent::__construct();

        $this->inner = new ObjectComparator();
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($expected, $actual)
    {
        return
            $expected instanceof Response
                &&
            $actual instanceof Response
                &&
            $this->inner->accepts($expected, $actual);
    }

    /**
     * @param Response $expected
     * @param Response $actual
     * {@inheritdoc}
     */
    public function assertEquals($expected, $actual, $delta = 0, $canonicalize = false, $ignoreCase = false)
    {
        $expected->headers->set('Date', self::PREDEFINED_DATE);
        $actual->headers->set('Date', self::PREDEFINED_DATE);

        $this->inner->assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);
    }

    /**
     * {@inheritdoc}
     */
    public function setFactory(Factory $factory)
    {
        $this->inner->setFactory($factory);
    }
}
