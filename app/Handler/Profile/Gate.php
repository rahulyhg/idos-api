<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler\Profile;

use App\Command\Profile\Gate\CreateNew;
use App\Command\Profile\Gate\DeleteAll;
use App\Command\Profile\Gate\DeleteOne;
use App\Command\Profile\Gate\UpdateOne;
use App\Command\Profile\Gate\UpsertOne;
use App\Entity\Category;
use App\Entity\Profile\Gate as GateEntity;
use App\Exception\Create;
use App\Exception\NotFound;
use App\Exception\Update;
use App\Exception\Validate;
use App\Factory\Event;
use App\Handler\HandlerInterface;
use App\Repository\RepositoryInterface;
use App\Validator\Profile\Gate as GateValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles Gate commands.
 */
class Gate implements HandlerInterface {
    /**
     * Gate Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $repository;
    /**
     * Category Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $categoryRepository;
    /**
     * Gate Validator instance.
     *
     * @var \App\Validator\Profile\Gate
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
     * Upserts a category.
     *
     * @param string $name      The name
     * @param int    $handlerId The handler identifier
     *
     * @throws \App\Exception\Update\Profile\GateException
     *
     * @return \App\Entity\Category
     */
    private function upsertCategory(string $name, int $handlerId) : Category {
        try {
            $category = $this->categoryRepository->create(
                [
                    'display_name' => $name,
                    'name'         => $name,
                    'handler_id'   => $handlerId,
                    'type'         => 'gate'
                ]
            );

            return $this->categoryRepository->upsert(
                $category,
                [
                    'name'
                ]
            );
        } catch (\Exception $exception) {
            throw new Update\Profile\GateException('Error while trying to upsert a Gate category', 500, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) : void {
        $container[self::class] = function (ContainerInterface $container) : HandlerInterface {
            $repositoryFactory = $container->get('repositoryFactory');

            return new \App\Handler\Profile\Gate(
                $repositoryFactory
                    ->create('Profile\Gate'),
                $repositoryFactory
                    ->create('Category'),
                $container
                    ->get('validatorFactory')
                    ->create('Profile\Gate'),
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
     * @param \App\Repository\RepositoryInterface $categoryRepository
     * @param \App\Validator\Profile\Gate         $validator
     * @param \App\Factory\Event                  $eventFactory
     * @param \League\Event\Emitter               $emitter
     *
     * @return void
     */
    public function __construct(
        RepositoryInterface $repository,
        RepositoryInterface $categoryRepository,
        GateValidator $validator,
        Event $eventFactory,
        Emitter $emitter
    ) {
        $this->repository           = $repository;
        $this->categoryRepository   = $categoryRepository;
        $this->validator            = $validator;
        $this->eventFactory         = $eventFactory;
        $this->emitter              = $emitter;
    }

    /**
     * Creates a gate.
     *
     * @param \App\Command\Profile\Gate\CreateNew $command
     *
     * @see \App\Repository\Profile\DBGate::save
     *
     * @throws \App\Exception\Validate\Profile\GateException
     * @throws \App\Exception\Create\Profile\GateException
     *
     * @return \App\Entity\Profile\Gate
     */
    public function handleCreateNew(CreateNew $command) : GateEntity {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertName($command->name, 'name');
            $this->validator->assertCredential($command->credential, 'credential');
            $this->validator->assertMediumName($command->confidenceLevel, 'confidenceLevel');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\GateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $entity = $this->repository->create(
            [
                'user_id'          => $command->user->id,
                'creator'          => $command->handler->id,
                'name'             => $command->name,
                'confidence_level' => $command->confidenceLevel,
                'created_at'       => time()
            ]
        );

        try {
            $this->upsertCategory($command->name, $command->handler->id);

            $entity = $this->repository->save($entity);
            $entity = $this->repository->hydrateRelations($entity);

            $event = $this->eventFactory->create('Profile\Gate\Created', $entity, $command->credential);
            $this->emitter->emit($event);
        } catch (\Exception $exception) {
            throw new Create\Profile\GateException('Error while trying to create a gate', 500, $exception);
        }

        return $entity;
    }

    /**
     * Updates a gate.
     *
     * @param \App\Command\Profile\Gate\UpdateOne $command
     *
     * @see \App\Repository\Profile\DBGate::findBySlug
     * @see \App\Repository\Profile\DBGate::save
     *
     * @throws \App\Exception\Validate\Profile\GateException
     * @throws \App\Exception\Update\Profile\GateException
     *
     * @return \App\Entity\Profile\Gate
     */
    public function handleUpdateOne(UpdateOne $command) : GateEntity {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertSlug($command->slug, 'slug');
            $this->validator->assertCredential($command->credential, 'credential');
            $this->validator->assertMediumName($command->confidenceLevel, 'confidenceLevel');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\GateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $entity = $this->repository->findBySlug($command->slug, $command->handler->id, $command->user->id);

        $entity->confidence_level = $command->confidenceLevel;
        $entity->updatedAt        = time();

        try {
            $entity = $this->repository->save($entity);
            $entity = $this->repository->hydrateRelations($entity);

            $event = $this->eventFactory->create('Profile\Gate\Updated', $entity, $command->credential);
            $this->emitter->emit($event);
        } catch (\Exception $exception) {
            throw new Update\Profile\GateException('Error while trying to update a gate', 500, $exception);
        }

        return $entity;
    }

    /**
     * Creates or updates a gate.
     *
     * @param \App\Command\Profile\Gate\UpsertOne $command
     *
     * @return \App\Entity\Profile\Gate
     */
    public function handleUpsertOne(UpsertOne $command) : GateEntity {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertName($command->name, 'name');
            $this->validator->assertMediumName($command->confidenceLevel, 'confidenceLevel');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\GateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        try {
            $this->repository->beginTransaction();
            $this->upsertCategory($command->name, $command->handler->id);

            $entity = $this->repository->create(
                [
                'creator'          => $command->handler->id,
                'user_id'          => $command->user->id,
                'name'             => $command->name,
                'confidence_level' => $command->confidenceLevel
                ]
            );

            $entity = $this->repository->upsert(
                $entity, ['user_id', 'creator', 'name'], [
                'updated_at'       => date('Y-m-d H:i:s'),
                'confidence_level' => $entity->confidenceLevel
                ]
            );

            $this->repository->commit();

            $entity = $this->repository->hydrateRelations($entity);

            $eventClass = 'Profile\Gate\Created';
            if ($entity->updatedAt) {
                $eventClass = 'Profile\Gate\Updated';
            }

            $event = $this->eventFactory->create(
                $eventClass,
                $entity,
                $command->credential
            );

            $this->emitter->emit($event);
        } catch (\Exception $exception) {
            $this->repository->rollBack();
            throw new Update\Profile\GateException('Error while trying to upsert a gate', 500, $exception);
        }

        return $entity;
    }

    /**
     * Deletes a Gate.
     *
     * @param \App\Command\Profile\Gate\DeleteOne $command
     *
     * @see \App\Repository\Profile\DBGate::findBySlug
     * @see \App\Repository\Profile\DBGate::delete
     *
     * @throws \App\Exception\Validate\Profile\GateException
     * @throws \App\Exception\NotFound\Profile\GateException
     *
     * @return int
     */
    public function handleDeleteOne(DeleteOne $command) : int {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertSlug($command->slug, 'slug');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\GateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        try {
            $entity       = $this->repository->findBySlug($command->slug, $command->handler->id, $command->user->id);
            $affectedRows = $this->repository->delete($entity->id);

            if ($affectedRows) {
                $event = $this->eventFactory->create('Profile\Gate\Deleted', $entity, $command->credential);
                $this->emitter->emit($event);
            }
        } catch (\Exception $exception) {
            throw new NotFound\Profile\GateException('No gates found for deletion', 404);
        }

        return $affectedRows;
    }

    /**
     * Deletes all gates ($command->userId).
     *
     * @param \App\Command\Profile\Gate\DeleteAll $command
     *
     * @see \App\Repository\Profile\DBGate::getByHandlerIdAndUserId
     * @see \App\Repository\Profile\DBGate::delete
     *
     * @throws \App\Exception\Validate\Profile\GateException
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\GateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $entities = $this->repository->getByHandlerIdAndUserId(
            $command->handler->id,
            $command->user->id,
            $command->queryParams
        );

        $affectedRows = 0;
        try {
            foreach ($entities as $entity) {
                $affectedRows += $this->repository->delete($entity->id);
            }

            if ($affectedRows) {
                $event = $this->eventFactory->create('Profile\Gate\DeletedMulti', $entities, $command->credential);
                $this->emitter->emit($event);
            }
        } catch (\Exception $exception) {
            throw new Update\Profile\GateException('Error while trying to delete all gates', 500, $exception);
        }

        return $affectedRows;
    }
}
