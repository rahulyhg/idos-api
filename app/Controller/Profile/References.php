<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller\Profile;

use App\Controller\ControllerInterface;
use App\Factory\Command;
use App\Repository\RepositoryInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /profiles/{userName}/reference and /profiles/{userName}/references/{referenceName}.
 */
class References implements ControllerInterface {
    /**
     * Reference Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $repository;
    /**
     * Command Bus instance.
     *
     * @var \League\Tactician\CommandBus
     */
    private $commandBus;
    /**
     * Command Factory instance.
     *
     * @var \App\Factory\Command
     */
    private $commandFactory;

    /**
     * Class constructor.
     *
     * @param \App\Repository\RepositoryInterface $repository
     * @param \League\Tactician\CommandBus        $commandBus
     * @param \App\Factory\Command                $commandFactory
     *
     * @return void
     */
    public function __construct(
        RepositoryInterface $repository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository     = $repository;
        $this->commandBus     = $commandBus;
        $this->commandFactory = $commandFactory;
    }

    /**
     * Retrieve a complete list of the data reference by a given source.
     *
     * @apiEndpointParam query string names firstName,middleName,lastName
     * @apiEndpointResponse 200 schema/reference/listAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Repository\DBReference::getAllByUserIdAndNames
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $user = $request->getAttribute('targetUser');

        $references = $this->repository->getAllByUserId($user->id, $request->getQueryParams());

        $body = [
            'data'    => $references->toArray(),
            'updated' => (
                $references->isEmpty() ? time() : max($references->max('updatedAt'), $references->max('createdAt'))
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
     * Retrieves a reference data from the given source.
     *
     * @apiEndpointResponse 200 schema/reference/referenceEntity.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Repository\DBReference::findOneByUserIdAndName
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $user = $request->getAttribute('targetUser');
        $name = $request->getAttribute('referenceName');

        $reference = $this->repository->findOne($name, $user->id);

        $body = [
            'data' => $reference->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Created a new reference data for a given source.
     *
     * @apiEndpointRequiredParam body string name reference-name Reference name
     * @apiEndpointRequiredParam body string value reference-value Reference value
     * @apiEndpointResponse 201 schema/reference/createNew.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Profile\Reference::handleCreateNew
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNew(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $credential = $request->getAttribute('credential');

        $command = $this->commandFactory->create('Profile\Reference\CreateNew');
        $command
            ->setParameters($request->getParsedBody() ?: [])
            ->setParameter('credential', $credential)
            ->setParameter('user', $request->getAttribute('targetUser'))
            ->setParameter('ipaddr', $request->getAttribute('ip_address'));

        $reference = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $reference->toArray()
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
     * Updates a reference data from the given source.
     *
     * @apiEndpointRequiredParam body string value reference-value Reference value
     * @apiEndpointResponse 200 schema/reference/updateOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Profile\Reference::handleUpdateOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $credential = $request->getAttribute('credential');

        $command = $this->commandFactory->create('Profile\Reference\UpdateOne');
        $command
            ->setParameters($request->getParsedBody() ?: [])
            ->setParameter('credential', $credential)
            ->setParameter('user', $request->getAttribute('targetUser'))
            ->setParameter('name', $request->getAttribute('referenceName'));

        $reference = $this->commandBus->handle($command);

        $body = [
            'data'    => $reference->toArray(),
            'updated' => $reference->updated_at
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes a reference data from a given source.
     *
     * @apiEndpointResponse 200 schema/reference/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Profile\Reference::handleDeleteOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $credential = $request->getAttribute('credential');

        $command = $this->commandFactory->create('Profile\Reference\DeleteOne');
        $command
            ->setParameter('credential', $credential)
            ->setParameter('user', $request->getAttribute('targetUser'))
            ->setParameter('name', $request->getAttribute('referenceName'));

        $this->commandBus->handle($command);
        $body = [
            'status' => true
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes all reference data from a given source.
     *
     * @apiEndpointResponse 200 schema/reference/deleteAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Profile\Reference::handleDeleteAll
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $credential = $request->getAttribute('credential');

        $command = $this->commandFactory->create('Profile\Reference\DeleteAll');
        $command
            ->setParameter('credential', $credential)
            ->setParameter('user', $request->getAttribute('targetUser'));

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
}
