<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Raw;

use App\Command\AbstractCommand;

/**
 * Raw "Create New" Command.
 */
class CreateNew extends AbstractCommand {
    /**
     * Raw's user.
     *
     * @var App\Entity\User
     */
    public $user;
    /**
     * Raw's Source.
     *
     * @var App\Entity\Source
     */
    public $source;
    /**
     * New raw name.
     *
     * @var string
     */
    public $name;
    /**
     * New raw data.
     *
     * @var string
     */
    public $data;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : self {
        if (isset($parameters['user'])) {
            $this->user = $parameters['user'];
        }

        if (isset($parameters['source'])) {
            $this->source = $parameters['source'];
        }

        if (isset($parameters['name'])) {
            $this->name = $parameters['name'];
        }

        if (isset($parameters['data'])) {
            $this->data = $parameters['data'];
        }

        return $this;
    }
}
