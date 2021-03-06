<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler\Profile;

use App\Command\Profile\Attribute\CreateNew;
use App\Command\Profile\Attribute\DeleteAll;
use App\Command\Profile\Attribute\UpsertBulk;
use App\Command\Profile\Attribute\UpsertOne;
use App\Entity\Profile\Attribute as AttributeEntity;
use App\Exception\Create;
use App\Exception\NotFound;
use App\Exception\Upsert\Profile\AttributeException as UpsertException;
use App\Exception\Validate;
use App\Factory\Event;
use App\Handler\HandlerInterface;
use App\Repository\RepositoryInterface;
use App\Validator\Profile\Attribute as AttributeValidator;
use Illuminate\Support\Collection;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles Attribute commands.
 */
class Attribute implements HandlerInterface {
    /**
     * Attribute Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $repository;
    /**
     * Attribute Validator instance.
     *
     * @var \App\Validator\Profile\Attribute
     */
    private $validator;
    /**
     * Event factory instance.
     *
     * @var \App\Factory\Event
     */
    private $eventFactory;
    /**
     * Event emitter instance.
     *
     * @var \League\Event\Emitter
     */
    private $emitter;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) : void {
        $container[self::class] = function (ContainerInterface $container) : HandlerInterface {
            return new \App\Handler\Profile\Attribute(
                $container
                    ->get('repositoryFactory')
                    ->create('Profile\Attribute'),
                $container
                    ->get('validatorFactory')
                    ->create('Profile\Attribute'),
                $container
                    ->get('eventFactory'),
                $container
                    ->get('eventEmitter')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param \App\Repository\RepositoryInterface $repository
     * @param \App\Validator\Profile\Attribute    $validator
     * @param \App\Factory\Event                  $eventFactory
     * @param \League\Event\Emitter               $emitter
     *
     * @return void
     */
    public function __construct(
        RepositoryInterface $repository,
        AttributeValidator $validator,
        Event $eventFactory,
        Emitter $emitter
    ) {
        $this->repository   = $repository;
        $this->validator    = $validator;
        $this->eventFactory = $eventFactory;
        $this->emitter      = $emitter;
    }

    /**
     * Creates a new attribute data for the given user.
     *
     * @param \App\Command\Profile\Attribute\CreateNew $command
     *
     * @see \App\Repository\DBAttribute::save
     *
     * @throws \App\Exception\Validate\Profile\AttributeException
     * @throws \App\Exception\Create\Profile\AttributeException
     *
     * @return \App\Entity\Profile\Attribute
     */
    public function handleCreateNew(CreateNew $command) : AttributeEntity {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertLongName($command->name, 'name');
            $this->validator->assertString($command->value, 'value');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\AttributeException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $entity = $this->repository->create(
            [
                'user_id'    => $command->user->id,
                'name'       => $command->name,
                'value'      => $command->value,
                'created_at' => time()
            ]
        );

        try {
            $entity = $this->repository->save($entity);
            $event  = $this->eventFactory->create('Profile\Attribute\Created', $entity, $command->credential);
            $this->emitter->emit($event);
        } catch (\Exception $exception) {
            throw new Create\Profile\AttributeException('Error while trying to create an attribute', 500, $exception);
        }

        return $entity;
    }

    /**
     * Creates or updates attribute data for the given user.
     *
     * @param \App\Command\Profile\Attribute\UpsertOne $command
     *
     * @see \App\Repository\DBAttribute::save
     *
     * @throws \App\Exception\Validate\Profile\AttributeException
     * @throws \App\Exception\Create\Profile\AttributeException
     *
     * @return \App\Entity\Profile\Attribute
     */
    public function handleUpsertOne(UpsertOne $command) : AttributeEntity {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertLongName($command->name, 'name');
            $this->validator->assertString($command->value, 'value');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\AttributeException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $now    = date('Y-m-d H:i:s');
        $entity = $this->repository->create(
            [
                'user_id'    => $command->user->id,
                'name'       => $command->name,
                'value'      => $command->value,
                'created_at' => $now
            ]
        );

        $entity = $this->repository->upsert(
            $entity,
            [
                'user_id',
                'name'
            ],
            [
                'value'      => $entity->getRawAttribute('value'),
                'updated_at' => $now
            ]
        );

        $event = $this->eventFactory->create('Profile\Attribute\Created', $entity, $command->credential);
        $this->emitter->emit($event);

        return $entity;
    }

    /**
     * Creates or updates attribute bulk data for the given user.
     *
     * @param \App\Command\Profile\Attribute\UpsertBulk $command
     *
     * @see \App\Repository\DBAttribute::save
     *
     * @throws \App\Exception\Validate\Profile\AttributeException
     * @throws \App\Exception\Create\Profile\AttributeException
     *
     * @return \Illuminate\Support\Collection entities
     */
    public function handleUpsertBulk(UpsertBulk $command) : Collection {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertAttributeArray($command->attributes, 'attributes');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\AttributeException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        try {
            $now = date('Y-m-d H:i:s');

            $entities = [];
            $this->repository->beginTransaction();
            foreach ($command->attributes as $attribute) {
                $entity = $this->repository->create($attribute);

                $serialized = $entity->serialize();

                $this->repository->upsert(
                    $entity,
                    [
                        'user_id',
                        'name'
                    ],
                    [
                        'value'      => $serialized['value'],
                        'updated_at' => $now
                    ]
                );

                $entities[] = $entity;
            }

            $event = $this->eventFactory->create('Profile\Attribute\UpsertedBulk', $command->attributes, $command->user, $command->credential);
            $this->emitter->emit($event);

            $this->repository->commit();

            return new Collection($entities);
        } catch (\Exception $exception) {
            $this->repository->rollBack();
            throw new UpsertException('Error while upserting attributes.', 500, $exception);
        }
    }

    /**
     * Deletes all attribute data from a given user.
     *
     * @param \App\Command\Profile\Attribute\DeleteAll $command
     *
     * @see \App\Repository\DBAttribute::getAllByUserIdAndNames
     * @see \App\Repository\DBAttribute::deleteByUserId
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertArray($command->queryParams, 'queryParams');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\AttributeException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $entities = $this->repository->findByUserId($command->user->id, $command->queryParams);

        $affectedRows = 0;

        try {
            $affectedRows = $this->repository->deleteByUserId($command->user->id);

            if ($affectedRows) {
                $event = $this->eventFactory->create('Profile\Attribute\DeletedMulti', $entities, $command->credential);
                $this->emitter->emit($event);
            }
        } catch (\Exception $exception) {
            throw new NotFound\Profile\AttributeException('Error while deleting all attributes', 404);
        }

        return $affectedRows;
    }
}
