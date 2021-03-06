<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

define('__VERSION__', '1.0');
define('__ROOT__', __DIR__ . '/..');
define('__RSRC__', __DIR__ . '/Resources');

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF8');
mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');

/**
 * Composer's autoload.
 */
require_once __ROOT__ . '/vendor/autoload.php';

// Loads .env if available
if (is_file(__ROOT__ . '/.env.testing')) {
    $dotEnv = new Dotenv\Dotenv(__ROOT__, '.env.testing');
    $dotEnv->overload();
}

// Load application settings
require_once __ROOT__ . '/config/settings.php';
