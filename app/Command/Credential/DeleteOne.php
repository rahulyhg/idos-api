<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Credential;

use App\Command\AbstractCommand;

/**
 * Credential "Delete One" Command.
 */
class DeleteOne extends AbstractCommand {
    /**
     * Credential to be deleted.
     *
     * @var int
     */
    public $credential;

    /**
     * Acting identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * {@inheritdoc}
     *
     * @return App\Command\Credential\DeleteOne
     */
    public function setParameters(array $parameters) : self {
        
        return $this;
    }
}
