<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Functional\Attribute;

use Test\Functional\AbstractFunctional;
use Test\Functional\Traits;

class ListAllTest extends AbstractFunctional {
    use Traits\RequiresAuth,
        Traits\RequiresCredentialToken,
        Traits\RejectsUserToken,
        Traits\RejectsIdentityToken;

    protected function setUp() {
        parent::setUp();

        $this->httpMethod = 'GET';
        $this->uri        = '/1.0/profiles/f67b96dcf96b49d713a520ce9f54053c/attributes';
    }

    public function testSuccess() {
        $environment = $this->createEnvironment(
            [
                'HTTP_AUTHORIZATION' => $this->credentialTokenHeader()
            ]
        );

        $request  = $this->createRequest($environment);
        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'attribute/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testNameFilter() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'name=first*'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(0, $body['data']);

        foreach ($body['data'] as $attribute) {
            $this->assertContains($attribute['name'], ['firstname']);
            $this->assertContains($attribute['value'], ['John']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'attribute/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testNameFilterMultiple() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'name=*name'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(2, $body['data']);

        foreach ($body['data'] as $attribute) {
            $this->assertContains($attribute['name'], ['firstname', 'lastname']);
            $this->assertContains($attribute['value'], ['John', 'Doe']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'attribute/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testCreatorNameFilter() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'creator:name=idOS FB Scraper'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(2, $body['data']);

        foreach ($body['data'] as $attribute) {
            $this->assertContains($attribute['name'], ['firstname', 'lastname']);
            $this->assertContains($attribute['value'], ['John', 'Doe']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'attribute/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testCreatorNameFilterMultiple() {
        $request = $this->createRequest(
            $this->createEnvironment(
                [
                    'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                    'QUERY_STRING'       => 'creator:name=id*'
                ]
            )
        );

        $response = $this->process($request);
        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $this->assertTrue($body['status']);
        $this->assertCount(2, $body['data']);

        foreach ($body['data'] as $attribute) {
            $this->assertContains($attribute['name'], ['firstname', 'lastname']);
            $this->assertContains($attribute['value'], ['John', 'Doe']);
        }

        /*
         * Validates Response using the Json Schema.
         */
        $this->assertTrue(
            $this->validateSchema(
                'attribute/listAll.json',
                json_decode((string) $response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testOrdering() {
        $orderableKeys = [
            'name',
            'support',
            'created_at',
            'updated_at'
        ];

        foreach (['ASC', 'DESC'] as $sort) {
            foreach ($orderableKeys as $orderableKey) {
                $request = $this->createRequest(
                    $this->createEnvironment(
                        [
                            'HTTP_AUTHORIZATION' => $this->credentialTokenHeader(),
                            'QUERY_STRING'       => 'filter:order=' . $orderableKey . '&filter:sort=' . $sort
                        ]
                    )
                );

                $response = $this->process($request);
                $this->assertSame(200, $response->getStatusCode());

                $body = json_decode((string) $response->getBody(), true);
                $this->assertNotEmpty($body);
                $this->assertTrue($body['status']);
                $this->assertCount(2, $body['data']);

                $keys = [];

                if (strpos(':', $orderableKey) !== false) {
                    $orderableKey = explode(':', $orderableKey);
                } else {
                    foreach ($body['data'] as $entity) {
                        $keys[] = isset($entity[$orderableKey]) ? $entity[$orderableKey] : null;
                    }

                    $orderedKeys = $keys;

                    if ($sort === 'ASC') {
                        sort($orderedKeys);
                    } else {
                        rsort($orderedKeys);
                    }

                    if ($orderedKeys !== $keys) {
                        $this->fail('Failed asserting correctly ordered elements (' . $orderableKey . ', ' . $sort . ')');
                    }
                }

                /*
                 * Validates Response using the Json Schema.
                 */
                $this->assertTrue(
                    $this->validateSchema(
                        'attribute/listAll.json',
                        json_decode((string) $response->getBody())
                    ),
                    $this->schemaErrors
                );
            }
        }
    }
}
