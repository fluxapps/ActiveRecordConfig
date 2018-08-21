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
	 * @param string $key
	 * @param bool   $store_new
	 *
	 * @return static
	 */
	protected static final function getConfig($key, $store_new = true) {
		/**
		 * @var static $config
		 */

		$config = self::where([
			"key" => $key
		])->first();

		if ($config === NULL) {
			$config = new static();

			$config->setKey($key);

			if ($store_new) {
				$config->store();
			}
		}

		return $config;
	}


	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected static final function getXValue($key) {
		$config = self::getConfig($key);

		return $config->getValue();
	}


	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	protected static final function setXValue($key, $value) {
		$config = self::getConfig($key, false);

		$config->setValue($value);

		$config->store();
	}


	/**
	 * @return string[]
	 */
	public static final function getAll() {
		return array_reduce(self::get(), function (array $configs, self $config) {
			$configs[$config->getKey()] = $config->getValue();

			return $configs;
		}, []);
	}


	/**
	 * @return string[]
	 */
	public static final function getKeys() {
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

		foreach ($configs as $key => $value) {
			self::setXValue($key, $value);
		}
	}


	/**
	 * @param string $key
	 */
	public static final function deleteConfig($key) {
		/**
		 * @var self $config
		 */

		$config = self::where([
			"key" => $key
		])->first();

		if ($config !== NULL) {
			$config->delete();
		}
	}


	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public static final function getStringValue($key) {
		return strval(self::getXValue($key));
	}


	/**
	 * @param string $key
	 * @param string $value
	 */
	public static final function setStringValue($key, $value) {
		self::setXValue($key, strval($value));
	}


	/**
	 * @param string $key
	 *
	 * @return int
	 */
	public static final function getIntegerValue($key) {
		return intval(self::getStringValue($key));
	}


	/**
	 * @param string $key
	 * @param int    $value
	 */
	public static final function setIntegerValue($key, $value) {
		self::setStringValue($key, intval($value));
	}


	/**
	 * @param string $key
	 *
	 * @return double
	 */
	public static final function getDoubleValue($key) {
		return doubleval(self::getStringValue($key));
	}


	/**
	 * @param string $key
	 * @param double $value
	 */
	public static final function setDoubleValue($key, $value) {
		self::setStringValue($key, doubleval($value));
	}


	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public static final function getBooleanValue($key) {
		return boolval(self::getStringValue($key));
	}


	/**
	 * @param string $key
	 * @param bool   $value
	 */
	public static final function setBooleanValue($key, $value) {
		self::setStringValue($key, boolval($value));
	}


	/**
	 * @param string $key
	 *
	 * @return int
	 */
	public static final function getDateValue($key) {
		$date_time = new DateTime(self::getStringValue($key));

		return $date_time->getTimestamp();
	}


	/**
	 * @param string $key
	 * @param int    $timestamp
	 */
	public static final function setDateValue($key, $timestamp) {
		if ($timestamp === NULL) {
			// Fix `@null`
			self::setNullValue($key);

			return;
		}

		$date_time = new DateTime("@" . $timestamp);

		$formated = $date_time->format("Y-m-d H:i:s");

		self::setStringValue($key, $formated);
	}


	/**
	 * @param string $key
	 * @param bool   $assoc
	 *
	 * @return mixed
	 */
	public static final function getJsonValue($key, $assoc = false) {
		return json_decode(self::getStringValue($key), $assoc);
	}


	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public static final function setJsonValue($key, $value) {
		self::setStringValue($key, json_encode($value));
	}


	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public static final function isNullValue($key) {
		return (self::getXValue($key) === NULL);
	}


	/**
	 * @param string $key
	 */
	public static final function setNullValue($key) {
		self::setXValue($key, NULL);
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
	protected $key = NULL;
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
	 * @param string|null      $primary_key_value
	 * @param arConnector|null $connector
	 */
	public final function __construct($primary_key_value = NULL, arConnector $connector = NULL) {
		parent::__construct($primary_key_value, $connector);
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
	protected final function getKey() {
		return $this->key;
	}


	/**
	 * @param string $key
	 */
	protected final function setKey($key) {
		$this->key = $key;
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
