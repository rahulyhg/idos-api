<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Validator\User;

use App\Entity\Role;
use App\Entity\User\RoleAccess as RoleAccessEntity;
use App\Validator\Traits;
use App\Validator\ValidatorInterface;
use Respect\Validation\Validator;

/**
 * RoleAccess Validation Rules.
 */
class RoleAccess implements ValidatorInterface {
    use Traits\AssertId,
        Traits\AssertName;
    /**
     * Asserts a valid role name.
     *
     * @throws \Respect\Validation\Exceptions\ExceptionInterface
     *
     * @return void
     */
    public function assertRoleName(string $value) {
        Validator::in(
            [
            Role::COMPANY,
            Role::COMPANY_OWNER,
            Role::COMPANY_ADMIN,
            Role::USER,
            Role::GUEST
            ]
        )->assert($value);
    }

    /**
     * Asserts a valid access value.
     *
     * @throws \Respect\Validation\Exceptions\ExceptionInterface
     *
     * @return void
     */
    public function assertAccess(int $value) {
        Validator::digit()->length(1, 1)->in(
            [
            RoleAccessEntity::ACCESS_NONE,
            RoleAccessEntity::ACCESS_EXECUTE,
            RoleAccessEntity::ACCESS_WRITE,
            RoleAccessEntity::ACCESS_READ,
            RoleAccessEntity::ACCESS_WRITE | RoleAccessEntity::ACCESS_EXECUTE,
            RoleAccessEntity::ACCESS_READ | RoleAccessEntity::ACCESS_EXECUTE,
            RoleAccessEntity::ACCESS_READ | RoleAccessEntity::ACCESS_WRITE,
            RoleAccessEntity::ACCESS_READ | RoleAccessEntity::ACCESS_WRITE | RoleAccessEntity::ACCESS_EXECUTE
            ]
        )->assert($value);

    }

    /**
     * Asserts a valid resource value.
     *
     * @throws \Respect\Validation\Exceptions\ExceptionInterface
     *
     * @return void
     */
    public function assertResource($value) {
        Validator::stringType()->assert($value);
    }
}