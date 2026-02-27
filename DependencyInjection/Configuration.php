<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\CookieConsentBundle\DependencyInjection;

use ConnectHolland\CookieConsentBundle\Enum\CategoryEnum;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ch_cookie_consent');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode('categories')
                    ->defaultValue(
                        [
                            CategoryEnum::CATEGORY_NECESSARY,
                            CategoryEnum::CATEGORY_FUNCTIONAL,
                            CategoryEnum::CATEGORY_ANALYTICS,
                            CategoryEnum::CATEGORY_MARKETING,
                        ])
                ->end()
                ->booleanNode('use_logger')
                    ->defaultTrue()
                ->end()
                ->booleanNode('http_only')
                    ->defaultTrue()
                ->end()
                ->scalarNode('form_action')
                    ->defaultNull()
                ->end()
                ->booleanNode('csrf_protection')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
