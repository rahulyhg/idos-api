<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Handler;

use App\Command\Permission\CreateNew;
use App\Command\Permission\DeleteAll;
use App\Command\Permission\DeleteOne;
use App\Entity\Permission as PermissionEntity;
use App\Repository\PermissionInterface;
use App\Validator\Permission as PermissionValidator;
use Interop\Container\ContainerInterface;

/**
 * Handles Permission commands.
 */
class Permission implements HandlerInterface {
    /**
     * Permission Repository instance.
     *
     * @var App\Repository\PermissionInterface
     */
    protected $repository;
    /**
     * Permission Validator instance.
     *
     * @var App\Validator\Permission
     */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) {
            return new \App\Handler\Permission(
                $container
                    ->get('repositoryFactory')
                    ->create('Permission'),
                $container
                    ->get('validatorFactory')
                    ->create('Permission')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param App\Repository\PermissionInterface
     * @param App\Validator\Permission
     *
     * @return void
     */
    public function __construct(
        PermissionInterface $repository,
        PermissionValidator $validator
    ) {
        $this->repository = $repository;
        $this->validator  = $validator;
    }

    /**
     * Creates a new Permission.
     *
     * @param App\Command\Permission\CreateNew $command
     *
     * @return array
     */
    public function handleCreateNew(CreateNew $command) : PermissionEntity {
        $this->validator->assertRouteName($command->routeName);
        $this->validator->assertId($command->companyId);

        $permission = $this->repository->create(
            [
                'route_name' => $command->routeName,
                'company_id' => $command->companyId,
                'created_at' => time()
            ]
        );

        $this->repository->save($permission);

        return $permission;
    }

    /**
     * Deletes all permissions ($command->companyId).
     *
     * @param App\Command\Permission\DeleteAll $command
     *
     * @return void
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        $this->validator->assertId($command->companyId);

        return $this->repository->deleteByCompanyId($command->companyId);
    }

    /**
     * Updates a Permission.
     *
     * @param App\Command\Permission\UpdateOne $command
     *
     * @return array
     */
    public function handleUpdateOne(UpdateOne $command) : int {
        $this->validator->assertId($command->companyId);
        $this->validator->assertPropName($command->propNameId);
        $this->validator->assertSectionName($command->sectionNameId);

        $permission = $this->repository->findOne($command->companyId, $command->sectionNameId, $command->propNameId);

        if ($command->value) {
            $permission->value = $command->value;
        }

        $success = $this->repository->update($permission);

        return $success ? $permission : 0;
    }

    /**
     * Deletes a Permission.
     *
     * @param App\Command\Permission\DeleteOne $command
     *
     * @return void
     */
    public function handleDeleteOne(DeleteOne $command) : int {
        $this->validator->assertId($command->companyId);
        $this->validator->assertRouteName($command->routeName);

        return $this->repository->deleteOne($command->companyId, $command->routeName);
    }

}