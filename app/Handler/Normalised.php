<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler;

use App\Command\Normalised\CreateNew;
use App\Command\Normalised\DeleteAll;
use App\Command\Normalised\DeleteOne;
use App\Command\Normalised\UpdateOne;
use App\Entity\Normalised as NormalisedEntity;
use App\Event\Normalised\Created;
use App\Event\Normalised\Deleted;
use App\Event\Normalised\DeletedMulti;
use App\Event\Normalised\ProfileSet;
use App\Event\Normalised\Updated;
use App\Exception\Create;
use App\Exception\Update;
use App\Exception\Validate;
use App\Repository\NormalisedInterface;
use App\Validator\Normalised as NormalisedValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles Normalised commands.
 */
class Normalised implements HandlerInterface {
    /**
     * Normalised Repository instance.
     *
     * @var App\Repository\NormalisedInterface
     */
    protected $repository;
    /**
     * Normalised Validator instance.
     *
     * @var App\Validator\Normalised
     */
    protected $validator;
    /**
     * Event emitter instance.
     *
     * @var \League\Event\Emitter
     */
    protected $emitter;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) {
            return new \App\Handler\Normalised(
                $container
                    ->get('repositoryFactory')
                    ->create('Normalised'),
                $container
                    ->get('validatorFactory')
                    ->create('Normalised'),
                $container
                    ->get('eventEmitter')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param App\Repository\NormalisedInterface $repository
     * @param App\Validator\Normalised           $validator
     * @param \League\Event\Emitter              $emitter
     *
     * @return void
     */
    public function __construct(
        NormalisedInterface $repository,
        NormalisedValidator $validator,
        Emitter $emitter
    ) {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->emitter    = $emitter;
    }

    /**
     * Creates a new normalised data in the given source.
     *
     * @param App\Command\Normalised\CreateNew $command
     *
     * @return App\Entity\Normalised
     */
    public function handleCreateNew(CreateNew $command) : NormalisedEntity {
        try {
            $this->validator->assertId($command->sourceId);
            $this->validator->assertName($command->name);
            $this->validator->assertValue($command->value);
        } catch (ValidationException $e) {
            throw new Validate\NormalisedException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        //@FIXME: check here if given source ($command->sourceId) has user_id == $command->user->id

        $normalised = $this->repository->create(
            [
                'source_id'  => $command->sourceId,
                'name'       => $command->name,
                'value'      => $command->value,
                'created_at' => time()
            ]
        );

        try {
            $normalised = $this->repository->save($normalised);
            $event      = new Created($normalised);
            $this->emitter->emit($event);
            if ($command->name === 'id') {
                $event = new ProfileSet($normalised);
                $this->emitter->emit($event);
            }
        } catch (\Exception $e) {
            throw new Create\NormalisedException('Error while trying to create a normalised', 500, $e);
        }

        return $normalised;
    }

    /**
     * Updates a normalised data from a given source.
     *
     * @param App\Command\Normalised\UpdateOne $command
     *
     * @return App\Entity\Normalised
     */
    public function handleUpdateOne(UpdateOne $command) : NormalisedEntity {
        try {
            $this->validator->assertId($command->sourceId);
            $this->validator->assertValue($command->value);
        } catch (ValidationException $e) {
            throw new Validate\NormalisedException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        //@FIXME: check here if given source ($command->sourceId) has user_id == $command->user->id

        $normalised        = $this->repository->findOneByUserIdSourceIdAndName($command->user->id, $command->sourceId, $command->name);
        $normalised->value = $command->value;

        try {
            $normalised = $this->repository->save($normalised);
            $event      = new Updated($normalised);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new Update\NormalisedException('Error while trying to update a normalised', 500, $e);
        }

        return $normalised;
    }

    /**
     * Deletes a normalised data from a given source.
     *
     * @param App\Command\Normalised\DeleteOne $command
     *
     * @return int
     */
    public function handleDeleteOne(DeleteOne $command) : int {
        try {
            $this->validator->assertUser($command->user);
            $this->validator->assertId($command->user->id);
            $this->validator->assertId($command->sourceId);
            $this->validator->assertName($command->name);
        } catch (ValidationException $e) {
            throw new Validate\NormalisedException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        //@FIXME: check here if given source ($command->sourceId) has user_id == $command->user->id

        $normalised = $this->repository->findOneByUserIdSourceIdAndName(
            $command->user->id,
            $command->sourceId,
            $command->name
        );

        $rowsAffected = $this->repository->deleteOneBySourceIdAndName($command->sourceId, $command->name);

        if (! $rowsAffected) {
            throw new NotFound\NormalisedException('No normaliseds found for deletion', 404);
        }

        $event = new Deleted($normalised);
        $this->emitter->emit($event);

        return $rowsAffected;
    }

    /**
     * Deletes all normalised data from a given source.
     *
     * @param App\Command\Normalised\DeleteAll $command
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        try {
            $this->validator->assertUser($command->user);
            $this->validator->assertId($command->user->id);
            $this->validator->assertId($command->sourceId);
        } catch (ValidationException $e) {
            throw new Validate\NormalisedException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        //@FIXME: check here if given source ($command->sourceId) has user_id == $command->user->id

        $deletedItems = $this->repository->getAllByUserIdAndSourceId($command->user->id, $command->sourceId);

        $rowsAffected = $this->repository->deleteBySourceId($command->sourceId);

        $event = new DeletedMulti($deletedItems);
        $this->emitter->emit($event);

        return $rowsAffected;
    }
}
