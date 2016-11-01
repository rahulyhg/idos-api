<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Company\Setting;

use App\Entity\Company\Setting;
use App\Entity\Identity;
use App\Event\AbstractEvent;

/**
 * Updated event.
 */
class Updated extends AbstractEvent {
    /**
     * Event related Setting.
     *
     * @var \App\Entity\Company\Setting
     */
    public $setting;
    /**
     * Event related Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Company\Setting $setting
     *
     * @return void
     */
    public function __construct(Setting $setting, Identity $identity) {
        $this->setting = $setting;
        $this->identity = $identity;
    }
}
