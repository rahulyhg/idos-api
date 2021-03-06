<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Entity\User;

use App\Entity\AbstractEntity;

/**
 * RoleAccesss Entity.
 *
 * @FIXME Schema does not exist!
 * @FIXME @apiEntity schema/role-access/roleAccessEntity.json
 *
 * @property int        $id
 * @property int        $identity_id
 * @property string     $role
 * @property string     $resource
 * @property int        $access
 */
class RoleAccess extends AbstractEntity {
    /**
     * Access levels following UNIX file permission standard.
     */
    const ACCESS_NONE    = 0x00;
    const ACCESS_EXECUTE = 0x01;
    const ACCESS_WRITE   = 0x02;
    const ACCESS_READ    = 0x04;

    /**
     * {@inheritdoc}
     */
    protected $visible = [
        'id',
        'role',
        'access',
        'resource',
        'created_at',
        'updated_at'
    ];
    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at', 'updated_at'];
}
