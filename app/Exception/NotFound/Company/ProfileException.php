<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotFound\Company;

use App\Exception\NotFound;

/**
 * CompanyProfile not found exception.
 *
 * @apiEndpointResponse 404 schema/error.json
 */
class ProfileException extends NotFound {
}
