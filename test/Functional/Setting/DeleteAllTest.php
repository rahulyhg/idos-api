<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Functional\Setting;

use Slim\Http\Response;
use Slim\Http\Uri;
use Test\Functional\AbstractFunctional;
use Test\Functional\Traits\HasAuthCompanyToken;
use Test\Functional\Traits\HasAuthMiddleware;

class DeleteAllTest extends AbstractFunctional {
    use HasAuthMiddleware;
    use HasAuthCompanyToken;

    protected function setUp() {
        $this->httpMethod = 'DELETE';
        $this->uri        = '/1.0/management/settings?perPage=900';
        $this->populate(
            $this->uri,
            'GET',
            [
                'HTTP_AUTHORIZATION' => $this->companyTokenHeader()
            ]
        );
    }

    public function testSuccess() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->companyTokenHeader()
                ]
            )
        );
        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        // refreshes the $entities prop
        $this->populate($this->uri);
        // checks if all entities were deleted
        $this->assertSame(0, count($this->entities));

        /*
         * Validates Json Schema with Json Response
         */
        $this->assertTrue(
            $this->validateSchema(
                'setting/deleteAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }
}
