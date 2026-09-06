<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Cookie;

use ConnectHolland\CookieConsentBundle\Entity\CookieConsentLog;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CookieLogger
{
    private ObjectManager $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        private readonly RequestStack $requestStack
    )
    {
        $this->entityManager = $registry->getManagerForClass(CookieConsentLog::class);
    }

    /**
     * Logs users' preferences in a database.
     */
    public function log(array $categories, string $key): void
    {
        if (!$this->requestStack->getCurrentRequest() instanceof Request)
        {
            throw new RuntimeException('No request found');
        }

        $ip = $this->anonymizeIp($this->requestStack->getCurrentRequest()->getClientIp());

        foreach ($categories as $category => $value)
        {
            $this->persistCookieConsentLog($category, $value, $ip, $key);
        }

        $this->entityManager->flush();
    }

    protected function persistCookieConsentLog(string $category, string $value, string $ip, string $key): void
    {
        $cookieConsentLog = new CookieConsentLog()
            ->setIpAddress($ip)
            ->setCookieConsentKey($key)
            ->setCookieName($category)
            ->setCookieValue($value)
            ->setTimestamp(new DateTime());

        $this->entityManager->persist($cookieConsentLog);
    }

    /**
     * GDPR required IP addresses to be saved anonymized.
     */
    protected function anonymizeIp(?string $ip): string
    {
        if ($ip === null)
        {
            return 'unknown';
        }

        $lastDot = strrpos($ip, '.') + 1;

        return substr($ip, 0, $lastDot) . str_repeat('x', strlen($ip) - $lastDot);
    }
}
