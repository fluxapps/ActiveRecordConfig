<?php

namespace srag\ActiveRecordConfig\Config;

use srag\DIC\DICTrait;

/**
 * Class AbstractFactory
 *
 * @package srag\ActiveRecordConfig\Config
 */
abstract class AbstractFactory
{

    use DICTrait;

    /**
     * AbstractFactory constructor
     */
    protected function __construct()
    {

    }


    /**
     * @return Config
     */
    public function newInstance() : Config
    {
        $config = new Config();

        return $config;
    }
}
