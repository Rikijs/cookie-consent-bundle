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
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
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
    /**
     * @var MockObject
     */
    private $formFactoryInterface;

    /**
     * @var MockObject
     */
    private $cookieLogger;

    /**
     * @var MockObject
     */
    private $cookieHandler;

    protected function setUp(): void
    {
        $this->formFactoryInterface = $this->createMock(FormFactoryInterface::class);
        $this->cookieLogger = $this->createMock(CookieLogger::class);
        $this->cookieHandler = $this->createMock(CookieHandler::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $expectedEvents = [
           KernelEvents::RESPONSE => ['onResponse'],
        ];

        $cookieConsentFormSubscriber = new CookieConsentFormSubscriber($this->formFactoryInterface, $this->cookieLogger, $this->cookieHandler, true);
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

        $this->formFactoryInterface
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($form);

        $this->cookieLogger
            ->expects($this->once())
            ->method('log');

        $cookieConsentFormSubscriber = new CookieConsentFormSubscriber($this->formFactoryInterface, $this->cookieLogger, $this->cookieHandler, true);
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

        $this->formFactoryInterface
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($form);

        $this->cookieLogger
            ->expects($this->never())
            ->method('log');

        $cookieConsentFormSubscriber = new CookieConsentFormSubscriber($this->formFactoryInterface, $this->cookieLogger, $this->cookieHandler, false);
        $cookieConsentFormSubscriber->onResponse($event);
    }

    public function testOnResponseWithUnfoundResponseEvent(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No ResponseEvent class found');

        $cookieConsentFormSubscriber = new CookieConsentFormSubscriber($this->formFactoryInterface, $this->cookieLogger, $this->cookieHandler, false);
        $event = $this->createMock(KernelEvent::class);
        $event->method('isMainRequest')->willReturn(true);
        $cookieConsentFormSubscriber->onResponse($event);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetCookieConsentKeyGeneratesRandomHexString(): void
    {
        $request = new Request();

        $cookieConsentFormSubscriber = new CookieConsentFormSubscriber(
            $this->formFactoryInterface,
            $this->cookieLogger,
            $this->cookieHandler,
            true
        );

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

        $cookieConsentFormSubscriber = new CookieConsentFormSubscriber(
            $this->formFactoryInterface,
            $this->cookieLogger,
            $this->cookieHandler,
            true
        );

        $reflectionMethod = new ReflectionMethod($cookieConsentFormSubscriber, 'getCookieConsentKey');
        $key = $reflectionMethod->invoke($cookieConsentFormSubscriber, $request);

        $this->assertSame($existingKey, $key);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseEvent
     */
    private function getResponseEvent(Request $request, Response $response): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
    }
}
