<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types=1);

namespace App\Entity;

/**
 * Users Entity.
 *
 * @apiEntity schema/credential/credentialEntity.json
 *
 * @property int $id
 * @FIXME
 * PUT $visible['username']
 */
class User extends AbstractEntity {
    /**
     * {@inheritdoc}
     */
    const CACHE_PREFIX = 'User';
    /**
     * {@inheritdoc}
     */
    protected $visible = ['username', 'created_at'];
    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * {@inheritdoc}
     */
    public function getCacheKeys() : array {
        return [
            sprintf(
                '%s/id/%s',
                self::CACHE_PREFIX,
                $this->id
            ),
            sprintf(
                '%s/slug/%s',
                self::CACHE_PREFIX,
                $this->slug
            ),
            sprintf(
                '%s/public/%s',
                self::CACHE_PREFIX,
                $this->public
            )
        ];
    }

    public function getReferenceCacheKeys() : array {

    }

}