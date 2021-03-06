<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Validator\Traits;

use App\Validator\Traits\AssertUserName;
use Respect\Validation\Exceptions\ExceptionInterface;
use Test\Unit\AbstractUnit;

class AssertUserNameTest extends AbstractUnit {
    public function testAssertUserNameNull() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName(null);
    }

    public function testAssertUserNameEmpty() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName('');
    }

    public function testAssertUserNameWhitespace() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName('ab cd');
    }

    public function testAssertUserNameSpecialChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName('LKM@#$%4;');
    }

    public function testAssertUserNameInvalid() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName('a' . chr(127) . chr(127));
    }

    public function testAssertUserNameTooLong() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName(str_repeat('x', 51));
    }

    public function testAssertUserNameValid() {
        $traitMock = $this->getMockForTrait(AssertUserName::class);
        $traitMock->assertUserName('x');
        $traitMock->assertUserName('Abc123HD');
        $traitMock->assertUserName(str_repeat('x', 50));

        $this->assertTrue(true);
    }
}
