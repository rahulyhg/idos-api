<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Source;

use App\Command\AbstractCommand;
use App\Entity\User;

/**
 * Source "Delete One" Command.
 */
class DeleteOne extends AbstractCommand {
    /**
     * Source Id to be deleted.
     *
     * @var int
     */
    public $sourceId;
    /**
     * Source owner User.
     *
     * @var App\Entity\User
     */
    public $user;

    /**
     * {@inheritdoc}
     *
     * @return App\Command\Source\DeleteOne
     */
    public function setParameters(array $parameters) : self {
        if (isset($parameters['sourceId'])) {
            $this->sourceId = $parameters['sourceId'];
        }

        if (isset($parameters['user'])) {
            $this->user = $parameters['user'];
        }

        return $this;
    }
}