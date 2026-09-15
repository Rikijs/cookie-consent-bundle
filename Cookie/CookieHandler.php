<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Cookie;

use ConnectHolland\CookieConsentBundle\Enum\CookieNameEnum;
use DateInterval;
use DateTime;
use DateTimeZone;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CookieHandler
{
    public function __construct(private readonly bool $httpOnly) {}

    /**
     * Save chosen cookie categories in cookies.
     */
    public function save(array $categories, string $key, Response $response): void
    {
        $this->saveCookie(CookieNameEnum::COOKIE_CONSENT_NAME, date('r'), $response);
        $this->saveCookie(CookieNameEnum::COOKIE_CONSENT_KEY_NAME, $key, $response);

        foreach ($categories as $category => $permitted)
        {
            $this->saveCookie(CookieNameEnum::getCookieCategoryName($category), $permitted, $response);
        }
    }

    /**
     * Add cookie to response headers.
     */
    protected function saveCookie(string $name, string $value, Response $response): void
    {
        $expirationInterval = new DateInterval('P1Y');
        $expirationDateTime = new DateTime();
        $expirationDateTime->add($expirationInterval);
        $expirationDateTime->setTimezone(new DateTimeZone('Europe/Riga'));
        $expirationTimestamp = $expirationDateTime->getTimestamp();

        $response->headers->setCookie(
            new Cookie($name, $value, $expirationTimestamp, '/', null, true, $this->httpOnly, true)
        );
    }
}
