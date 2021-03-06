<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Service;

use App\Entity\Identity;
use App\Entity\Service;
use App\Event\AbstractEvent;

/**
 * Updated event.
 */
class Updated extends AbstractEvent {
    /**
     * Event related Service.
     *
     * @var \App\Entity\Service
     */
    public $service;
    /**
     * Event related Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Service  $service
     * @param \App\Entity\Identity $identity
     *
     * @return void
     */
    public function __construct(Service $service, Identity $identity) {
        $this->service  = $service;
        $this->identity = $identity;
    }
}
