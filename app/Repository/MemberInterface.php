<?php

/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Member as MemberEntity;
use Illuminate\Support\Collection;

/**
 * Member Repository Interface.
 */
interface MemberInterface extends RepositoryInterface {

    /**
     * Gets all Members based on their Company Id.
     *
     * @param int $companyId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByCompanyId($companyId) : Collection;
    /**
     * Gets all Members basedon their Company Id and role.
     *
     * @param int    companyId member's company_id
     * @param string role  member's role
     *
     * @return Illuminate\Support\Collection
     */
    public function getAllByCompanyIdAndRole($companyId, $role) : Collection;
    /*
     * Deletes all Members based on their Company Id.
     *
     * @param int $companyId
     *
     * @return int
     */
    public function deleteByCompanyId($companyId) : int;
     /**
     * Find one member based on their companyId and username.
     *
     * @param int    $companyId
     * @param string $username
     *
     * @return App\Entity\Member
     */
    public function findOne($companyId, $username) : MemberEntity;
     /**
     * Deletes one member from company.
     *
     * @param int    companyId member's company_id
     * @param string username   member's username
     *
     * @return int
     */
    public function deleteOne(int $companyId, string $username) : int;
}
