<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Enum;

class CookieNameEnum
{
    public const COOKIE_CONSENT_NAME = 'Cookie_Consent';
    public const COOKIE_CONSENT_KEY_NAME = 'Cookie_Consent_Key';
    public const COOKIE_CATEGORY_NAME_PREFIX = 'Cookie_Category_';

    /**
     * Get a cookie category name.
     */
    public static function getCookieCategoryName(string $category): string
    {
        return self::COOKIE_CATEGORY_NAME_PREFIX.$category;
    }
}
