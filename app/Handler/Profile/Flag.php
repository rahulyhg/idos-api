<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler\Profile;

use App\Command\Profile\Flag\CreateNew;
use App\Command\Profile\Flag\DeleteAll;
use App\Command\Profile\Flag\DeleteOne;
use App\Entity\Profile\Flag as FlagEntity;
use App\Exception\Create;
use App\Exception\Validate;
use App\Factory\Event;
use App\Handler\HandlerInterface;
use App\Repository\Profile\FlagInterface;
use App\Validator\Profile\Flag as FlagValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles Flag commands.
 */
class Flag implements HandlerInterface {
    /**
     * Flag Repository instance.
     *
     * @var \App\Repository\Profile\FlagInterface
     */
    private $repository;
    /**
     * Flag Validator instance.
     *
     * @var \App\Validator\Profile\Flag
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
    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) {
            return new \App\Handler\Profile\Flag(
                $container
                    ->get('repositoryFactory')
                    ->create('Profile\Flag'),
                $container
                    ->get('validatorFactory')
                    ->create('Profile\Flag'),
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
     * @param \App\Repository\Profile\FlagInterface $repository
     * @param \App\Validator\Profile\Flag           $validator
     * @param \App\Factory\Event                    $eventFactory
     * @param \League\Event\Emitter                 $emitter
     *
     * @return void
     */
    public function __construct(
        FlagInterface $repository,
        FlagValidator $validator,
        Event $eventFactory,
        Emitter $emitter
    ) {
        $this->repository   = $repository;
        $this->validator    = $validator;
        $this->eventFactory = $eventFactory;
        $this->emitter      = $emitter;
    }

    /**
     * Creates a flag.
     *
     * @param \App\Command\Profile\Flag\CreateNew $command
     *
     * @throws \App\Exception\Validate\Profile\FlagException
     * @throws \App\Exception\Create\Profile\FlagException
     *
     * @see \App\Repository\DBFlag::save
     * @see \App\Repository\DBFlag::hydrateRelations
     *
     * @return \App\Entity\Profile\Flag
     */
    public function handleCreateNew(CreateNew $command) : FlagEntity {
        try {
            $this->validator->assertUser($command->user);
            $this->validator->assertService($command->service);
            $this->validator->assertSlug($command->slug);

            if (isset($command->attribute)) {
                $this->validator->assertSlug($command->attribute);
            }
        } catch (ValidationException $e) {
            throw new Validate\Profile\FlagException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $entity = $this->repository->create(
            [
                'user_id'    => $command->user->id,
                'creator'    => $command->service->id,
                'slug'       => $command->slug,
                'attribute'  => $command->attribute,
                'created_at' => time()
            ]
        );

        try {
            $entity = $this->repository->save($entity);
            $entity = $this->repository->hydrateRelations($entity);

            $event = $this->eventFactory->create('Profile\\Flag\\Created', $entity);
            $this->emitter->emit($event);
        } catch (\Exception $exception) {
            throw new Create\Profile\FlagException('Error while trying to create a flag', 500, $e);
        }

        return $entity;
    }

    /**
     * Deletes a Flag.
     *
     * @param \App\Command\Profile\Flag\DeleteOne $command
     *
     * @throws \App\Exception\Validate\FlagException
     * @throws \App\Exception\AppException
     *
     * @see \App\Repository\DBFlag::findOneBySlug
     * @see \App\Repository\DBFlag::delete
     *
     * @return void
     */
    public function handleDeleteOne(DeleteOne $command) : int {
        try {
            $this->validator->assertUser($command->user);
            $this->validator->assertService($command->service);
            $this->validator->assertSlug($command->slug);
        } catch (ValidationException $e) {
            throw new Validate\Profile\FlagException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $entity = $this->repository->findOne($command->slug, $command->service->id, $command->user->id);

        try {
            $affectedRows = $this->repository->delete($entity->id);

            $event = $this->eventFactory->create('Profile\\Flag\\Deleted', $entity);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new AppException('Error while deleting flag');
        }

        return $affectedRows;
    }

    /**
     * Deletes all settings ($command->userId).
     *
     * @param \App\Command\Profile\Flag\DeleteAll $command
     *
     * @throws \App\Exception\Validate\Profile\FlagException
     * @throws \App\Exception\AppException
     *
     * @see \App\Repository\DBFlag::findBy
     * @see \App\Repository\DBFlag::delete
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        try {
            $this->validator->assertUser($command->user);
            $this->validator->assertService($command->service);
        } catch (ValidationException $e) {
            throw new Validate\Profile\FlagException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $entities = $this->repository->getByUserIdAndServiceId(
            $command->service->id,
            $command->user->id,
            $command->queryParams
        );

        $affectedRows = 0;
        try {
            foreach ($entities as $entity) {
                $affectedRows += $this->repository->delete($entity->id);
            }

            $event = $this->eventFactory->create('Profile\\Flag\\DeletedMulti', $entities);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new AppException('Error while deleting flags');
        }

        return $affectedRows;
    }
}