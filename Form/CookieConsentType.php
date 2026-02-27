<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use ConnectHolland\CookieConsentBundle\Cookie\CookieChecker;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CookieConsentType extends AbstractType
{
    /**
     * @param CookieChecker $cookieChecker
     * @param array<string> $cookieCategories
     * @param bool $csrfProtection
     */
    public function __construct(
        protected CookieChecker $cookieChecker,
        protected array $cookieCategories,
        protected bool $csrfProtection = true
    ) {}

    /**
     * Build the cookie consent form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->cookieCategories as $category) {
            $builder->add($category, CheckboxType::class, [
                'required' => false,
                'data' => $category === 'necessary' || ($this->cookieChecker->isCookieConsentSavedByUser() && $this->cookieChecker->isCategoryAllowedByUser($category)),
                'label_attr' => ['class' => 'checkbox-switch ms-3'],
                'attr'=> $category === 'necessary' ? ['class' => 'form-check-input switch-success', 'disabled' => 'disabled'] : ['class' => 'form-check-input'],
            ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();

            foreach ($this->cookieCategories as $category) {
                if ($category === 'necessary') {
                    $data[$category] = 'true';
                    continue;
                }
                $data[$category] = ($data[$category] ?? false) ? 'true' : 'false';
            }

            $event->setData($data);
        });

        $builder->add('save', SubmitType::class, ['label' => 'ch_cookie_consent.save', 'attr' => ['class' => 'btn btn-primary mt-3 ch-cookie-consent__btn']]);
    }

    /**
     * Default options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'CHCookieConsentBundle',
            'csrf_protection' => $this->csrfProtection,
        ]);
    }
}

/*
 * 'data' => $category === 'necessary' ? true : ($this->cookieChecker->isCookieConsentSavedByUser()
 *  ? $this->cookieChecker->isCategoryAllowedByUser($category)
 *  : false),
*/
