<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Identity;

use App\Command\AbstractCommand;

/**
 * Identity "Create New" Command.
 */
class CreateNew extends AbstractCommand {
    /**
     * Profile Id.
     *
     * @var int
     */
    public $profileId;
    /**
     * Source Name.
     *
     * @var string
     */
    public $sourceName;
    /**
     * Application Key.
     *
     * @var string
     */
    public $appKey;

    /**
     * {@inheritdoc}
     *
     * @return App\Command\Identity\CreateNew
     */
    public function setParameters(array $parameters) : self {
        if (isset($parameters['profileId'])) {
            $this->profileId = $parameters['profileId'];
        }

        if (isset($parameters['sourceName'])) {
            $this->sourceName = $parameters['sourceName'];
        }

        if (isset($parameters['appKey'])) {
            $this->appKey = $parameters['appKey'];
        }

        return $this;
    }
}
