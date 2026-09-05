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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class CookieConsentController
{
    public function __construct(
        protected CookieChecker $cookieChecker,
        protected Environment $twigEnvironment,
        protected FormFactoryInterface $formFactory,
        protected RouterInterface $router,
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
     * Reject all previously set cookies.
     */
    #[Route('/cookie_reject', name: 'ch_cookie_consent.reject')]
    public function reject(): Response
    {
        $response = $this->redirectToRoute('ch_cookie_consent.show_if_cookie_consent_not_set');

        $response->headers->clearCookie('Cookie_Consent');
        $response->headers->clearCookie('Cookie_Consent_Key');
        $response->headers->clearCookie('Cookie_Category_necessary');
        $response->headers->clearCookie('Cookie_Category_functional');
        $response->headers->clearCookie('Cookie_Category_statistics');
        $response->headers->clearCookie('Cookie_Category_marketing');

        return $response;
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
        if ($this->formAction === null)
        {
            $form = $this->formFactory->create(CookieConsentType::class);
        }
        else
        {
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
     * Set locale if available as a request attribute.
     */
    protected function setLocale(Request $request): void
    {
        $locale = $request->attributes->get('_locale');

        if (is_string($locale) && $locale !== '')
        {
            $request->setLocale($locale);
        }
    }

    /**
     * Manually made Helper method as original is not available in the bundle
     */
    private function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        $url = $this->router->generate($route, $parameters);

        return new RedirectResponse($url, $status);
    }
}
