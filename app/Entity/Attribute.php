<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Entity;

use App\Extension\SecureFields;

/**
 * Attribute Entity.
 *
 * @apiEntity schema/attribute/attributeEntity.json
 *
 * @property int    $id
 * @property string $name
 * @property string $value
 * @property int    $created_at
 * @property int    $updated_at
 */
class Attribute extends AbstractEntity {
    use SecureFields;
    /**
     * {@inheritdoc}
     */
    const CACHE_PREFIX = 'Attribute';

    /**
     * {@inheritdoc}
     */
    protected $visible = ['name', 'value', 'created_at'];
    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at'];
    /**
     * The attributes that should be secured.
     *
     * @var array
     */
    protected $secure = ['value'];

    /**
     * {@inheritdoc}
     */
    public function getCacheKeys() : array {
        return [
            sprintf(
                '%s.id.%s',
                self::CACHE_PREFIX,
                $this->id
            ),
            sprintf(
                '%s.user_id.%s',
                self::CACHE_PREFIX,
                $this->user_id
            ),
            sprintf(
                '%s.name.%s',
                self::CACHE_PREFIX,
                $this->name
            ),
            sprintf(
                '%s.value.%s',
                self::CACHE_PREFIX,
                $this->value
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceCacheKeys() : array {
        return array_merge(
            [
            sprintf(
                '%s.by.user_id.%s',
                self::CACHE_PREFIX,
                $this->sourceId
            )
            ],
            $this->getCacheKeys()
        );
    }
}