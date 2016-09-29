<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Raw;

use App\Entity\Company\Credential;
use App\Entity\Profile\Process;
use App\Entity\Profile\Raw;
use App\Entity\Profile\Source;
use App\Entity\User;
use App\Event\AbstractServiceQueueEvent;

/**
 * Updated event.
 */
class Updated extends AbstractServiceQueueEvent {
    /**
     * Event related Raw.
     *
     * @var App\Entity\Profile\Raw
     */
    public $raw;

    /**
     * Event related User.
     *
     * @var App\Entity\User
     */
    public $user;

    /**
     * Event related Source.
     *
     * @var App\Entity\Profile\Source
     */
    public $source;

    /**
     * Event related Credential.
     *
     * @var App\Entity\Company\Credential
     */
    public $credential;

    /**
     * Event related Process.
     *
     * @var App\Entity\Profile\Process
     */
    public $process;

    /**
     * Class constructor.
     *
     * @param App\Entity\Profile\Raw $raw
     *
     * @return void
     */
    public function __construct(Raw $raw, User $user, Credential $credential, Source $source, Process $process) {
        $this->raw         = $raw;
        $this->user        = $user;
        $this->credential  = $credential;
        $this->source      = $source;
        $this->process     = $process;
    }

    /**
     * {inheritdoc}.
     */
    public function getServiceHandlerPayload(array $merge = []) : array {

        return array_merge(
            [
            'providerName' => $this->source->name,
            'sourceId'     => $this->source->getEncodedId(),
            'publicKey'    => $this->credential->public,
            'processId'    => $this->process->getEncodedId(),
            'userName'     => $this->user->username
            ], $merge
        );
    }

    /**
     * {inheritdoc}.
     **/
    public function __toString() {
        return sprintf('idos:raw.%s.updated', $this->source->name);
    }
}
