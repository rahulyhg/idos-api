<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository;

use App\Factory\Repository;

/**
 * Repository Strategy Interface.
 */
interface RepositoryStrategyInterface {
    /**
     * Gets the repository's formatted name.
     *
     * @param string $repositoryName
     *
     * @return string
     */
    public function getFormattedName(string $repositoryName) : string;

    /**
     * Builds a new repository.
     *
     * @param \App\Factory\Repository $repositoryFactory
     * @param string                  $className
     *
     * @return \App\Repository\RepositoryInterface
     */
    public function build(Repository $repositoryFactory, string $className) : RepositoryInterface;
}
