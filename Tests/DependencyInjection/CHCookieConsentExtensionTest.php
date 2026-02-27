<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\Tests\DependencyInjection;

use ConnectHolland\CookieConsentBundle\DependencyInjection\CHCookieConsentExtension;
use ConnectHolland\CookieConsentBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class CHCookieConsentExtensionTest extends TestCase
{
    /**
     * @var CHCookieConsentExtension
     */
    private CHCookieConsentExtension $chCookieConsentExtension;

    /**
     * @var ContainerBuilder
     */
    private ContainerBuilder $configuration;

    protected function setUp(): void
    {
        $this->chCookieConsentExtension = new TestableCHCookieConsentExtension();
        $this->configuration = new ContainerBuilder();
    }

    public function testFullConfiguration(): void
    {
        $this->createConfiguration($this->getFullConfig());

        $this->assertParameter(['necessary', 'functional', 'analytics', 'marketing'], 'ch_cookie_consent.categories');
    }

    public function testInvalidConfiguration(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->createConfiguration($this->getInvalidConfig());
    }

    /**
     * create configuration.
     */
    protected function createConfiguration(array $config): void
    {
        $this->chCookieConsentExtension->load([$config], $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
     * get full config.
     */
    protected function getFullConfig(): array
    {
        $yaml = <<<EOF
categories: ['necessary', 'functional', 'analytics', 'marketing']
EOF;
        return (new Parser())->parse($yaml);
    }

    /**
     * get invalid config.
     */
    protected function getInvalidConfig(): array
    {
        $yaml = <<<EOF
theme: 'not_existing'
position: 'not_existing'
EOF;
        return (new Parser())->parse($yaml);
    }

    /**
     * Test if parameter is set.
     */
    private function assertParameter($value, $key): void
    {
        $this->assertSame($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }
}

final class TestableCHCookieConsentExtension extends CHCookieConsentExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ch_cookie_consent.categories', $config['categories']);
        $container->setParameter('ch_cookie_consent.use_logger', $config['use_logger']);
        $container->setParameter('ch_cookie_consent.http_only', $config['http_only']);
        $container->setParameter('ch_cookie_consent.form_action', $config['form_action']);
        $container->setParameter('ch_cookie_consent.csrf_protection', $config['csrf_protection']);
    }
}
