<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Profile\Score;

use App\Command\AbstractCommand;

/**
 * Score "Delete One" Command.
 */
class DeleteOne extends AbstractCommand {
    /**
     * Score's user.
     *
     * @var \App\Entity\User
     */
    public $user;

    /**
     * Score's Creator.
     *
     * @var \App\Entity\Service
     */
    public $service;

    /**
     * New score name.
     *
     * @var string
     */
    public $name;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : self {
        if (isset($parameters['user'])) {
            $this->user = $parameters['user'];
        }

        if (isset($parameters['service'])) {
            $this->service = $parameters['service'];
        }

        if (isset($parameters['name'])) {
            $this->name = $parameters['name'];
        }

        return $this;
    }
}
