<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Tests\Form;

use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use ConnectHolland\CookieConsentBundle\Form\CookieConsentType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class CookieConsentTypeSimplifiedTest extends TypeTestCase
{
    /**
     * @var MockObject
     */
    private $cookieChecker;

    protected function setUp(): void
    {
        $this->cookieChecker = $this->createMock(CookieChecker::class);

        parent::setUp();
    }

    /**
     * Test submitting of CookieConsentType.
     */
    public function testSubmitValidDate(): void
    {
        $formData = [
            'necessary' => 'false',
            'functional' => 'false',
            'analytics' => 'false',
            'marketing' => 'false',
            'use_all_cookies' => true,
        ];

        $form = $this->factory->create(CookieConsentType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame([
            'necessary' => 'true',
            'functional' => 'true',
            'analytics' => 'true',
            'marketing' => 'true',
        ], $form->getData());
    }

    protected function getExtensions(): array
    {
        $type = new CookieConsentType($this->cookieChecker, ['necessary', 'functional', 'analytics', 'marketing'], true);

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
