<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Whitelist Middleware.
 *
 * Scope: Application.
 * IP-based (whitelist) access control.
 *
 * FIXME Must be properly implemented! Remove Container injection!
 */
class Whitelist implements MiddlewareInterface {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param callable                                 $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) : ResponseInterface {
        // Token based requests aren't subject to whitelisting
        if (! empty($request->getAttribute('token'))) {
            return $next($request, $response);
        }

        // Retrieves Key data from request
        $key = $request->getAttribute('key');

        // Development credentials aren't subject to whitelisting
        if (! $key->isProduction()) {
            return $next($request, $response);
        }

        // Loads whitelist data based on Client Id
        $repositoryFactory   = $this->container->get('repositoryFactory');
        $whitelistRepository = $repositoryFactory('WhitelistCache');
        $whitelist           = $whitelistRepository->getAll($key->getClientId());

        // Checks request ip address
        if ($whitelist->isListed($request->getIp())) {
            return $next($request, $response);
        }

        throw new \Exception(
            sprintf(
                'AccessDenied - You IP Address (%s) has not been whitelisted.',
                $request->getIp()
            )
        );
    }
}
