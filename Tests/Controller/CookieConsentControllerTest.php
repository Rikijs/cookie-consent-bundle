<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Tests\Controller;

use ConnectHolland\CookieConsentBundle\Controller\CookieConsentController;
use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use ConnectHolland\CookieConsentBundle\Form\CookieConsentType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class CookieConsentControllerTest extends TestCase
{
    protected function createController(
        ?CookieChecker $cookieChecker = null,
        ?Environment $templating = null,
        ?FormFactoryInterface $formFactory = null,
        ?RouterInterface $router = null,
        ?ParameterBagInterface $parameterBag = null,
        ?string $formAction = null
    ): CookieConsentController {
        return new CookieConsentController(
            $cookieChecker ?? $this->createStub(CookieChecker::class),
            $templating ?? $this->createStub(Environment::class),
            $formFactory ?? $this->createStub(FormFactoryInterface::class),
            $router ?? $this->createStub(RouterInterface::class),
            $parameterBag ?? $this->createStub(ParameterBagInterface::class),
            $formAction
        );
    }

    public function testShow(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createStub(FormInterface::class));

        $templating = $this->createMock(Environment::class);
        $templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $controller = $this->createController(templating: $templating, formFactory: $formFactory);
        $response = $controller->show(new Request());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowIfCookieConsentNotSet(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createStub(FormInterface::class));

        $templating = $this->createMock(Environment::class);
        $templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $controller = $this->createController(templating: $templating, formFactory: $formFactory);
        $response = $controller->showIfCookieConsentNotSet(new Request());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowIfCookieConsentNotSetWithLocale(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createStub(FormInterface::class));

        $templating = $this->createMock(Environment::class);
        $templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $locale = 'lv';

        $request = new Request();
        $request->attributes->set('_locale', $locale);

        $controller = $this->createController(templating: $templating, formFactory: $formFactory);
        $response = $controller->showIfCookieConsentNotSet($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($locale, $request->getLocale());
    }

    public function testShowWithFormAction(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('test_route')
            ->willReturn('/test_route');

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class, null, ['action' => '/test_route'])
            ->willReturn($this->createStub(FormInterface::class));

        $templating = $this->createMock(Environment::class);
        $templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $controller = $this->createController(
            templating: $templating,
            formFactory: $formFactory,
            router: $router,
            formAction: 'test_route'
        );

        $response = $controller->show(new Request());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testReject(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag
            ->expects($this->once())
            ->method('get')
            ->with('ch_cookie_consent.reject_route_name')
            ->willReturn('privacy_cookies');

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('privacy_cookies', [])
            ->willReturn('/privacy_cookies');

        $controller = $this->createController(router: $router, parameterBag: $parameterBag);
        $response = $controller->reject();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/privacy_cookies', $response->headers->get('Location'));
    }
}
