<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler\User;

use App\Command\User\RoleAccess\CreateNew;
use App\Command\User\RoleAccess\DeleteAll;
use App\Command\User\RoleAccess\DeleteOne;
use App\Command\User\RoleAccess\UpdateOne;
use App\Entity\User\RoleAccess as RoleAccessEntity;
use App\Event\User\RoleAccess\Created;
use App\Event\User\RoleAccess\Deleted;
use App\Event\User\RoleAccess\DeletedMulti;
use App\Event\User\RoleAccess\Updated;
use App\Exception\Create;
use App\Exception\NotFound;
use App\Exception\Update;
use App\Exception\Validate;
use App\Handler\HandlerInterface;
use App\Repository\User\RoleAccessInterface;
use App\Validator\User\RoleAccess as RoleAccessValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles RoleAccess commands.
 */
class RoleAccess implements HandlerInterface {
    /**
     * RoleAccess Repository instance.
     *
     * @var App\Repository\User\RoleAccessInterface
     */
    protected $repository;

    /**
     * RoleAccess Validator instance.
     *
     * @var App\Validator\User\RoleAccess
     */
    protected $validator;
    /**
     * Event emitter instance.
     *
     * @var League\Event\Emitter
     */
    protected $emitter;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) : HandlerInterface {
            return new \App\Handler\User\RoleAccess(
                $container
                    ->get('repositoryFactory')
                    ->create('User\RoleAccess'),
                $container
                    ->get('validatorFactory')
                    ->create('User\RoleAccess'),
                $container
                    ->get('eventEmitter')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param App\Repository\User\RoleAccessInterface
     * @param App\Validator\User\RoleAccess
     *
     * @return void
     */
    public function __construct(
        RoleAccessInterface $repository,
        RoleAccessValidator $validator,
        Emitter $emitter
    ) {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->emitter    = $emitter;
    }

    /**
     * Creates a new child RoleAccess.
     *
     * @param App\Command\User\RoleAccess\CreateNew $command
     *
     * @throws App\Exception\Validate\RoleAccessException
     * @throws App\Exception\Create\RoleAccessException
     *
     * @return App\Entity\RoleAccess
     */
    public function handleCreateNew(CreateNew $command) : RoleAccessEntity {
        try {
            $this->validator->assertRoleName($command->role);
            $this->validator->assertResource($command->resource);
            $this->validator->assertAccess($command->access);
            $this->validator->assertId($command->identityId);
        } catch (ValidationException $e) {
            throw new Validate\User\RoleAccessException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $now = time();

        $entity = $this->repository->create(
            [
                'role'        => $command->role,
                'resource'    => $command->resource,
                'access'      => $command->access,
                'identity_id' => $command->identityId,
                'created_at'  => $now,
                'updated_at'  => $now
            ]
        );

        try {
            $entity = $this->repository->save($entity);
            $event  = new Created($entity);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new Create\User\RoleAccessException('Error while trying to create a role access', 500, $e);
        }

        return $entity;
    }

    /**
     * Deletes all RoleAccess of the identity.
     *
     * @param App\Command\User\RoleAccess\DeleteAll $command
     *
     * @throws App\Exception\Validate\RoleAccessException
     *
     * @return int number of affected rows
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        try {
            $this->validator->assertId($command->identityId);
        } catch (ValidationException $e) {
            throw new Validate\User\RoleAccessException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $roleAccesses = $this->repository->findByIdentity($command->identityId);

        $rowsAffected = $this->repository->deleteAllFromIdentity($command->identityId);

        $event = new DeletedMulti($roleAccesses);
        $this->emitter->emit($event);

        return $rowsAffected;
    }

    /**
     * Updates a RoleAccess.
     *
     * @param App\Command\User\RoleAccess\UpdateOne $command
     *
     * @throws App\Exception\Validate\RoleAccessException
     * @throws App\Exception\Update\RoleAccessException
     *
     * @return App\Entity\RoleAccess
     */
    public function handleUpdateOne(UpdateOne $command) : RoleAccessEntity {
        try {
            $this->validator->assertId($command->identityId);
            $this->validator->assertId($command->roleAccessId);
            $this->validator->assertAccess($command->access);
        } catch (ValidationException $e) {
            throw new Validate\User\RoleAccessException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        // finds entity
        $entity            = $this->repository->findOne($command->identityId, $command->roleAccessId);
        $entity->access    = $command->access;
        $entity->updatedAt = time();

        // saves entity
        try {
            $entity = $this->repository->save($entity);
            $event  = new Updated($entity);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new Update\User\RoleAccessException('Error while trying to update a role access', 500, $e);
        }

        return $entity;
    }

    /**
     * Deletes a RoleAccess.
     *
     * @param App\Command\User\RoleAccess\DeleteOne $command
     *
     * @throws App\Exception\Validate\RoleAccessException
     * @throws App\Exception\NotFound\RoleAccessException
     *
     * @return void
     */
    public function handleDeleteOne(DeleteOne $command) {
        try {
            $this->validator->assertId($command->identityId);
            $this->validator->assertId($command->roleAccessId);
        } catch (ValidationException $e) {
            throw new Validate\User\RoleAccessException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $roleAccess   = $this->repository->findOne($command->identityId, $command->roleAccessId);
        $rowsAffected = $this->repository->deleteOne($command->identityId, $command->roleAccessId);

        if (! $rowsAffected) {
            throw new NotFound\User\RoleAccessException('No role accesses found for deletion', 404);
        }

        $event = new Deleted($roleAccess);
        $this->emitter->emit($event);
    }
}
