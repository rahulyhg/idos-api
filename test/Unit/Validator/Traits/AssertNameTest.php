<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Validator\Traits;

use App\Validator\Traits\AssertName;
use Respect\Validation\Exceptions\ExceptionInterface;
use Test\Unit\AbstractUnit;

class AssertNameTest extends AbstractUnit {
    public function testAssertNameEmpty() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName('');
    }

    public function testAssertShortNameEmpty() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertShortName('');
    }

    public function testAssertMediumNameEmpty() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertMediumName('');
    }

    public function testAssertLongNameEmpty() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertLongName('');
    }

    public function testAssertNameOneChar() {
        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName('a');
        $traitMock->assertShortName('a');
        $traitMock->assertMediumName('a');
        $traitMock->assertLongName('a');
        $this->assertTrue(true);
    }

    public function testAssertNameFifteenChars() {
        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName(str_repeat('a', 15));
        $traitMock->assertShortName(str_repeat('a', 15));
        $traitMock->assertMediumName(str_repeat('a', 15));
        $traitMock->assertLongName(str_repeat('a', 15));
        $this->assertTrue(true);
    }

    public function testAssertShortNameSixteenChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertShortName(str_repeat('a', 16));
    }

    public function testAssertNameSixteenChars() {
        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName(str_repeat('a', 16));
        $traitMock->assertMediumName(str_repeat('a', 16));
        $traitMock->assertLongName(str_repeat('a', 16));
        $this->assertTrue(true);
    }

    public function testAssertShortNameThirtyOneChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertShortName(str_repeat('a', 31));
    }

    public function testAssertMediumNameThirtyOneChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertMediumName(str_repeat('a', 31));
    }

    public function testAssertNameThirtyOneChars() {
        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName(str_repeat('a', 31));
        $traitMock->assertLongName(str_repeat('a', 31));
        $this->assertTrue(true);
    }

    public function testAssertShortNameSixtyOneChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertShortName(str_repeat('a', 61));
    }

    public function testAssertMediumNameSixtyOneChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertMediumName(str_repeat('a', 61));
    }

    public function testAssertLongNameSixtyOneChars() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertLongName(str_repeat('a', 61));
    }

    public function testAssertNameSixtyOneChars() {
        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName(str_repeat('a', 61));
        $this->assertTrue(true);
    }

    public function testAssertNameInvalidInput() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertName(chr(20) . chr(127));
    }

    public function testAssertShortNameInvalidInput() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertShortName(chr(20) . chr(127));
    }

    public function testAssertMediumNameInvalidInput() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertMediumName(chr(20) . chr(127));
    }

    public function testAssertLongNameInvalidInput() {
        $this->expectedException(ExceptionInterface::class);

        $traitMock = $this->getMockForTrait(AssertName::class);
        $traitMock->assertLongName(chr(20) . chr(127));
    }
}
