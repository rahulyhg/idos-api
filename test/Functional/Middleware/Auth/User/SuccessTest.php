<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Functional\Middleware\Auth\User;

use App\Middleware\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Test\Functional\Middleware\Auth\AbstractAuthFunctional;

class SuccessTest extends AbstractAuthFunctional {
    protected function setUp() {
        $this->middlewareApp = parent::getApp();
        $this->uri           = '/testSuccess';
        $this->httpMethod    = 'GET';
    }

    public function testSuccess() {
        $token = $this->userToken();

        $authMiddleware = $this->middlewareApp
            ->getContainer()
            ->get('authMiddleware');
        $this->middlewareApp
            ->get(
                '/testSuccess', function (ServerRequestInterface $request, ResponseInterface $response) {
                    $user = $request->getAttribute('user');
                    $company = $request->getAttribute('company');
                    $credential = $request->getAttribute('credential');

                    $data = [
                        'user'       => $user->serialize(),
                        'company'    => $company->serialize(),
                        'credential' => $credential->serialize()
                    ];

                    return $response->withJson($data, 200);
                }
            )
            ->add($authMiddleware(Auth::USER));

        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'QUERY_STRING' => 'userToken=' . $token
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertSame('f67b96dcf96b49d713a520ce9f54053c', $body['user']['username']);
        $this->assertSame($body['credential']['id'], $body['user']['credential_id']);
        $this->assertSame(md5('public'), $body['credential']['public']);
        $this->assertNotEmpty($body['credential']['private']);
        $this->assertSame($body['company']['id'], $body['credential']['company_id']);
    }
}
