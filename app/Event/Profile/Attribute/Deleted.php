<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Attribute;

use App\Event\AbstractEvent;
use Illuminate\Support\Collection;

/**
 * Deleted event.
 */
class Deleted extends AbstractEvent {
    /**
     * Event related Companies.
     *
     * @var \Illuminate\Support\Collection
     */
    public $attributes;

    /**
     * Class constructor.
     *
     * @param \Illuminate\Support\Collection $attributes
     *
     * @return void
     */
    public function __construct(Collection $attributes) {
        $this->attributes = $attributes;
    }
}
