<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Validator;

/**
 * Setting Validation Rules.
 */
class Setting implements ValidatorInterface {
    use Traits\AssertId,
        Traits\AssertName;
}
