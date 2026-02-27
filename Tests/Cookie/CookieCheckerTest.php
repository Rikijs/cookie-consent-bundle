<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Tests\Cookie;

use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CookieCheckerTest extends TestCase
{
    /**
     * @var Stub
     */
    private $request;

    /**
     * @var Stub
     */
    private $requestStack;

    /**
     * @var CookieChecker
     */
    private $cookieChecker;

    protected function setUp(): void
    {
        $this->requestStack = $this->createStub(RequestStack::class);
        $this->request = $this->createStub(Request::class);

        $this->requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->cookieChecker = new CookieChecker($this->requestStack);
    }

    #[DataProvider('isCookieConsentSavedByUserDataProvider')]
    public function testIsCookieConsentSavedByUser(array $cookies = [], bool $expected = false): void
    {
        $this->request->cookies = new InputBag($cookies);

        $this->assertSame($expected, $this->cookieChecker->isCookieConsentSavedByUser());
    }

    /**
     * Data provider for testIsCookieConsentSavedByUser.
     */
    public static function isCookieConsentSavedByUserDataProvider(): array
    {
        return [
            [['Cookie_Consent' => date('r')], true],
            [['Cookie_Consent' => 'true'], true],
            [['Cookie_Consent' => ''], true],
            [['Cookie Consent' => 'true'], false],
            [['CookieConsent' => 'true'], false],
            [[], false],
        ];
    }

    #[DataProvider('isCategoryAllowedByUserDataProvider')]
    public function testIsCategoryAllowedByUser(array $cookies = [], string $category = '', bool $expected = false): void
    {
        $this->request->cookies = new InputBag($cookies);

        $this->assertSame($expected, $this->cookieChecker->isCategoryAllowedByUser($category));
    }

    /**
     * Data provider for testIsCategoryAllowedByUser.
     */
    public static function isCategoryAllowedByUserDataProvider(): array
    {
        return [
            [['Cookie_Category_necessary' => 'true'], 'necessary', true],
            [['Cookie_Category_functional' => 'true'], 'functional', true],
            [['Cookie_Category_analytics' => 'true'], 'analytics', true],
            [['Cookie_Category_marketing' => 'true'], 'marketing', true],
            [['Cookie_Category_analytics' => 'false'], 'analytics', false],
            [['Cookie Category analytics' => 'true'], 'analytics', false],
            [['Cookie_Category_Analytics' => 'true'], 'analytics', false],
            [['analytics' => 'true'], 'analytics', false],
        ];
    }
}
