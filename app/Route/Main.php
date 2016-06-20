<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Route;

use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Root routing definitions.
 *
 * @link docs/overview.md
 *
 * @see App\Controller\Main
 */
class Main implements RouteInterface {
    /**
     * {@inheritDoc}
     */
    public static function getPublicNames() {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public static function register(App $app) {
        $app->getContainer()[\App\Controller\Main::class] = function (ContainerInterface $container) {
            return new \App\Controller\Main(
                $container->get('router'),
                $container->get('commandBus'),
                $container->get('commandFactory')
            );
        };

        self::listAll($app);
    }

    /**
     * List all Endpoints
     *
     * Retrieve a complete list of all public endpoints.
     *
     * @apiEndpoint GET /
     *
     * @param \Slim\App $app
     *
     * @return void
     *
     * @link docs/listAll.md
     *
     * @see App\Controller\Main::listAll
     */
    private static function listAll(App $app) {
        $app
            ->get(
                '/',
                'App\Controller\Main:listAll'
            )
            ->setName('main:listAll');
    }
}
