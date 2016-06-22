<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Repository;

use Stash\Invalidation;

/**
 * Cache-based Credential Repository Implementation.
 */
class CachedCredential extends AbstractCachedRepository implements CredentialInterface {
    /**
     * {@inheritDoc}
     */
    public function find($id) {
        return $this->repository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id) {
        return $this->repository->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() {
        return $this->repository->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function findByPubKey($pubKey) {
        return $this->repository->findByPubKey($pubKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllByCompanyId($companyId) {
        return $this->getAllByCompanyId($companyId);
    }
}
