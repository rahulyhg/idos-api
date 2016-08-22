<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

use Phinx\Seed\AbstractSeed;

class S30ServiceHandlersSeed extends AbstractSeed {
    public function run() {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'company_id' => 1,
                'service_id' => 1, // scraper
                'listens'    => json_encode(['idos:source.facebook.added']),
                'created_at' => $now
            ],
            [
                'company_id' => 1,
                'service_id' => 2, // data-mapper
                'listens'    => json_encode(['idos:scraper.facebook.completed']),
                'created_at' => $now
            ]
        ];

        $service_handlers = $this->table('service_handlers');
        $service_handlers
            ->insert($data)
            ->save();
    }
}