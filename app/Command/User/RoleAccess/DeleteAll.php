<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\User\RoleAccess;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * RoleAccess "Delete All" Command.
 */
class DeleteAll extends AbstractCommand {
    /**
     * RoleAccess's owner's identity id.
     *
     * @var int
     */
    public $identityId;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        if (isset($parameters['identityId'])) {
            $this->identityId = $parameters['identityId'];
        }

        return $this;
    }
}
