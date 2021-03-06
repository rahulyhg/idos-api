<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception;

/**
 * Model Not Allowed Exception.
 *
 * @apiEndpointResponse 404 schema/error.json
 *
 * @see \App\Exception\AppException
 */
class NotAllowed extends AppException {
    /**
     * {@inheritdoc}
     */
    protected $code = 403;
    /**
     * {@inheritdoc}
     */
    protected $message = 'Not Allowed.';
}
