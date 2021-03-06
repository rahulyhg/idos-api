<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Company;

use App\Entity\Company\Credential;
use App\Repository\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Credential Repository Interface.
 */
interface CredentialInterface extends RepositoryInterface {
    /**
     * Finds one credential with the given company id and public key.
     *
     * @param int    $companyId The company identifier
     * @param string $key       The key
     *
     * @return \App\Entity\Company\Credential
     */
    public function findOneByCompanyIdAndPubKey(int $companyId, string $key) : Credential;

    /**
     * Finds a Credential based on its Public Key.
     *
     * @param string $pubKey
     *
     * @throws \App\Exception\NotFound
     *
     * @return \App\Entity\Company\Credential
     */
    public function findByPubKey(string $pubKey) : Credential;

    /**
     * Gets all Credentials based on their Company Id.
     *
     * @param int $companyId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getByCompanyId(int $companyId) : Collection;

    /**
     * Deletes all Credentials based on their Company Id.
     *
     * @param int $companyId
     *
     * @return int
     */
    public function deleteByCompanyId(int $companyId) : int;

    /**
     * Deletes a set of credentials with the given public key.
     *
     * @param string $key The key
     *
     * @return int the amount of deleted credentials
     */
    public function deleteByPubKey(string $key) : int;
}
