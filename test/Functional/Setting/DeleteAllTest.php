<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Functional\Setting;

use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;
use Test\Functional\AbstractFunctional;
use Test\Functional\Traits\HasAuthMiddleware;

class DeleteAllTest extends AbstractFunctional {
	use HasAuthMiddleware;
	
	protected function setUp() {
        $this->httpMethod = 'DELETE';
        $this->uri        = '/1.0/companies/veridu-ltd/settings';
		$this->populate($this->uri);
    }

    public function testSuccess() {
    	$request    = $this->createRequest($this->createEnvironment());
        $response   = $this->process($request);
        $body       = json_decode($response->getBody(), true);

        // success assertions
        $this->assertNotEmpty($body);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($body['status']);
        $this->assertEquals(count($this->entities), $body['deleted']);
        $this->populate($this->uri); // refreshes the $entities prop
        $this->assertEquals(0, sizeof($this->entities)); // checks if all entities were deleted

        /*
         * Validates Json Schema with Json Response
         */
        $this->assertTrue(
            $this->validateSchema(
                'setting/deleteAll.json',
                json_decode($response->getBody())
            ),
            $this->schemaErrors
        );
    }

    public function testNotFound() {
		$this->uri        = '/1.0/companies/dummy-ltd/settings';
    	$request    = $this->createRequest($this->createEnvironment());
        $response   = $this->process($request);
        $body       = json_decode($response->getBody(), true);

        // success assertions
        $this->assertNotEmpty($body);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($body['status']);

        /*
         * Validates Json Schema with Json Response
         */
        $this->assertTrue(
            $this->validateSchema(
                'error.json',
                json_decode($response->getBody())
            ),
            $this->schemaErrors
        );
    }
}