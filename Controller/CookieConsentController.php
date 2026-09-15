<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Controller;

use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use ConnectHolland\CookieConsentBundle\Enum\CookieNameEnum;
use ConnectHolland\CookieConsentBundle\Form\CookieConsentType;
use DateInterval;
use DateTime;
use DateTimeZone;
use Random\RandomException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        protected ParameterBagInterface $parameterBag,
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
     * Accept all consent cookies.
     * @throws RandomException
     */
    #[Route('/cookie_acccept_all', name: 'ch_cookie_consent.accept')]
    public function acceptAll(Request $request): Response
    {
        // 1. Getting route name for redirect to `set` route
        $acceptRouteName = $this->parameterBag->get('ch_cookie_consent.landing_accept_route') ?? 'homepage';
        $response = $this->redirectToRoute($acceptRouteName);

        // 2. Set period to: 1 year
        $expirationInterval = new DateInterval('P1Y');
        $expirationDateTime = new DateTime();
        $expirationDateTime->setTimezone(new DateTimeZone('Europe/Riga'));
        $expirationDateTime->add($expirationInterval);
        $expirationTimestamp = $expirationDateTime->getTimestamp();

        // 3. get or set a cookie key
        $cookieRandomKey = $request->cookies->get(CookieNameEnum::COOKIE_CONSENT_KEY_NAME) ?? bin2hex(random_bytes(16));

        // 4. Create and add main accept cookies
        $response->headers->setCookie(new Cookie('Cookie_Consent', 'true', $expirationTimestamp, '/', null, true, true));
        $response->headers->setCookie(new Cookie('Cookie_Consent_Key', $cookieRandomKey, $expirationTimestamp, '/', null, true, true));

        // 5. Create and add category cookies
        $response->headers->setCookie(new Cookie('Cookie_Category_necessary', 'true', $expirationTimestamp, '/', null, true, false));
        $response->headers->setCookie(new Cookie('Cookie_Category_functional', 'true', $expirationTimestamp, '/', null, true, false));
        $response->headers->setCookie(new Cookie('Cookie_Category_analytics', 'true', $expirationTimestamp, '/', null, true, false));
        $response->headers->setCookie(new Cookie('Cookie_Category_marketing', 'true', $expirationTimestamp, '/', null, true, false));

        return $response;
    }

    /**
     * Reject all previously set cookies.
     */
    #[Route('/cookie_reject_all', name: 'ch_cookie_consent.reject')]
    public function rejectAll(): Response
    {
        $rejectRouteName = $this->parameterBag->get('ch_cookie_consent.landing_reject_route');
        $response = $this->redirectToRoute($rejectRouteName);

        $response->headers->clearCookie('Cookie_Consent');
        $response->headers->clearCookie('Cookie_Consent_Key');
        $response->headers->clearCookie('Cookie_Category_necessary');
        $response->headers->clearCookie('Cookie_Category_functional');
        $response->headers->clearCookie('Cookie_Category_analytics');
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
            return $this->formFactory->create(CookieConsentType::class);
        }

        return $this->formFactory->create(
            CookieConsentType::class,
            null,
            [
                'action' => $this->router->generate($this->formAction),
            ]
        );
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

    /**
     * Return an existing key from cookies or create a new one.
     * @throws RandomException
     */
    protected function getCookieConsentKey(Request $request): string
    {
        return $request->cookies->get(CookieNameEnum::COOKIE_CONSENT_KEY_NAME) ?? bin2hex(random_bytes(16));
    }
}
