<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Validator;

use App\Validator\ServiceHandler;
use Respect\Validation\Exceptions\ExceptionInterface;
use Test\Unit\AbstractUnit;

class ServiceHandlerTest extends AbstractUnit {
    protected $validator;

    protected function setUp() {
        $this->validator = new ServiceHandler();
    }

    // What is the source after all..?
    // public function testAssertSourceInvalid() {
    //     $this->expectedException(ExceptionInterface::class);
    //     $this->validator->assertName([]);
    // }

    // public function testAssertSource() {
    //     $this->validator->assertSource('source');
    //     $this->assertTrue(true);
    // }

    public function testAssertLocationInvalidUrl() {
        $this->expectedException(ExceptionInterface::class);
        $this->validator->assertUrl('url');
    }

    public function testAssertLocation() {
        $this->validator->assertUrl('http://localhost:8080');
        $this->assertTrue(true);
    }

    public function testAuthUsernameInvalid() {
        $this->expectedException(ExceptionInterface::class);
        $this->validator->assertUserName([]);
    }

    public function testAuthUsername() {
        $this->validator->assertUserName('us3rn4m3');
        $this->assertTrue(true);
    }

    public function testAuthPasswordInvalid() {
        $this->expectedException(ExceptionInterface::class);
        $this->validator->assertPassword([]);
    }

    public function testAuthPassword() {
        $this->validator->assertPassword('Auth_P4ssw0rd$');
        $this->assertTrue(true);
    }
}
