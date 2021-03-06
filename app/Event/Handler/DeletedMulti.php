<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Handler;

use App\Entity\Identity;
use App\Event\AbstractEvent;
use Illuminate\Support\Collection;

/**
 * Deleted event for multiple service handlers.
 */
class DeletedMulti extends AbstractEvent {
    /**
     * Event related Handlers.
     *
     * @var \Illuminate\Support\Collection
     */
    public $serviceHandlers;
    /**
     * Event related Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * Class constructor.
     *
     * @param \Illuminate\Support\Collection $serviceHandlers
     * @param \App\Entity\Identity           $identity
     *
     * @return void
     */
    public function __construct(Collection $serviceHandlers, Identity $identity) {
        $this->serviceHandlers = $serviceHandlers;
        $this->identity        = $identity;
    }
}
