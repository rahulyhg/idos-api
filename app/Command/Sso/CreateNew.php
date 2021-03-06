<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Sso;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Sso "Create New" Command.
 */
abstract class CreateNew extends AbstractCommand {
    /**
     * API Version.
     *
     * @var string
     */
    public $apiVersion;
    /**
     * Signup hash.
     *
     * @var string
     */
    public $signupHash;
    /**
     * Provider access token.
     *
     * @var string
     */
    public $accessToken;
    /**
     * Credential public key.
     *
     * @var string
     */
    public $credentialPubKey;
    /**
     * Application key.
     *
     * @var string
     */
    public $appKey;
    /**
     * Application secret.
     *
     * @var string
     */
    public $appSecret;
    /**
     * User ip address.
     *
     * @var string
     */
    public $ipAddress;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        if (isset($parameters['apiVersion'])) {
            $this->apiVersion = $parameters['apiVersion'];
        }

        if (isset($parameters['appKey'])) {
            $this->appKey = $parameters['appKey'];
        }

        if (isset($parameters['appSecret'])) {
            $this->appSecret = $parameters['appSecret'];
        }

        if (isset($parameters['ipAddress'])) {
            $this->ipAddress = $parameters['ipAddress'];
        }

        if (isset($parameters['accessToken'])) {
            $this->accessToken = $parameters['accessToken'];
        }

        if (isset($parameters['credentialPubKey'])) {
            $this->credentialPubKey = $parameters['credentialPubKey'];
        }

        return $this;
    }
}
