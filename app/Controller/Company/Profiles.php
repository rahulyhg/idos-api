<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller\Company;

use App\Controller\ControllerInterface;
use App\Exception\NotFound;
use App\Factory\Command;
use App\Repository\RepositoryInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /companies/{companySlug}/profiles/ and /companies/{companySlug}/profiles/{userId}.
 */
class Profiles implements ControllerInterface {
    /**
     * UserRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $repository;
    /**
     * SourceRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $sourceRepository;
    /**
     * TagRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $tagRepository;
    /**
     * ReviewRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $reviewRepository;
    /**
     * FlagRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $flagRepository;
    /**
     * GateRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $gateRepository;
    /**
     * AttributeRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $attributeRepository;
    /**
     * RecommendationRepository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $recommendationRepository;
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
     * @param \App\Repository\RepositoryInterface $sourceRepository
     * @param \App\Repository\RepositoryInterface $tagRepository
     * @param \App\Repository\RepositoryInterface $reviewRepository
     * @param \App\Repository\RepositoryInterface $flagRepository
     * @param \App\Repository\RepositoryInterface $gateRepository
     * @param \App\Repository\RepositoryInterface $attributeRepository
     * @param \App\Repository\RepositoryInterface $recommendationRepository
     * @param \League\Tactician\CommandBus        $commandBus
     * @param \App\Factory\Command                $commandFactory
     *
     * @return void
     */
    public function __construct(
        RepositoryInterface $repository,
        RepositoryInterface $sourceRepository,
        RepositoryInterface $tagRepository,
        RepositoryInterface $reviewRepository,
        RepositoryInterface $flagRepository,
        RepositoryInterface $gateRepository,
        RepositoryInterface $attributeRepository,
        RepositoryInterface $recommendationRepository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository                = $repository;
        $this->sourceRepository          = $sourceRepository;
        $this->tagRepository             = $tagRepository;
        $this->reviewRepository          = $reviewRepository;
        $this->flagRepository            = $flagRepository;
        $this->gateRepository            = $gateRepository;
        $this->attributeRepository       = $attributeRepository;
        $this->recommendationRepository  = $recommendationRepository;
        $this->commandBus                = $commandBus;
        $this->commandFactory            = $commandFactory;
    }

    /**
     * List all Profiles that belongs to the target Company.
     *
     * @apiEndpointResponse 200 schema/companyProfile/listAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company = $request->getAttribute('targetCompany');

        $data     = [];
        $profiles = $this->repository->findByCompanyId($company->id);

        foreach ($profiles as $profile) {
            $sources        = $this->sourceRepository->getByUserId($profile->id);
            $tags           = $this->tagRepository->getByUserId($profile->id);
            $flags          = $this->flagRepository->getByUserId($profile->id);
            $gates          = $this->gateRepository->getByUserId($profile->id);
            $attributes     = $this->attributeRepository->findByUserId($profile->id);

            try {
                $recommendation = $this->recommendationRepository->findOne($profile->id)->toArray();
            } catch (NotFound $exception) {
                $recommendation = null;
            }

            $data[] = array_merge(
                $profile->toArray(),
                ['sources'        => $sources->toArray()],
                ['tags'           => $tags->toArray()],
                ['flags'          => $flags->toArray()],
                ['gates'          => $gates->toArray()],
                ['attributes'     => $attributes->toArray()],
                ['recommendation' => $recommendation]
            );
        }

        $body = [
            'data'    => $data,
            'updated' => (
                $profiles->isEmpty() ? null : max($profiles->max('updatedAt'), $profiles->max('createdAt'))
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
     * Retrieves the user given by userId.
     *
     * @apiEndpointResponse 200 schema/companyProfile/getOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @throws \App\Exception\NotFound\Company\ProfileException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $userId = $request->getAttribute('decodedUserId');

        $data = [];

        $profile = $this->repository->find($userId);

        $attributes       = $this->attributeRepository->findByUserId($profile->id);
        $sources          = $this->sourceRepository->getByUserId($profile->id);
        $tags             = $this->tagRepository->getByUserId($profile->id);
        $reviews          = $this->reviewRepository->getByUserId($profile->id);
        $flags            = $this->flagRepository->getByUserId($profile->id);
        $gates            = $this->gateRepository->getByUserId($profile->id);

        try {
            $recommendation = $this->recommendationRepository->findOne($profile->id)->toArray();
        } catch (NotFound $exception) {
            $recommendation = null;
        }

        foreach ($gates as $gate) {
            $gateReview = null;
            foreach ($reviews as $review) {
                if ($review->gateId === $gate->id) {
                    $gateReview = $review->toArray();
                    break;
                }
            }

            $gate->review = $gateReview;
        }

        $data = array_merge(
            $profile->toArray(),
            ['attributes'     => $attributes->toArray()],
            ['sources'        => $sources->toArray()],
            ['tags'           => $tags->toArray()],
            ['flags'          => $flags->toArray()],
            ['gates'          => $gates->toArray()],
            ['recommendation' => $recommendation]
        );

        $body = [
            'data' => $data
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes the target company profile.
     *
     * @apiEndpointResponse 200 schema/companyProfile/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @throws \App\Exception\NotFound\Company\ProfileException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $userId   = $request->getAttribute('decodedUserId');
        $identity = $request->getAttribute('identity');

        $command = $this->commandFactory->create('Company\Profile\DeleteOne');
        $command
            ->setParameter('identity', $identity)
            ->setParameter('userId', $userId);

        $deleted = $this->commandBus->handle($command);

        $body = [
            'deleted' => $deleted
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }
}
