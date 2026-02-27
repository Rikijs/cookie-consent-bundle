<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Twig;

use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CHCookieConsentTwigExtension extends AbstractExtension
{
    /**
     * Register all custom twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'chcookieconsent_isCookieConsentSavedByUser',
                $this->isCookieConsentSavedByUser(...)
            ),
            new TwigFunction(
                'chcookieconsent_isCategoryAllowedByUser',
                $this->isCategoryAllowedByUser(...)
            ),
        ];
    }

    public function __construct(private readonly CookieChecker $cookieChecker)
    {
    }

    /**
     * Checks if a user has sent a cookie consent form.
     */
    public function isCookieConsentSavedByUser(): bool
    {
        return $this->cookieChecker->isCookieConsentSavedByUser();
    }

    /**
     * Checks if the user has given permission for the cookie category.
     */
    public function isCategoryAllowedByUser(string $category): bool
    {
        return $this->cookieChecker->isCategoryAllowedByUser($category);
    }
}
