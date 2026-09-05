<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Controller;

use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use ConnectHolland\CookieConsentBundle\Form\CookieConsentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CookieConsentController extends AbstractController
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
     * Reject all previously set cookies.
     */
    #[Route('/cookie_reject', name: 'ch_cookie_consent.reject')]
    public function reject(): Response
    {
        $response = new Response('Cookies where rejected.');

        $response->headers->clearCookie('Cookie_Consent');
        $response->headers->clearCookie('Cookie_Consent_Key');
        $response->headers->clearCookie('Cookie_Category_necessary');
        $response->headers->clearCookie('Cookie_Category_functional');
        $response->headers->clearCookie('Cookie_Category_statistics');
        $response->headers->clearCookie('Cookie_Category_marketing');

        //return $response;
        return $this->redirectToRoute('ch_cookie_consent.show_if_cookie_consent_not_set');
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
     * Set locale if available as a GET parameter.
     */
    protected function setLocale(Request $request): void
    {
        $locale = $request->attributes->get('locale');
        if (empty($locale) === false)
        {
            $this->translator->setLocale($locale);
            $request->setLocale($locale);
        }
    }
}
