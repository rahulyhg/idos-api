<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Entity;

/**
 * Service's Entity.
 *
 * @apiEntity schema/handler/handlerEntity.json
 *
 * @property int        $id
 * @property int        $company_id
 * @property string     $name
 * @property string     $auth_username
 * @property string     $auth_password
 * @property string     $public
 * @property string     $private
 * @property bool       $enabled
 * @property int        $created_at
 * @property int        $updated_at
 */
class Handler extends AbstractEntity {
    /**
     * {@inheritdoc}
     */
    protected $visible = [
        'id',
        'name',
        'public',
        'enabled',
        'created_at',
        'updated_at'
    ];
    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at', 'updated_at'];
    /**
     * {@inheritdoc}
     */
    protected $secure = ['auth_username', 'auth_password', 'private'];
}
