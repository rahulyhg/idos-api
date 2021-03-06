<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Company;

use App\Entity\Company\Credential;
use App\Repository\AbstractDBRepository;
use Illuminate\Support\Collection;

/**
 * Database-based Credential Repository Implementation.
 */
class DBCredential extends AbstractDBRepository implements CredentialInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'credentials';
    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'Company\Credential';

    /**
     * {@inheritdoc}
     */
    public function findByPubKey(string $key) : Credential {
        return $this->findOneBy(['public' => $key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getByCompanyId(int $companyId) : Collection {
        $collection = $this->findBy(
            [
            'company_id' => $companyId
            ]
        );

        $subscriptionRepository = $this->repositoryFactory->create('Company\Subscription');

        return $collection->map(
            function ($credential) use ($subscriptionRepository) {
                $credential->subscriptions = $subscriptionRepository->getByCredentialId($credential->id);

                return $credential;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByCompanyId(int $companyId) : int {
        return $this->deleteByKey('company_id', $companyId);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCompanyId(int $companyId) : Collection {
        return $this->findBy(['company_id' => $companyId]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByPubKey(string $key) : int {
        return $this->deleteByKey('public', $key);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByCompanyIdAndPubKey(int $companyId, string $key) : Credential {
        return $this->findOneBy(['company_id' => $companyId, 'public' => $key]);
    }
}
