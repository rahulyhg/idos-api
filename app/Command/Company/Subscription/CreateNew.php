<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Company\Subscription;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Subscription "Create New" Command.
 */
class CreateNew extends AbstractCommand {
    /**
     * Subscription category slug.
     *
     * @var string
     */
    public $categoryName;
    /**
     * Acting Credential.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;
    /**
     * Subscription's credential's public key.
     *
     * @var string
     */
    public $credentialPubKey;
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
        if (isset($parameters['category_name'])) {
            $this->categoryName = $parameters['category_name'];
        }

        if (isset($parameters['identity'])) {
            $this->identity = $parameters['identity'];
        }

        if (isset($parameters['credential'])) {
            $this->credential = $parameters['credential'];
        }

        return $this;
    }
}
