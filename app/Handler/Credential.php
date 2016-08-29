<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler;

use App\Command\Credential\CreateNew;
use App\Command\Credential\DeleteAll;
use App\Command\Credential\DeleteOne;
use App\Command\Credential\UpdateOne;
use App\Entity\Credential as CredentialEntity;
use App\Repository\CredentialInterface;
use App\Validator\Credential as CredentialValidator;
use Defuse\Crypto\Key;
use Interop\Container\ContainerInterface;

/**
 * Handles Credential commands.
 */
class Credential implements HandlerInterface {
    /**
     * Credential Repository instance.
     *
     * @var App\Repository\CredentialInterface
     */
    protected $repository;
    /**
     * Credential Validator instance.
     *
     * @var App\Validator\Credential
     */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) : HandlerInterface {
            return new \App\Handler\Credential(
                $container
                    ->get('repositoryFactory')
                    ->create('Credential'),
                $container
                    ->get('validatorFactory')
                    ->create('Credential')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param App\Repository\CredentialInterface
     * @param App\Validator\Credential
     *
     * @return void
     */
    public function __construct(
        CredentialInterface $repository,
        CredentialValidator $validator
    ) {
        $this->repository = $repository;
        $this->validator  = $validator;
    }

    /**
     * Creates a new child Credential ($command->companyId).
     *
     * @param App\Command\Credential\CreateNew $command
     *
     * @return App\Entity\Credential
     */
    public function handleCreateNew(CreateNew $command) : CredentialEntity {
        $this->validator->assertName($command->name);
        $this->validator->assertFlag($command->production);
        $this->validator->assertId($command->companyId);

        $credential = $this->repository->create(
            [
                'name'       => $command->name,
                'production' => $this->validator->validateFlag($command->production),
                'company_id' => $command->companyId,
                'created_at' => time()
            ]
        );

        $credential->public  = md5((string) time()); // Key::createNewRandomKey()->saveToAsciiSafeString();
        $credential->private = md5((string) time()); // Key::createNewRandomKey()->saveToAsciiSafeString();

        return $this->repository->save($credential);
    }

    /**
     * Updates a Credential.
     *
     * @param App\Command\Credential\UpdateOne $command
     *
     * @return App\Entity\Credential
     */
    public function handleUpdateOne(UpdateOne $command) : CredentialEntity {
        $this->validator->assertId($command->credentialId);
        $this->validator->assertName($command->name);

        $credential            = $this->repository->find($command->credentialId);
        $credential->name      = $command->name;
        $credential->updatedAt = time();

        return $this->repository->save($credential);
    }

    /**
     * Deletes a Credential.
     *
     * @param App\Command\Credential\DeleteOne $command
     *
     * @return int
     */
    public function handleDeleteOne(DeleteOne $command) : int {
        $this->validator->assertId($command->credentialId);

        return $this->repository->delete($command->credentialId);
    }

    /**
     * Deletes all credentials ($command->companyId).
     *
     * @param App\Command\Credential\DeleteAll $command
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        $this->validator->assertId($command->companyId);

        return $this->repository->deleteByCompanyId($command->companyId);
    }
}
