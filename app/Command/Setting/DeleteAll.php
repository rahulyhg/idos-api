<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Command\Setting;

use App\Command\AbstractCommand;

/**
 * Setting "Delete All" Command.
 */
class DeleteAll extends AbstractCommand {
    /**
     * Company Id that all settings will be deleted.
     *
     * @var int
     */
    public $companyId;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) {
        if (isset($parameters['companyId']))
            $this->companyId = $parameters['companyId'];

        return $this;
    }
}