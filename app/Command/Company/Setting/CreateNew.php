<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Company\Setting;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Setting "Create New" Command.
 */
class CreateNew extends AbstractCommand {
    /**
     * Setting's section name (user input).
     *
     * @var string
     */
    public $section;
    /**
     * Setting's property name (user input).
     *
     * @var string
     */
    public $property;
    /**
     * Setting's protected value.
     *
     * @var bool
     */
    public $protected;
    /**
     * Setting's property value (user input).
     *
     * @var mixed
     */
    public $value;
    /**
     * Company.
     *
     * @var \App\Entity\Company
     */
    public $company;
    /**
     * Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        if (isset($parameters['section'])) {
            $this->section = $parameters['section'];
        }

        if (isset($parameters['property'])) {
            $this->property = $parameters['property'];
        }

        if (isset($parameters['value'])) {
            $this->value = $parameters['value'];
        }

        if (isset($parameters['protected'])) {
            $this->protected = $parameters['protected'];
        }

        return $this;
    }
}
