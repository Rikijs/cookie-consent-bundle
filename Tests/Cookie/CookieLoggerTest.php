<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Tests\Cookie;

use ConnectHolland\CookieConsentBundle\Cookie\CookieLogger;
use ConnectHolland\CookieConsentBundle\Entity\CookieConsentLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CookieLoggerTest extends TestCase
{
    private function createCookieLogger(
        ?ManagerRegistry $registry = null,
        ?RequestStack $requestStack = null
    ): CookieLogger {
        if ($registry === null) {
            $registry = $this->createStub(ManagerRegistry::class);
            $registry
                ->method('getManagerForClass')
                ->willReturn($this->createStub(ObjectManager::class));
        }

        return new CookieLogger(
            $registry,
            $requestStack ?? $this->createStub(RequestStack::class)
        );
    }

    /**
     * Test CookieLogger:log.
     */
    public function testLog(): void
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack
            ->method('getCurrentRequest')
            ->willReturn($request);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(3))
            ->method('persist');
        $entityManager
            ->expects($this->once())
            ->method('flush')
            ->with();

        $registry = $this->createStub(ManagerRegistry::class);
        $registry
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $cookieLogger = $this->createCookieLogger($registry, $requestStack);

        $cookieLogger->log([
            'analytics'    => 'true',
            'social_media' => 'true',
            'tracking'     => 'false',
        ], 'key-test');
    }

    /**
     * Test CookieLogger:log.
     */
    public function testLogWithNullIp(): void
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('getClientIp')
            ->willReturn(null);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack
            ->method('getCurrentRequest')
            ->willReturn($request);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(3))
            ->method('persist');
        $entityManager
            ->expects($this->once())
            ->method('flush')
            ->with();

        $registry = $this->createStub(ManagerRegistry::class);
        $registry
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $cookieLogger = $this->createCookieLogger($registry, $requestStack);

        $cookieLogger->log([
            'analytics'    => 'true',
            'social_media' => 'true',
            'tracking'     => 'false',
        ], 'key-test');
    }

    /**
     * Test CookieLogger:log.
     */
    public function testLogWithoutRequest(): void
    {
        $this->expectException(\RuntimeException::class);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack
            ->method('getCurrentRequest')
            ->willReturn(null);

        $cookieLogger = $this->createCookieLogger(requestStack: $requestStack);
        $cookieLogger->log([], 'key-test');
    }
}
