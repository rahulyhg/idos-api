<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\HandlerService;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * HandlerService "Delete one" Command.
 */
class DeleteOne extends AbstractCommand {
    /**
     * HandlerService's id.
     *
     * @var int
     */
    public $handlerServiceId;
    /**
     * Acting company.
     *
     * @var \App\Entity\Company
     */
    public $company;
    /**
     * Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        return $this;
    }
}
