<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\Update\User;

use App\Exception\AppException;

/**
 * RoleAccess update exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class RoleAccessException extends AppException {
}
