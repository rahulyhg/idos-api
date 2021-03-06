<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Company;

use App\Entity\Company\Permission;
use App\Repository\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Permission Repository Interface.
 */
interface PermissionInterface extends RepositoryInterface {
    /**
     * Return a permission based on its route name and company id.
     *
     * @param int    $companyId
     * @param string $routeName
     *
     * @return \App\Entity\Company\Permission
     */
    public function findOne(int $companyId, string $routeName) : Permission;

    /**
     * Return permissions based on their company id.
     *
     * @param int $companyId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getByCompanyId(int $companyId) : Collection;

    /**
     * Delete a permission based on its route name and company id.
     *
     * @param int    $companyId
     * @param string $routeName
     *
     * @return int
     */
    public function deleteOne(int $companyId, string $routeName) : int;

    /**
     * Delete permissions based on their company id.
     *
     * @param int $companyId
     *
     * @return int
     */
    public function deleteByCompanyId(int $companyId) : int;
}
