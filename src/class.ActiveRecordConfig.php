<?php

namespace srag\ActiveRecordConfig;

use ActiveRecord;
use arConnector;
use DateTime;
use srag\DIC\DICTrait;

/**
 * Class ActiveRecordConfig
 *
 * @package srag\ActiveRecordConfig
 */
abstract class ActiveRecordConfig extends ActiveRecord {

	use DICTrait;
	/**
	 * @var string
	 *
	 * @abstract
	 */
	const TABLE_NAME = "";


	/**
	 * @return string
	 */
	public final function getConnectorContainerName() {
		return static::TABLE_NAME;
	}


	/**
	 * @return string
	 *
	 * @deprecated
	 */
	public static final function returnDbTableName() {
		return static::TABLE_NAME;
	}


	/**
	 * @param string $name
	 * @param bool   $store_new
	 *
	 * @return static
	 */
	protected static final function getConfig($name, $store_new = true) {
		/**
		 * @var static $config
		 */

		$config = self::where([
			"name" => $name
		])->first();

		if ($config === NULL) {
			$config = new static();

			$config->setName($name);

			if ($store_new) {
				$config->store();
			}
		}

		return $config;
	}


	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected static final function getXValue($name) {
		$config = self::getConfig($name);

		return $config->getValue();
	}


	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	protected static final function setXValue($name, $value) {
		$config = self::getConfig($name, false);

		$config->setValue($value);

		$config->store();
	}


	/**
	 * @return string[]
	 */
	public static final function getAll() {
		return array_reduce(self::get(), function (array $configs, self $config) {
			$configs[$config->getName()] = $config->getValue();

			return $configs;
		}, []);
	}


	/**
	 * @return string[]
	 */
	public static final function getNames() {
		return array_keys(self::getAll());
	}


	/**
	 * @param array $configs
	 * @param bool  $delete_exists
	 */
	public static final function setAll(array $configs, $delete_exists = false) {
		if ($delete_exists) {
			self::truncateDB();
		}

		foreach ($configs as $name => $value) {
			self::setXValue($name, $value);
		}
	}


	/**
	 * @param string $name
	 */
	public static final function deleteConfig($name) {
		$config = self::getConfig($name, false);

		$config->delete();
	}


	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public static final function getStringValue($name) {
		return strval(self::getXValue($name));
	}


	/**
	 * @param string $name
	 * @param string $value
	 */
	public static final function setStringValue($name, $value) {
		self::setXValue($name, strval($value));
	}


	/**
	 * @param string $name
	 *
	 * @return int
	 */
	public static final function getIntegerValue($name) {
		return intval(self::getStringValue($name));
	}


	/**
	 * @param string $name
	 * @param int    $value
	 */
	public static final function setIntegerValue($name, $value) {
		self::setStringValue($name, intval($value));
	}


	/**
	 * @param string $name
	 *
	 * @return double
	 */
	public static final function getDoubleValue($name) {
		return doubleval(self::getStringValue($name));
	}


	/**
	 * @param string $name
	 * @param double $value
	 */
	public static final function setDoubleValue($name, $value) {
		self::setStringValue($name, doubleval($value));
	}


	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static final function getBooleanValue($name) {
		return boolval(self::getStringValue($name));
	}


	/**
	 * @param string $name
	 * @param bool   $value
	 */
	public static final function setBooleanValue($name, $value) {
		self::setStringValue($name, boolval($value));
	}


	/**
	 * @param string $name
	 *
	 * @return int
	 */
	public static final function getDateValue($name) {
		$date_time = new DateTime(self::getStringValue($name));

		return $date_time->getTimestamp();
	}


	/**
	 * @param string $name
	 * @param int    $timestamp
	 */
	public static final function setDateValue($name, $timestamp) {
		if ($timestamp === NULL) {
			// Fix `@null`
			self::setNullValue($name);

			return;
		}

		$date_time = new DateTime("@" . $timestamp);

		$formated = $date_time->format("Y-m-d H:i:s");

		self::setStringValue($name, $formated);
	}


	/**
	 * @param string $name
	 * @param bool   $assoc
	 *
	 * @return mixed
	 */
	public static final function getJsonValue($name, $assoc = false) {
		return json_decode(self::getStringValue($name), $assoc);
	}


	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public static final function setJsonValue($name, $value) {
		self::setStringValue($name, json_encode($value));
	}


	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static final function isNullValue($name) {
		return (self::getXValue($name) === NULL);
	}


	/**
	 * @param string $name
	 */
	public static final function setNullValue($name) {
		self::setXValue($name, NULL);
	}


	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_length      100
	 * @con_is_notnull  true
	 * @con_is_primary  true
	 */
	protected $name = NULL;
	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_is_notnull  false
	 */
	protected $value = NULL;


	/**
	 * ActiveRecordConfig constructor
	 *
	 * @param string|null      $primary_name_value
	 * @param arConnector|null $connector
	 */
	public final function __construct($primary_name_value = NULL, arConnector $connector = NULL) {
		parent::__construct($primary_name_value, $connector);
	}


	/**
	 * @param string $field_name
	 *
	 * @return mixed|null
	 */
	public final function sleep($field_name) {
		$field_value = $this->{$field_name};

		switch ($field_name) {
			default:
				return NULL;
		}
	}


	/**
	 * @param string $field_name
	 * @param mixed  $field_value
	 *
	 * @return mixed|null
	 */
	public final function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			default:
				return NULL;
		}
	}


	/**
	 * @return string
	 */
	protected final function getName() {
		return $this->name;
	}


	/**
	 * @param string $name
	 */
	protected final function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	protected final function getValue() {
		return $this->value;
	}


	/**
	 * @param string $value
	 */
	protected final function setValue($value) {
		$this->value = $value;
	}
}
