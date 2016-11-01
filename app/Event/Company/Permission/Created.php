<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Company\Permission;

use App\Entity\Company\Permission;
use App\Entity\Identity;
use App\Event\AbstractEvent;

/**
 * Created event.
 */
class Created extends AbstractEvent {
    /**
     * Event related Permission.
     *
     * @var \App\Entity\Company\Permission
     */
    public $permission;
    /**
     * Event related Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Company\Permission $permission
     *
     * @return void
     */
    public function __construct(Permission $permission, Identity $identity) {
        $this->permission = $permission;
        $this->identity = $identity;
    }
}
