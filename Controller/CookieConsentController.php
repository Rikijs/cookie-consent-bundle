<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Controller;

use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use ConnectHolland\CookieConsentBundle\Form\CookieConsentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

readonly class CookieConsentController
{
    public function __construct(
        protected Environment $twigEnvironment,
        protected FormFactoryInterface $formFactory,
        protected CookieChecker $cookieChecker,
        protected RouterInterface $router,
        protected TranslatorInterface  $translator,
        protected ?string $formAction = null
    ) {}

    /**
     * Show cookie consent.
     */
    #[Route(path: '/cookie_consent', name: 'ch_cookie_consent.show')]
    public function show(Request $request): Response
    {
        $this->setLocale($request);

        $response = new Response(
            $this->twigEnvironment->render('@CHCookieConsent/cookie_consent.html.twig', [
                'form' => $this->createCookieConsentForm()->createView(),
            ])
        );

        // Cache in ESI should not be shared
        $response->setPrivate();
        $response->setMaxAge(0);

        return $response;
    }

    /**
     * Reject all cookies.
     */
    #[Route(path: '/cookie_reject', name: 'ch_cookie_consent.reject')]
    public function reject(Request $request): void
    {
        $test = 'test-123';
    }

    /**
     * Show cookie consent if cookie consent is not set.
     */
    #[Route('/cookie_consent_alt', name: 'ch_cookie_consent.show_if_cookie_consent_not_set')]
    public function showIfCookieConsentNotSet(Request $request): Response
    {
        return $this->show($request);
    }

    /**
     * Create a cookie consent form.
     */
    protected function createCookieConsentForm(): FormInterface
    {
        if ($this->formAction === null) {
            $form = $this->formFactory->create(CookieConsentType::class);
        } else {
            $form = $this->formFactory->create(
                CookieConsentType::class,
                null,
                [
                    'action' => $this->router->generate($this->formAction),
                ]
            );
        }

        return $form;
    }

    /**
     * Set locale if available as a GET parameter.
     */
    protected function setLocale(Request $request): void
    {
        $locale = $request->attributes->get('locale');
        if (empty($locale) === false) {
            $this->translator->setLocale($locale);
            $request->setLocale($locale);
        }
    }
}
