<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\User;

use App\Entity\User\RoleAccess;
use App\Repository\AbstractDBRepository;
use Illuminate\Support\Collection;

/**
 * Database-based User Repository Implementation.
 */
class DBRoleAccess extends AbstractDBRepository implements RoleAccessInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'role_access';
    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'User\RoleAccess';

    /**
     * {@inheritdoc}
     */
    public function findByIdentityRoleResource(int $identityId, string $role, string $resource) : RoleAccess {
        return $this->findOneBy(
            [
                'identity_id' => $identityId,
                'role'        => $role,
                'resource'    => $resource
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentityAndRole(int $identityId, string $role) : Collection {
        return $this->findBy(
            [
                'identity_id' => $identityId,
                'role'        => $role
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findOne(int $identityId, int $roleAccessId) : RoleAccess {
        return $this->findOneBy(
            [
                'id'          => $roleAccessId,
                'identity_id' => $identityId
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOne(int $identityId, int $roleAccessId) : int {
        return $this->deleteBy(
            [
                'id'          => $roleAccessId,
                'identity_id' => $identityId
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllFromIdentity(int $identityId) : int {
        return $this->deleteBy(
            [
                'identity_id' => $identityId
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentity(int $identityId, string $resource = '') : Collection {
        $constraints = [
            'identity_id' => $identityId
        ];
        if (strlen($resource) > 0) {
            $constraints['resource'] = $resource;
        }

        return $this->findBy($constraints);
    }
}
