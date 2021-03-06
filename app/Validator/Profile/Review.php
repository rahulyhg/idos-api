<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Validator\Profile;

use App\Validator\Traits;
use App\Validator\ValidatorInterface;

/**
 * Review Validation Rules.
 */
class Review implements ValidatorInterface {
    use Traits\AssertEntity,
        Traits\AssertId,
        Traits\AssertFlag,
        Traits\AssertName,
        Traits\ValidateFlag;
}
