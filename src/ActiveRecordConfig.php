<?php

namespace srag\ActiveRecordConfig;

use srag\ActiveRecordConfig\Config\Config;
use srag\ActiveRecordConfig\Exception\ActiveRecordConfigException;

/**
 * Class ActiveRecordConfig
 *
 * @package    srag\ActiveRecordConfig
 *
 * @author     studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @deprecated Please use ConfigTrait instead
 */
abstract class ActiveRecordConfig extends Config
{

    /**
     * @var string
     *
     * @abstract
     *
     * @deprecated
     */
    const TABLE_NAME = "";
    /**
     * @var array
     *
     * @abstract
     */
    protected static $fields = [];


    /**
     * @inheritDoc
     *
     * @deprecated
     */
    public static function getTableName() : string
    {
        self::config()->withTableName(static::TABLE_NAME)->withFields(static::$fields);

        return parent::getTableName();
    }


    /**
     * @param string $name
     * @param int    $type
     * @param mixed  $default_value
     *
     * @return mixed
     *
     * @throws ActiveRecordConfigException
     *
     * @deprecated
     */
    protected static final function getDefaultValue(string $name, int $type, $default_value)
    {
        throw new ActiveRecordConfigException("getDefaultValue is not supported anymore - please try to use the second parameter in the fields array instead!",
            ActiveRecordConfigException::CODE_INVALID_FIELD);
    }


    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws ActiveRecordConfigException Invalid type $type!
     * @throws ActiveRecordConfigException Invalid field $name!
     *
     * @deprecated
     */
    public static function getField(string $name)
    {
        self::getTableName();

        return self::config()->getField($name);
    }


    /**
     * Get all values
     *
     * @return array [ [ "name" => value ], ... ]
     *
     * @throws ActiveRecordConfigException Invalid type $type!
     * @throws ActiveRecordConfigException Invalid field $name!
     *
     * @deprecated
     */
    public static function getFields() : array
    {
        self::getTableName();

        return self::config()->getFields_();
    }


    /**
     * Remove a field
     *
     * @param string $name Name
     *
     * @deprecated
     */
    public static function removeField(string $name)/*: void*/
    {
        self::getTableName();

        self::config()->removeField($name);
    }


    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws ActiveRecordConfigException Invalid type $type!
     * @throws ActiveRecordConfigException Invalid field $name!
     *
     * @deprecated
     */
    public static function setField(string $name, $value)/*: void*/
    {
        self::getTableName();

        self::config()->setField($name, $value);
    }


    /**
     * Set all values
     *
     * @param array $fields        [ [ "name" => value ], ... ]
     * @param bool  $remove_exists Delete all exists name before
     *
     * @throws ActiveRecordConfigException Invalid type $type!
     * @throws ActiveRecordConfigException Invalid field $name!
     *
     * @deprecated
     */
    public static function setFields(array $fields, bool $remove_exists = false)/*: void*/
    {
        self::getTableName();

        self::config()->setFields($fields, $remove_exists);
    }
}
