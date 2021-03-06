<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Test\Unit\Event\Permission;

use App\Entity\Company\Permission;
use App\Event\Company\Permission\Created;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class CreatedTest extends AbstractUnit {
    public function testConstruct() {
        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $permission = new Permission([], $optimus);

        $created = new Created($permission);

        $this->assertInstanceOf(Permission::class, $created->permission);
    }
}
