<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Listener;

use App\Factory\Command;
use Interop\Container\ContainerInterface;
use League\Event\EventInterface;
use League\Tactician\CommandBus;

/**
 * Company Event Listener.
 */
class Company extends AbstractListener {
    /**
     * Command Bus instance.
     *
     * @var \League\Tactician\CommandBus
     */
    private $commandBus;
    /**
     * Command Factory instance.
     *
     * @var \App\Factory\Command
     */
    private $commandFactory;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) : void {
        $container[self::class] = function (ContainerInterface $container) : ListenerInterface {
            return new \App\Listener\Company(
                $container
                    ->get('commandBus'),
                $container
                    ->get('commandFactory')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param \League\Tactician\CommandBus $commandBus
     * @param \App\Factory\Command         $commandFactory
     *
     * @return void
     */
    public function __construct(
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->commandBus     = $commandBus;
        $this->commandFactory = $commandFactory;
    }

    /**
     * Handles events that trigger Service Handler creation.
     *
     * @param \League\Event\EventInterface $event
     *
     * @return void
     */
    public function handle(EventInterface $event) {
        $command = $this->commandFactory->create('Company\Setup');

        $command
            ->setParameter('companyId', $event->company->id)
            ->setParameter('identity', $event->identity);

        $this->commandBus->handle($command);
    }
}
