<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Profile;

use App\Entity\Profile\Feature;
use App\Repository\AbstractSQLDBRepository;
use Illuminate\Support\Collection;

/**
 * Database-based Feature Repository Implementation.
 */
class DBFeature extends AbstractSQLDBRepository implements FeatureInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'features';
    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'Profile\Feature';
    /**
     * {@inheritdoc}
     */
    protected $filterableKeys = [
        'creator.name' => 'string',
        'source'       => 'string',
        'name'         => 'string',
        'type'         => 'string',
        'source'       => 'string',
        'created_at'   => 'date'
    ];
    /**
     * {@inheritdoc}
     */
    protected $orderableKeys = [
        'source',
        'name',
        'type',
        'created_at',
        'updated_at'
    ];
    /**
     * {@inheritdoc}
     */
    protected $relationships = [
        'user' => [
            'type'       => 'MANY_TO_ONE',
            'table'      => 'users',
            'foreignKey' => 'user_id',
            'key'        => 'id',
            'entity'     => 'User',
            'nullable'   => false,
            'hydrate'    => false
        ],

        'creator' => [
            'type'       => 'MANY_TO_ONE',
            'table'      => 'services',
            'foreignKey' => 'creator',
            'key'        => 'id',
            'entity'     => 'Service',
            'nullable'   => false,
            'hydrate'    => [
                'name'
            ]
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function findOne(int $id, int $serviceId, int $userId) : Feature {
        return $this->findOneBy(
            [
                'id'      => $id,
                'creator' => $serviceId,
                'user_id' => $userId
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName(string $name, int $serviceId, $sourceName, int $userId) : Feature {
        return $this->findOneBy(
            [
                'name'    => $name,
                'creator' => $serviceId,
                'source'  => $sourceName,
                'user_id' => $userId
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByServiceIdAndUserId(int $serviceId, int $userId, array $queryParams = []) : Collection {
        return $this->findBy(
            [
                'creator' => $serviceId,
                'user_id' => $userId
            ], $queryParams
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserId(int $userId, array $queryParams = []) : Collection {
        return $this->findBy(
            [
            'user_id' => $userId
            ], $queryParams
        );
    }

    /**
     * {@inheritdoc}
     */
    public function upsertBulk(int $serviceId, int $userId, array $features) : bool {
        $this->beginTransaction();
        $success = true;

        foreach ($features as $key => $feature) {
            if ($feature['type'] === 'array') {
                $feature['value'] = json_encode($feature['value']);
            }

            // user_id, source, name, creator(service_id), type, value
            $success = $success && $this->runRaw(
                    'INSERT INTO features (user_id, source, name, creator, type, value) VALUES (:user_id, :source, :name, :creator, :type, :value)
                ON CONFLICT (user_id, source, creator, name)
                DO UPDATE set value = :value, type = :type, updated_at = NOW()',
                    [
                        'user_id' => $userId,
                        'source'  => $feature['source'],
                        'name'    => $feature['name'],
                        'creator' => $serviceId,
                        'type'    => $feature['type'],
                        'value'   => $feature['value']
                    ]
                );
        }

        if ($success) {
            $this->commit();
        } else {
            $this->rollBack();
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByUserId(int $userId, array $queryParams = []) : int {
        return $this->deleteByKey('user_id', $userId);
    }
}
