<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Event\Hook;

use App\Entity\Hook;
use App\Event\Hook\Updated;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class UpdatedTest extends AbstractUnit {
    public function testConstruct() {
        $optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hook = new Hook([], $optimus);

        $created = new Updated($hook);

        $this->assertInstanceOf(Hook::class, $created->hook);
    }
}