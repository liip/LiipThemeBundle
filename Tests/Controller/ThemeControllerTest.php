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
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThemeControllerTest extends \PHPUnit\Framework\TestCase
{
    const WRONG_THEME = 'wrong_theme';
    const RIGHT_THEME = 'right_theme';
    const REFERER = 'some_referer';
    const DEFAULT_REDIRECT_URL = '/';
    const COOKIE_NAME = 'some_cookie_name';
    const COOKIE_LIFETIME = 112233;
    const COOKIE_PATH = '/';
    const COOKIE_DOMAIN = 'some_domain';
    const IS_COOKIE_SECURE = false;
    const IS_COOKIE_HTTP_ONLY = false;
    const REQUEST_TIME = 123;

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
     * @dataProvider switchActionDataProvider
     * @param string|null $referer
     * @param RedirectResponse $expectedResponse
     */
    public function testSwitchActionWithCookieOptions($referer, RedirectResponse $expectedResponse)
    {
        $controller = $this->createThemeController(self::once(), $this->createCookieOptions());

        $actualResponse = $controller->switchAction($this->createRequest($referer));

        self::assertEquals($this->addCookie($expectedResponse), $actualResponse);
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

        $request->server->add(
            array('REQUEST_TIME' => self::REQUEST_TIME)
        );

        return $request;
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    private function createExpectedResponse($url)
    {
        return new RedirectResponse($url);
    }

    /**
     * @return mixed[]
     */
    private function createCookieOptions()
    {
        return array(
            'name' => self::COOKIE_NAME,
            'lifetime' => self::COOKIE_LIFETIME,
            'path' => self::COOKIE_PATH,
            'domain' => self::COOKIE_DOMAIN,
            'secure' => self::IS_COOKIE_SECURE,
            'http_only' => self::IS_COOKIE_HTTP_ONLY,
        );
    }

    /**
     * @return Request
     */
    private function createRequestWithWrongTheme()
    {
        return new Request(array('theme' => self::WRONG_THEME));
    }

    /**
     * @param Response $response
     * @return Response
     */
    private function addCookie(Response $response)
    {
        $response->headers->setCookie(
            new Cookie(
                self::COOKIE_NAME,
                self::RIGHT_THEME,
                self::REQUEST_TIME + self::COOKIE_LIFETIME,
                self::COOKIE_PATH,
                self::COOKIE_DOMAIN,
                self::IS_COOKIE_SECURE,
                self::IS_COOKIE_HTTP_ONLY
            )
        );

        return $response;
    }
}
