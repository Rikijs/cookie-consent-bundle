<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Cookie;

use ConnectHolland\CookieConsentBundle\Enum\CookieNameEnum;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class CookieChecker
{
    public function __construct(private RequestStack $requestStack) {}

    /**
     * Check if cookie consent has already been saved.
     */
    public function isCookieConsentSavedByUser(): bool
    {
        return $this->requestStack->getCurrentRequest()->cookies->has(CookieNameEnum::COOKIE_CONSENT_NAME);
    }

    /**
     * Check if the user permits a given cookie category.
     */
    public function isCategoryAllowedByUser(string $category): bool
    {
        return $this->requestStack->getCurrentRequest()->cookies->get(CookieNameEnum::getCookieCategoryName($category)) === 'true';
    }
}
