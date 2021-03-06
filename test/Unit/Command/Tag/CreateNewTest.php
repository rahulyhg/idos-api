<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Command\Tag;

use App\Command\Profile\Tag\CreateNew;
use Test\Unit\AbstractUnit;

class CreateNewTest extends AbstractUnit {
    public function testSetParameters() {
        $command = new CreateNew();
        $this->assertNull($command->user);
        $this->assertNull($command->name);

        $this->assertInstanceOf(
            CreateNew::class,
            $command->setParameters([])
        );

        $this->assertNull($command->user);
        $this->assertNull($command->name);

        $command->setParameters(['user' => 'a']);
        $this->assertSame('a', $command->user);
        $this->assertNull($command->name);

        $command->setParameters(['name' => 'b']);
        $this->assertSame('a', $command->user);
        $this->assertSame('b', $command->name);
    }
}
