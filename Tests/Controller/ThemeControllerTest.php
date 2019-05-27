<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Tests\Controller;

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Controller\ThemeController;
use Liip\ThemeBundle\Tests\Common\Comparator\SymfonyResponse as SymfonyResponseComparator;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ThemeControllerTest extends \PHPUnit\Framework\TestCase
{
    const WRONG_THEME = 'wrong_theme';
    const RIGHT_THEME = 'right_theme';
    const REFERER = 'some_referer';
    const DEFAULT_REDIRECT_URL = '/';

    /**
     * @var SymfonyResponseComparator
     */
    private $symfonyResponseComparator;

    /**
     * @dataProvider switchActionDataProvider
     * @param string|null $referer
     * @param RedirectResponse $expectedResponse
     */
    public function testSwitchAction($referer, RedirectResponse $expectedResponse)
    {
        $controller = $this->createThemeController(self::once());

        $actualResponse = $controller->switchAction($this->createRequest($referer));

        self::assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * @return mixed[]
     */
    public function switchActionDataProvider()
    {
        return array(
            'not empty referer' => array(
                'referer' => self::REFERER,
                'expectedResponse' => $this->createExpectedResponse(self::REFERER),
            ),
            'empty string as referer' => array(
                'referer' => '',
                'expectedResponse' => $this->createExpectedResponse(self::DEFAULT_REDIRECT_URL),
            ),
            'zero string as referer' => array(
                'referer' => '0',
                'expectedResponse' => $this->createExpectedResponse(self::DEFAULT_REDIRECT_URL),
            ),
            'zero int as referer' => array(
                'referer' => 0,
                'expectedResponse' => $this->createExpectedResponse(self::DEFAULT_REDIRECT_URL),
            ),
            'empty array as referer' => array(
                'referer' => array(),
                'expectedResponse' => $this->createExpectedResponse(self::DEFAULT_REDIRECT_URL),
            ),
            'null as referer' => array(
                'referer' => null,
                'expectedResponse' => $this->createExpectedResponse(self::DEFAULT_REDIRECT_URL),
            ),
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The theme "wrong_theme" does not exist
     */
    public function testSwitchActionWithNotFoundTheme()
    {
        $controller = $this->createThemeController(self::never());

        $controller->switchAction($this->createRequestWithWrongTheme());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->symfonyResponseComparator = new SymfonyResponseComparator();
        ComparatorFactory::getInstance()->register($this->symfonyResponseComparator);
    }

    protected function tearDown()
    {
        ComparatorFactory::getInstance()->unregister($this->symfonyResponseComparator);
        $this->symfonyResponseComparator = null;

        parent::tearDown();
    }

    /**
     * @param Invocation $activeThemeInvocation
     * @param mixed[]|null $cookieOptions
     * @return ThemeController
     */
    private function createThemeController(Invocation $activeThemeInvocation, array $cookieOptions = null)
    {
        return new ThemeController(
            $this->createActiveThemeMock($activeThemeInvocation),
            array(self::RIGHT_THEME),
            $cookieOptions
        );
    }

    /**
     * @param Invocation $invocation
     * @return ActiveTheme
     */
    private function createActiveThemeMock(Invocation $invocation)
    {
        $mock = $this->createMock('\Liip\ThemeBundle\ActiveTheme');

        $mock
            ->expects($invocation)
            ->method('setName')
            ->with(self::RIGHT_THEME)
        ;

        return $mock;
    }

    /**
     * @param string|null $referer
     * @return Request
     */
    private function createRequest($referer)
    {
        $request = new Request(array('theme' => self::RIGHT_THEME));

        $request->headers->add(
            array('Referer' => array($referer))
        );

        return $request;
    }

    /**
     * @return Request
     */
    private function createRequestWithWrongTheme()
    {
        return new Request(array('theme' => self::WRONG_THEME));
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    private function createExpectedResponse($url)
    {
        return new RedirectResponse($url);
    }
}
