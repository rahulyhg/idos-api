<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Entity;

use App\Extension\SecureFields;
use App\Helper\Utils;

/**
 * Warnings Entity.
 *
 * @apiEntity schema/warning/warningEntity.json
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property int    $user_id
 * @property int    $created_at
 * @property int    $updated_at
 */
class Warning extends AbstractEntity {
    use SecureFields;
    /**
     * {@inheritdoc}
     */
    const CACHE_PREFIX = 'Warning';

    /**
     * {@inheritdoc}
     */
    protected $visible = ['name', 'slug', 'reference', 'user_id', 'created_at', 'updated_at'];
    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Property Mutator for $name.
     *
     * @param string $value
     *
     * @return App\Entity\Warning
     */
    public function setNameAttribute(string $value) : self {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Utils::slugify($value);

        return $this;
    }

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
                '%s.slug.%s',
                self::CACHE_PREFIX,
                $this->slug
            ),
            sprintf(
                '%s.reference.%s',
                self::CACHE_PREFIX,
                $this->reference
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
                    $this->userId
                )
            ],
            $this->getCacheKeys()
        );
    }
}