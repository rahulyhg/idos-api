<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller\Company;

use App\Controller\ControllerInterface;
use App\Factory\Command;
use App\Repository\RepositoryInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /companies/{companySlug}/invitations and /companies/{companySlug}/invitations/{invitationId}.
 */
class Invitations implements ControllerInterface {
    /**
     * Invitation Repository instance.
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
        $this->repository           = $repository;
        $this->commandBus           = $commandBus;
        $this->commandFactory       = $commandFactory;
    }

    /**
     * Lists all Invitations that belongs to the Target Company.
     *
     * @apiEndpointResponse 200 schema/invitation/listAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company     = $request->getAttribute('targetCompany');
        $invitations = $this->repository->getAllByCompanyId($company->id);

        $body = [
            'data'    => $invitations->toArray(),
            'updated' => (
                $invitations->isEmpty() ? time() : max($invitations->max('updatedAt'), $invitations->max('createdAt'))
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
     * Creates a new Invitation for the Target Company.
     *
     * @apiEndpointRequiredParam body string role company.owner Role type
     * @apiEndpointRequiredParam body string email jhondoe@idos.io User's email
     * @apiEndpointRequiredParam body string name jhon User's name
     * @apiEndpointRequiredParam body string credentialPubKey wqxehuwqwsthwosjbxwwsqwsdi A valid credential public key
     * @apiEndpointParam body string expires 2016-11-23 Expiration date (if no expiration date is passed, the invitation will expire in one day)
     * @apiEndpointResponse 201 schema/invitation/createNew.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Invitation::createNew
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNew(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $targetCompany = $request->getAttribute('targetCompany');
        $identity      = $request->getAttribute('identity');
        $command       = $this->commandFactory->create('Company\Invitation\CreateNew');

        $command
            ->setParameter('company', $targetCompany)
            ->setParameter('identity', $identity)
            ->setParameter('ipaddr', $request->getAttribute('ip_address'))
            ->setParameters($request->getParsedBody());

        $invitation = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $invitation->toArray()
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
     * Deletes one Invitation of the Target Company based on the invitation id.
     *
     * @apiEndpointResponse 200 schema/invitation/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Invitation::handleDeleteOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $identity = $request->getAttribute('identity');

        $command = $this->commandFactory->create('Company\Invitation\DeleteOne');
        $command
            ->setParameter('identity', $identity)
            ->setParameter('invitationId', $request->getAttribute('decodedInvitationId'));

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
     * Updates one Invitation of the Target Company based on the invitation id.
     *
     * @apiEndpointResponse 200 schema/invitation/updateOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\Invitation::handleUpdateOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $command = $this->commandFactory->create('Company\Invitation\UpdateOne');

        $command->setParameter('invitationId', $request->getAttribute('decodedInvitationId'));
        $command->setParameters($request->getParsedBody());

        $entity = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $entity->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }
}
