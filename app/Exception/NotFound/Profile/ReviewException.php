<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotFound\Profile;

use App\Exception\NotFound;

/**
 * Review not found exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class ReviewException extends NotFound {
}
