<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Tests\EventSubscriber;

use ConnectHolland\CookieConsentBundle\Cookie\CookieHandler;
use ConnectHolland\CookieConsentBundle\Cookie\CookieLogger;
use ConnectHolland\CookieConsentBundle\EventSubscriber\CookieConsentFormSubscriber;
use ConnectHolland\CookieConsentBundle\Form\CookieConsentType;
use ConnectHolland\CookieConsentBundle\Enum\CookieNameEnum;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CookieConsentFormSubscriberTest extends TestCase
{
    private function createSubscriber(
        ?FormFactoryInterface $formFactory = null,
        ?CookieLogger $cookieLogger = null,
        ?CookieHandler $cookieHandler = null,
        bool $useLogger = true
    ): CookieConsentFormSubscriber {
        return new CookieConsentFormSubscriber(
            $formFactory ?? $this->createStub(FormFactoryInterface::class),
            $cookieLogger ?? $this->createStub(CookieLogger::class),
            $cookieHandler ?? $this->createStub(CookieHandler::class),
            $useLogger
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $expectedEvents = [
           KernelEvents::RESPONSE => ['onResponse'],
        ];

        $cookieConsentFormSubscriber = $this->createSubscriber();
        $this->assertSame($expectedEvents, $cookieConsentFormSubscriber::getSubscribedEvents());
    }

    public function testOnResponse(): void
    {
        $request = new Request();
        $response = new Response();
        $event = $this->getResponseEvent($request, $response);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $form
            ->expects($this->once())
            ->method('getData')
            ->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($form);

        $cookieLogger = $this->createMock(CookieLogger::class);
        $cookieLogger
            ->expects($this->once())
            ->method('log');

        $cookieConsentFormSubscriber = $this->createSubscriber(
            formFactory: $formFactory,
            cookieLogger: $cookieLogger,
            useLogger: true
        );
        $cookieConsentFormSubscriber->onResponse($event);
    }

    public function testOnResponseWithLoggerDisabled(): void
    {
        $request = new Request();
        $response = new Response();
        $event = $this->getResponseEvent($request, $response);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $form
            ->expects($this->once())
            ->method('getData')
            ->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($form);

        $cookieLogger = $this->createMock(CookieLogger::class);
        $cookieLogger
            ->expects($this->never())
            ->method('log');

        $cookieConsentFormSubscriber = $this->createSubscriber(
            formFactory: $formFactory,
            cookieLogger: $cookieLogger,
            useLogger: false
        );
        $cookieConsentFormSubscriber->onResponse($event);
    }

    public function testOnResponseWithUnfoundResponseEvent(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No ResponseEvent class found');

        $cookieConsentFormSubscriber = $this->createSubscriber(useLogger: false);
        $event = $this->createStub(KernelEvent::class);
        $event->method('isMainRequest')->willReturn(true);
        $cookieConsentFormSubscriber->onResponse($event);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetCookieConsentKeyGeneratesRandomHexString(): void
    {
        $request = new Request();

        $cookieConsentFormSubscriber = $this->createSubscriber();

        $reflectionMethod = new ReflectionMethod($cookieConsentFormSubscriber, 'getCookieConsentKey');
        $key = $reflectionMethod->invoke($cookieConsentFormSubscriber, $request);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $key);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetCookieConsentKeyReturnsExistingKey(): void
    {
        $existingKey = 'existing-key';
        $request = new Request();
        $request->cookies->set(CookieNameEnum::COOKIE_CONSENT_KEY_NAME, $existingKey);

        $cookieConsentFormSubscriber = $this->createSubscriber();

        $reflectionMethod = new ReflectionMethod($cookieConsentFormSubscriber, 'getCookieConsentKey');
        $key = $reflectionMethod->invoke($cookieConsentFormSubscriber, $request);

        $this->assertSame($existingKey, $key);
    }

    private function getResponseEvent(Request $request, Response $response): ResponseEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
    }
}
