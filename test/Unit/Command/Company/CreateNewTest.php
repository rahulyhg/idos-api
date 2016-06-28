<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Command;

use App\Command\Company\CreateNew;

class CreateNewTest extends \PHPUnit_Framework_TestCase {
    public function testSetParameters() {
        $command = new CreateNew();
        $this->assertNull($command->name);
        $this->assertNull($command->parentId);

        $this->assertInstanceOf(
            CreateNew::class,
            $command->setParameters([])
        );
        $this->assertNull($command->name);
        $this->assertNull($command->parentId);

        $command->setParameters(['name' => 'a']);
        $this->assertSame('a', $command->name);
        $this->assertNull($command->parentId);

        $command->setParameters(['parentId' => 1]);
        $this->assertSame('a', $command->name);
        $this->assertSame(1, $command->parentId);
    }
}