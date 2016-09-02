<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Functional\Warning;

use Slim\Http\Response;
use Slim\Http\Uri;
use Test\Functional\AbstractFunctional;
use Test\Functional\Traits\RequiresAuth;
use Test\Functional\Traits\RequiresCredentialToken;

class CreateNewTest extends AbstractFunctional {
    use RequiresAuth;
    use RequiresCredentialToken;

    protected function setUp() {
        $this->httpMethod = 'POST';
        $this->uri        = '/1.0/profiles/f67b96dcf96b49d713a520ce9f54053c/warnings';
    }

    public function testSuccess() {
        $environment = $this->createEnvironment(
            [
                'HTTP_CONTENT_TYPE'  => 'application/json',
                'HTTP_AUTHORIZATION' => $this->credentialTokenHeader()
            ]
        );

        $name    = 'Testing';
        $value   = 'testing';
        $request = $this->createRequest(
            $environment, json_encode(
                [
                    'name' => $name,
                ]
            )
        );
        $response = $this->process($request);
        $body     = json_decode((string) $response->getBody(), true);

        $this->assertNotEmpty($body);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($body['status']);
        $this->assertSame($name, $body['data']['name']);
        /*
         * Validates Json Schema against Json Response'
         */
        $this->assertTrue(
            $this->validateSchema('warning/createNew.json', json_decode((string) $response->getBody())),
            $this->schemaErrors
        );
    }
}
