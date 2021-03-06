<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Profile\Flag;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Flag "Create New" Command.
 */
class CreateNew extends AbstractCommand {
    /**
     * Flag's user.
     *
     * @var \App\Entity\User
     */
    public $user;
    /**
     * Flag's creator.
     *
     * @var \App\Entity\Handler
     */
    public $handler;
    /**
     * Flag's slug (user input).
     *
     * @var string
     */
    public $slug;
    /**
     * Flag's attribute (user input).
     *
     * @var string
     */
    public $attribute;
    /**
     * Credential.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        if (isset($parameters['user'])) {
            $this->user = $parameters['user'];
        }

        if (isset($parameters['slug'])) {
            $this->slug = $parameters['slug'];
        }

        if (isset($parameters['attribute'])) {
            $this->attribute = $parameters['attribute'];
        }

        return $this;
    }
}
