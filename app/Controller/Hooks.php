<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Exception\NotFound;
use App\Factory\Command;
use App\Repository\CredentialInterface;
use App\Repository\HookInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /companies/{companySlug}/credentials/{pubKey}/hooks.
 */
class Hooks implements ControllerInterface {
    /**
     * Hook Repository instance.
     *
     * @var App\Repository\HookInterface
     */
    private $repository;
    /**
     * Credential Repository instance.
     *
     * @var App\Repository\CredentialInterface
     */
    private $credentialRepository;
    /**
     * Command Bus instance.
     *
     * @var \League\Tactician\CommandBus
     */
    private $commandBus;
    /**
     * Command Factory instance.
     *
     * @var App\Factory\Command
     */
    private $commandFactory;

    /**
     * Class constructor.
     *
     * @param App\Repository\HookInterface       $repository
     * @param App\Repository\CredentialInterface $credentialRepository
     * @param \League\Tactician\CommandBus       $commandBus
     * @param App\Factory\Command                $commandFactory
     *
     * @return void
     */
    public function __construct(
        HookInterface $repository,
        CredentialInterface $credentialRepository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository           = $repository;
        $this->credentialRepository = $credentialRepository;
        $this->commandBus           = $commandBus;
        $this->commandFactory       = $commandFactory;
    }

    /**
     * Lists all hooks associated with given credential.
     *
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey 4c9184f37cff01bcdc32dc486ec36961
     * @apiEndpointResponse 200 schema/hook/listAll.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingCompany    = $request->getAttribute('actingCompany');
        $targetCompany    = $request->getAttribute('targetCompany');
        $credentialPubKey = $request->getAttribute('pubKey');

        $credential = $this->credentialRepository->findByPubKey($credentialPubKey);

        if ($credential->companyId != $targetCompany->id) {
            throw new NotFound();
        }

        $hooks = $this->repository->getAllByCredentialId($credential->id);

        $body = [
            'data'    => $hooks->toArray(),
            'updated' => (
                $hooks->isEmpty() ? time() : max($hooks->max('updatedAt'), $hooks->max('createdAt'))
            )
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Creates a new hook for the given credential.
     *
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey 4c9184f37cff01bcdc32dc486ec36961
     * @apiEndpointResponse 201 schema/hook/hookEntity.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNew(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $bodyRequest = $request->getParsedBody();

        $actingCompany    = $request->getAttribute('actingCompany');
        $targetCompany    = $request->getAttribute('targetCompany');
        $credentialPubKey = $request->getAttribute('pubKey');

        $command = $this->commandFactory->create('Hook\\CreateNew');
        $command
            ->setParameter('credentialPubKey', $credentialPubKey)
            ->setParameter('company', $targetCompany)
            ->setParameters($bodyRequest);

        $hook = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $hook->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('statusCode', 201)
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Updates a hook from the given credential.
     *
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey 4c9184f37cff01bcdc32dc486ec36961
     * @apiEndpointResponse 200 schema/hook/updateOne.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $bodyRequest = $request->getParsedBody();

        $actingCompany    = $request->getAttribute('actingCompany');
        $targetCompany    = $request->getAttribute('targetCompany');
        $credentialPubKey = $request->getAttribute('pubKey');

        $command = $this->commandFactory->create('Hook\\UpdateOne');
        $command
            ->setParameter('hookId', $request->getAttribute('decodedHookId'))
            ->setParameter('credentialPubKey', $credentialPubKey)
            ->setParameter('company', $targetCompany)
            ->setParameters($bodyRequest);

        $hook = $this->commandBus->handle($command);

        $body = [
            'data'    => $hook->toArray(),
            'updated' => $hook->updated_at
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Retrieves a hook from the given credential.
     *
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey 4c9184f37cff01bcdc32dc486ec36961
     * @apiEndpointResponse 200 schema/hook/hookEntity.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingCompany    = $request->getAttribute('actingCompany');
        $targetCompany    = $request->getAttribute('targetCompany');
        $credentialPubKey = $request->getAttribute('pubKey');

        $credential = $this->credentialRepository->findByPubKey($credentialPubKey);

        if ($credential->companyId != $targetCompany->id) {
            throw new NotFound();
        }

        $hook = $this->repository->find($request->getAttribute('decodedHookId'));

        if ($hook->credential_id != $credential->id) {
            throw new NotFound();
        }

        $body = [
            'data' => $hook->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes all hooks from the given credential.
     *
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey 4c9184f37cff01bcdc32dc486ec36961
     * @apiEndpointResponse 200 schema/hook/deleteAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingCompany    = $request->getAttribute('actingCompany');
        $targetCompany    = $request->getAttribute('targetCompany');
        $credentialPubKey = $request->getAttribute('pubKey');

        $command = $this->commandFactory->create('Hook\\DeleteAll');
        $command
            ->setParameter('credentialPubKey', $credentialPubKey)
            ->setParameter('company', $targetCompany);

        $body = [
            'deleted' => $this->commandBus->handle($command)
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes a hook from the given credential.
     *
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey 4c9184f37cff01bcdc32dc486ec36961
     * @apiEndpointResponse 200 schema/hook/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingCompany    = $request->getAttribute('actingCompany');
        $targetCompany    = $request->getAttribute('targetCompany');
        $credentialPubKey = $request->getAttribute('pubKey');

        $command = $this->commandFactory->create('Hook\\DeleteOne');
        $command
            ->setParameter('hookId', $request->getAttribute('decodedHookId'))
            ->setParameter('credentialPubKey', $credentialPubKey)
            ->setParameter('company', $targetCompany);

        $deleted = $this->commandBus->handle($command);
        $body    = [
            'status' => $deleted === 1
        ];

        $statusCode = $body['status'] ? 200 : 404;

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('statusCode', $statusCode)
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }
}
