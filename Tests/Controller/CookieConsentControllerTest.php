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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CookieConsentControllerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $templating;

    /**
     * @var MockObject
     */
    private $formFactory;

    /**
     * @var MockObject
     */
    private $cookieChecker;

    /**
     * @var MockObject
     */
    private $translator;

    /**
     * @var MockObject
     */
    private $router;

    /**
     * @var CookieConsentController
     */
    private $cookieConsentController;

    protected function setUp(): void
    {
        $this->templating              = $this->createMock(Environment::class);
        $this->formFactory             = $this->createMock(FormFactoryInterface::class);
        $this->cookieChecker           = $this->createMock(CookieChecker::class);
        $this->translator              = $this->getMockBuilder(TranslatorInterface::class)->addMethods(['setLocale'])->getMockForAbstractClass();
        $this->router                  = $this->createMock(RouterInterface::class);
        $this->cookieConsentController = new CookieConsentController(
            $this->templating,
            $this->formFactory,
            $this->cookieChecker,
            $this->router,
            $this->translator,
            'false'
        );
    }

    public function testShow(): void
    {
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createMock(FormInterface::class));

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $response = $this->cookieConsentController->show(new Request());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowIfCookieConsentNotSet(): void
    {
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createMock(FormInterface::class));

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $response = $this->cookieConsentController->showIfCookieConsentNotSet(new Request());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowIfCookieConsentNotSetWithLocale(): void
    {
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createMock(FormInterface::class));

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $locale  = 'lv';

        $request = new Request();
        $request->attributes->set('locale', $locale);

        $this->translator
            ->expects($this->once())
            ->method('setLocale')
            ->with($locale);

        $response = $this->cookieConsentController->showIfCookieConsentNotSet($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($locale, $request->getLocale());
    }

    public function testShowIfCookieConsentNotSetWithCookieConsentSet(): void
    {
        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(CookieConsentType::class)
            ->willReturn($this->createMock(FormInterface::class));

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->willReturn('test');

        $response = $this->cookieConsentController->showIfCookieConsentNotSet(new Request());

        $this->assertInstanceOf(Response::class, $response);
    }
}
