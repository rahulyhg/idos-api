<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Profile\Source;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Source "Create New" Command.
 */
class CreateNew extends AbstractCommand {
    /**
     * Source Name.
     *
     * @var string
     */
    public $name;
    /**
     * Source ip address.
     *
     * @var string
     */
    public $ipaddr;
    /**
     * Source Tags.
     *
     * @var array
     */
    public $tags;
    /**
     * Source's User.
     *
     * @var \App\Entity\User
     */
    public $user;
    /**
     * Target Credential.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        if (isset($parameters['name'])) {
            $this->name = $parameters['name'];
        }

        if (isset($parameters['tags'])) {
            $this->tags = $parameters['tags'];
        }

        if (isset($parameters['user'])) {
            $this->user = $parameters['user'];
        }

        if (isset($parameters['credential'])) {
            $this->credential = $parameters['credential'];
        }

        if (isset($parameters['ipaddr'])) {
            $this->ipaddr = $parameters['ipaddr'];
        }

        return $this;
    }
}
