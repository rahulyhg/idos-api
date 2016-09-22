<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller\Profile;

use App\Controller\ControllerInterface;
use App\Entity\User;
use App\Factory\Command;
use App\Repository\Profile\RawInterface;
use App\Repository\Profile\SourceInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /profiles/{userName}/sources/{sourceId}/raw.
 */
class Raw implements ControllerInterface {
    /**
     * Raw Repository instance.
     *
     * @var App\Repository\Profile\RawInterface
     */
    private $repository;
    /**
     * Source Repository instance.
     *
     * @var App\Repository\Profile\SourceInterface
     */
    private $sourceRepository;
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
     * @param App\Repository\Profile\RawInterface    $repository
     * @param App\Repository\Profile\SourceInterface $sourceRepository
     * @param \League\Tactician\CommandBus           $commandBus
     * @param App\Factory\Command                    $commandFactory
     *
     * @return void
     */
    public function __construct(
        RawInterface $repository,
        SourceInterface $sourceRepository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository       = $repository;
        $this->sourceRepository = $sourceRepository;
        $this->commandBus       = $commandBus;
        $this->commandFactory   = $commandFactory;
    }

    /**
     * Retrieve a complete list of the raw data by a given source.
     *
     * @apiEndpointParam       query  string   collections  collection1,collection2
     * @apiEndpointResponse 200 schema/raw/listAll.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Repository\DBService::getAllBySourceAndCollections
     *
     * @throws App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $user        = $request->getAttribute('targetUser');
        $service     = $request->getAttribute('service');
        $queryParams = $request->getQueryParams();

        $entities = $this->repository->findByUserId($user->id, $queryParams);

        $body = [
            'data'    => $entities->toArray(),
            'updated' => (
                $entities->isEmpty() ? time() : max($entities->max('updatedAt'), $entities->max('createdAt'))
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
     * Created a new raw data for a given source.
     *
     * @apiEndpointResponse 201 schema/raw/rawEntity.json
     * @apiEndpointRequiredParam body string collection collection-name Collection name
     * @apiEndpointRequiredParam body string data data-value Data
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Handler\Raw::handleCreateNew
     *
     * @throws App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNew(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $command = $this->commandFactory->create('Profile\\Raw\\CreateNew');

        $user     = $request->getAttribute('targetUser');
        $service  = $request->getAttribute('service');
        $sourceId = (int) $request->getParsedBodyParam('decoded_source_id');

        $source = $this->sourceRepository->findOne($sourceId, $user->id);

        $command
            ->setParameters($request->getParsedBody())
            ->setParameter('user', $user)
            ->setParameter('service', $service)
            ->setParameter('source', $source);

        $raw = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $raw->toArray()
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
     * Updates a raw data from the given source.
     *
     * @apiEndpointRequiredParam body   string       data        1
     * @apiEndpointResponse 200 schema/raw/updateOne.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Handler\Raw::handleUpdateOne
     *
     * @throws App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $user     = $request->getAttribute('targetUser');
        $service  = $request->getAttribute('service');
        $sourceId = (int) $request->getAttribute('decodedSourceId');

        $source = $this->sourceRepository->findOne($sourceId, $user->id);

        $command = $this->commandFactory->create('Profile\\Raw\\UpdateOne');
        $command
            ->setParameters($request->getParsedBody())
            ->setParameter('user', $user)
            ->setParameter('service', $service)
            ->setParameter('source', $source);

        $raw = $this->commandBus->handle($command);

        $body = [
            'data'    => $raw->toArray(),
            'updated' => $raw->updated_at
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Creates or updates a raw data for a given source.
     *
     * @apiEndpointResponse 201 schema/raw/rawEntity.json
     * @apiEndpointRequiredParam body string collection collection-name Collection name
     * @apiEndpointRequiredParam body string data data-value Data
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Handler\Raw::handleCreateNew
     *
     * @throws App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upsert(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $command = $this->commandFactory->create('Profile\\Raw\\Upsert');

        $user     = $request->getAttribute('targetUser');
        $service  = $request->getAttribute('service');
        $sourceId = (int) $request->getParsedBodyParam('decoded_source_id');

        $source = $this->sourceRepository->findOne($sourceId, $user->id);

        $command
            ->setParameters($request->getParsedBody())
            ->setParameter('user', $user)
            ->setParameter('service', $service)
            ->setParameter('source', $source);

        $entity = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $entity->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('statusCode', isset($entity->updatedAt) ? 200 : 201)
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Retrieves a raw data from the given source.
     *
     * @apiEndpointURIFragment string collection collectionName
     * @apiEndpointResponse 200 schema/raw/rawEntity.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $user   = $request->getAttribute('targetUser');
        $source = $this->sourceRepository->findOne((int) $request->getAttribute('decodedSourceId'), $user->id);

        if ($source->userId !== $user->id) {
            throw new AppException('Source not found');
        }

        $raw = $this->repository->findOneBySourceAndCollection($source, $request->getAttribute('collection'));

        $body = [
            'data' => $raw->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes all raw data from a given source.
     *
     * @apiEndpointResponse 200 schema/raw/deleteAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Handler\Raw::handlerDeleteAll
     * @see App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $command = $this->commandFactory->create('Profile\\Raw\\DeleteAll');

        $user   = $request->getAttribute('targetUser');
        $source = $this->sourceRepository->findOne((int) $request->getAttribute('decodedSourceId'), $user->id);

        if ($source->userId !== $user->id) {
            throw new AppException('Source not found');
        }

        $command
            ->setParameter('user', $user)
            ->setParameter('source', $source);

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
     * Deletes a raw data from a given source.
     *
     * @apiEndpointResponse    200    schema/raw/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Repository\DBSource::findOne
     * @see App\Handler\Raw::handleDeleteOne
     *
     * @throws App\Exception\AppException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $command = $this->commandFactory->create('Profile\\Raw\\DeleteOne');

        $user   = $request->getAttribute('targetUser');
        $source = $this->sourceRepository->findOne((int) $request->getAttribute('decodedSourceId'), $user->id);

        if ($source->userId !== $user->id) {
            throw new AppException('Source not found');
        }

        $command
            ->setParameter('user', $user)
            ->setParameter('source', $source)
            ->setParameter('collection', $request->getAttribute('collection'));

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
}