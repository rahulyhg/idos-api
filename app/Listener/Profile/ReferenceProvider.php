<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Listener\Profile;

use App\Event\Profile\Reference;
use App\Listener;
use Interop\Container\ContainerInterface;

class ReferenceProvider extends Listener\AbstractListenerProvider {
    public function __construct(ContainerInterface $container) {
        $this->events = [
            Reference\Created::class => [
                new Listener\LogFiredEventListener(($container->get('log'))('Event')),
                new Listener\MetricEventListener($container->get('commandBus'), $container->get('commandFactory'))
            ],
            Reference\Updated::class => [
                new Listener\LogFiredEventListener(($container->get('log'))('Event')),
                new Listener\MetricEventListener($container->get('commandBus'), $container->get('commandFactory'))
            ],
            Reference\Deleted::class => [
                new Listener\LogFiredEventListener(($container->get('log'))('Event')),
                new Listener\MetricEventListener($container->get('commandBus'), $container->get('commandFactory'))
            ],
            Reference\DeletedMulti::class => [
                new Listener\LogFiredEventListener(($container->get('log'))('Event')),
                new Listener\MetricEventListener($container->get('commandBus'), $container->get('commandFactory'))
            ]
        ];
    }
}
