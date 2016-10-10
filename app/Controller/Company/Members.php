<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller\Company;

use App\Controller\ControllerInterface;
use App\Entity\User;
use App\Factory\Command;
use App\Repository\Company\MemberInterface;
use App\Repository\Company\InvitationInterface;
use App\Repository\UserInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /companies/{company-slug}/members.
 */
class Members implements ControllerInterface {
    /**
     * Member Repository instance.
     *
     * @var App\Repository\Company\MemberInterface
     */
    private $repository;
    /**
     * User Repository instance.
     *
     * @var App\Repository\UserInterface
     */
    private $userRepository;
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
     * @param App\Repository\Company\MemberInterface $repository
     * @param App\Repository\UserInterface           $userRepository
     * @param App\Repository\InvitationInterface         $invitationRepository
     * @param \League\Tactician\CommandBus           $commandBus
     * @param App\Factory\Command                    $commandFactory
     *
     * @return void
     */
    public function __construct(
        MemberInterface $repository,
        InvitationInterface $invitationRepository,
        UserInterface $userRepository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository     = $repository;
        $this->invitationRepository = $invitationRepository;
        $this->userRepository = $userRepository;
        $this->commandBus     = $commandBus;
        $this->commandFactory = $commandFactory;
    }

    /**
     * Lists all Members that belongs to the Target Company.
     *
     * @apiEndpointResponse 200 schema/member/listAll.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company = $request->getAttribute('targetCompany');
        $members = $this->repository->getAllByCompanyId($company->id, $request->getQueryParams());

        $body = [
            'data'    => $members->toArray(),
            'updated' => (
                $members->isEmpty() ? time() : max($members->max('updatedAt'), $members->max('createdAt'))
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
     * Retrieve the membership of the related company and identity.
     *
     * @apiEndpointResponse 200 schema/member/getMembership.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getMembership(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company = $request->getAttribute('targetCompany');
        $identity = $request->getAttribute('identity');

        $member = $this->repository->findMembership($identity->id, $company->id);

        $body = [
            'data'    => $member->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Lists all Members Invitations that belongs to the Target Company.
     *
     * @apiEndpointResponse 200 schema/member/listAll.json
     *
     * @param \Psr\ServerRequestInterface $request
     * @param \Psr\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getInvitations(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company = $request->getAttribute('targetCompany');
        $invitations = $this->invitationRepository->getAllByCompanyId($company->id, $request->getQueryParams());

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
     * Creates a new Member for the Target Company.
     *
     * @apiEndpointRequiredParam body string role company:owner Role type
     * @apiEndpointRequiredParam body string userName jhondoe UserName
     * @apiEndpointResponse 201 schema/member/createNewInvitation.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Handler\Member::handleCreateNew
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNewInvitation(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $targetCompany = $request->getAttribute('targetCompany');
        $identity = $request->getAttribute('identity');

        $command = $this->commandFactory->create('Company\\Member\\CreateNewInvitation');
        $command
            ->setParameter('company', $targetCompany)
            ->setParameter('identity', $identity)
            ->setParameter('ipaddr', $request->getAttribute('ip_address'))
            ->setParameters($request->getParsedBody());

        $member = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $member->toArray()
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
     * Retrieves one Member of the Target Company based on the userName.
     *
     * @apiEndpointResponse 200 schema/member/memberEntity.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Repository\DBMember::findOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $member = $this->repository->findOne($request->getAttribute('decodedMemberId'));

        $body = [
            'data' => $member->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes one Member of the Target Company based on the userId.
     *
     * @apiEndpointResponse 200 schema/member/deleteInvitation.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see App\Handler\Member::handleDeleteOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteInvitation(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $command = $this->commandFactory->create('Company\\Member\\DeleteInvitation');
        $command->setParameter('invitationId', $request->getAttribute('decodedInvitationId'));

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
