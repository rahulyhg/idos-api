<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler\Profile;

use App\Command\Profile\Process\CreateNew;
use App\Command\Profile\Process\UpdateOne;
use App\Entity\Profile\Process as ProcessEntity;
use App\Event\Profile\Process\Created;
use App\Event\Profile\Process\Updated;
use App\Exception\Create;
use App\Exception\Update;
use App\Exception\Validate;
use App\Handler\HandlerInterface;
use App\Repository\Profile\ProcessInterface;
use App\Validator\Profile\Process as ProcessValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles Process commands.
 */
class Process implements HandlerInterface {
    /**
     * Process Repository instance.
     *
     * @var App\Repository\Profile\ProcessInterface
     */
    protected $repository;
    /**
     * Process Validator instance.
     *
     * @var App\Validator\Profile\Process
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
        $container[self::class] = function (ContainerInterface $container) {
            return new \App\Handler\Profile\Process(
                $container
                    ->get('repositoryFactory')
                    ->create('Profile\Process'),
                $container
                    ->get('validatorFactory')
                    ->create('Profile\Process'),
                $container
                    ->get('eventEmitter')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param App\Repository\Profile\ProcessInterface $repository
     * @param App\Validator\Profile\Process           $validator
     *
     * @return void
     */
    public function __construct(
        ProcessInterface $repository,
        ProcessValidator $validator,
        Emitter $emitter
    ) {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->emitter    = $emitter;
    }

    /**
     * Creates a process.
     *
     * @param App\Command\Profile\Process\CreateNew $command
     *
     * @see App\Repository\DBProcess::create
     * @see App\Repository\DBProcess::save
     *
     * @throws App\Exception\Validate\ProcessException
     * @throws App\Exception\Create\ProcessException
     *
     * @return App\Entity\Process
     */
    public function handleCreateNew(CreateNew $command) : ProcessEntity {
        try {
            $this->validator->assertName($command->name);
            $this->validator->assertName($command->event);
            $this->validator->assertId($command->userId);
        } catch (ValidationException $e) {
            throw new Validate\Profile\ProcessException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $process = $this->repository->create(
            [
                'name'       => $command->name,
                'event'      => $command->event,
                'user_id'    => $command->userId,
                'created_at' => time()
            ]
        );

        try {
            $this->repository->save($process);
            $event = new Created($process);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new Create\Profile\ProcessException('Error while trying to create a process', 500, $e);
        }

        return $process;
    }

    /**
     * Updates a Process.
     *
     * @param App\Command\Profile\Process\UpdateOne $command
     *
     * @see App\Repository\DBProcess::find
     * @see App\Repository\DBProcess::save
     *
     * @throws App\Exception\Validate\ProcessException
     * @throws App\Exception\Update\ProcessException
     *
     * @return App\Entity\Process
     */
    public function handleUpdateOne(UpdateOne $command) : ProcessEntity {
        try {
            $this->validator->assertName($command->name);
            $this->validator->assertName($command->event);
            $this->validator->assertId($command->id);
        } catch (ValidationException $e) {
            throw new Validate\Profile\ProcessException(
                $e->getFullMessage(),
                400,
                $e
            );
        }

        $process = $this->repository->find($command->id);

        $process->name      = $command->name;
        $process->event     = $command->event;
        $process->updatedAt = time();

        try {
            $process = $this->repository->save($process);
            $event   = new Updated($process);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
            throw new Update\Profile\ProcessException('Error while trying to update a feature', 500, $e);
        }

        return $process;
    }
}