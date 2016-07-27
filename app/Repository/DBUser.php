<?php

/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Repository\DBCredential;
use App\Exception\NotFound;

/**
 * Database-based User Repository Implementation.
 */
class DBUser extends AbstractDBRepository implements UserInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'users';
    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'User';

    /**
     * {@inheritdoc}
     */
    public function findByUserName($userName, $credentialId) {
        $result = $this->query()
            ->where('username', $userName)
            ->where('credential_id', $credentialId)
            ->first();
        if (empty($result))
            throw new NotFound();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findByPrivKey(string $privateKey) {
        $result = $this->query()
            ->selectRaw('users.*')
            ->join('credentials', 'users.credential_id', '=', 'credentials.id')
            ->where('credentials.private', '=', $privateKey)
            ->first();


        if (empty($result))
            throw new NotFound();

        return $result;
    }
}
