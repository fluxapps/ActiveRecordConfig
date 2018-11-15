<?php

namespace srag\ActiveRecordConfig\PropertyForm;

use ilCheckboxInputGUI;
use ilDateTimeInputGUI;
use ilFormPropertyGUI;
use ilFormSectionHeaderGUI;
use srag\ActiveRecordConfig\Exception\ActiveRecordConfigException;

/**
 * Class BasePropertyFormGUI
 *
 * @package srag\ActiveRecordConfig\PropertyForm
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class PropertyFormGUI extends BasePropertyFormGUI {

	/**
	 * @var string
	 */
	const PROPERTY_CLASS = "class";
	/**
	 * @var string
	 */
	const PROPERTY_DISABLED = "disabled";
	/**
	 * @var string
	 */
	const PROPERTY_INFO = "info";
	/**
	 * @var string
	 */
	const PROPERTY_MULTI = "multi";
	/**
	 * @var string
	 */
	const PROPERTY_OPTIONS = "options";
	/**
	 * @var string
	 */
	const PROPERTY_REQUIRED = "required";
	/**
	 * @var string
	 */
	const PROPERTY_SUBITEMS = "subitems";
	/**
	 * @var array
	 */
	protected $fields = [];
	/**
	 * @var ilFormPropertyGUI[]|ilFormSectionHeaderGUI[]
	 */
	private $items_cache = [];


	/**
	 * PropertyFormGUI constructor
	 *
	 * @param object $parent
	 */
	public function __construct($parent) {
		parent::__construct($parent);
	}


	/**
	 * @param string $key
	 * @param array  $field
	 *
	 * @return ilFormPropertyGUI|ilFormSectionHeaderGUI
	 */
	private final function getItem($key, array $field) {
		$value = $this->getValue($key);

		/**
		 * @var ilFormPropertyGUI|ilFormSectionHeaderGUI $item
		 */
		$item = new $field[self::PROPERTY_CLASS]($this->txt($key), $key);

		$this->setValueToItem($item, $value);

		$this->setPropertiesToItem($item, $field);

		$this->items_cache[$key] = $item;

		return $item;
	}


	/**
	 * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $item
	 *
	 * @return mixed
	 */
	private final function getValueFromItem($item) {
		if ($item instanceof ilCheckboxInputGUI) {
			return boolval($item->getChecked());
		} else {
			if ($item instanceof ilDateTimeInputGUI) {
				return $item->getDate();
			} else {
				return $item->getValue();
			}
		}
	}


	/**
	 * @inheritdoc
	 */
	protected function initItems()/*: void*/ {
		$this->initFields();

		$this->handleFields();
	}


	/**
	 * @throws ActiveRecordConfigException $fields needs to be an array!
	 * @throws ActiveRecordConfigException Class $class not exists!
	 * @throws ActiveRecordConfigException $item muss be an instance of ilFormPropertyGUI or ilFormSectionHeaderGUI!
	 */
	private final function handleFields()/*: void*/ {
		if (!is_array($this->fields)) {
			throw new ActiveRecordConfigException("\$fields needs to be an array!");
		}

		foreach ($this->fields as $key => $field) {
			if (!is_array($field)) {
				throw new ActiveRecordConfigException("\$fields needs to be an array!");
			}
			if (!class_exists($field[self::PROPERTY_CLASS])) {
				throw new ActiveRecordConfigException("Class " . $field[self::PROPERTY_CLASS] . " not exists!");
			}

			$item = $this->getItem($key, $field);

			if (!($item instanceof ilFormPropertyGUI || $item instanceof ilFormSectionHeaderGUI)) {
				throw new ActiveRecordConfigException("\$item muss be an instance of ilFormPropertyGUI or ilFormSectionHeaderGUI!");
			}

			if (is_array($field[self::PROPERTY_SUBITEMS])) {
				foreach ($field[self::PROPERTY_SUBITEMS] as $sub_key => $sub_field) {
					if (!is_array($sub_field)) {
						throw new ActiveRecordConfigException("\$fields needs to be an array!");
					}

					$sub_item = $this->getItem($sub_key, $sub_field);

					$item->addSubItem($sub_item);
				}
			}

			$this->addItem($item);
		}
	}


	/**
	 * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $item
	 * @param array                                    $properties
	 */
	private final function setPropertiesToItem($item, array $properties)/*: void*/ {
		foreach ($properties as $property_key => $property_value) {
			switch ($property_key) {
				case self::PROPERTY_DISABLED:
					$item->setDisabled($property_value);
					break;

				case self::PROPERTY_INFO:
					$item->setInfo($property_value);
					break;

				case self::PROPERTY_MULTI:
					$item->setMulti($property_value);
					break;

				case self::PROPERTY_OPTIONS:
					$item->setOptions($property_value);
					break;

				case self::PROPERTY_REQUIRED:
					$item->setRequired($property_value);
					break;

				default:
					break;
			}
		}
	}


	/**
	 * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $item
	 * @param mixed                                    $value
	 */
	private final function setValueToItem($item, $value)/*: void*/ {
		if ($item instanceof ilCheckboxInputGUI) {
			$item->setChecked($value);
		} else {
			if ($item instanceof ilDateTimeInputGUI) {
				$item->setDate($value);
			} else {
				$item->setValue($value);
			}
		}
	}


	/**
	 * @param string $key
	 *
	 * @return string
	 */
	protected function txt(/*string*/
		$key)/*: string*/ {
		return self::plugin()->translate($key);
	}


	/**
	 * @inheritdoc
	 */
	public function updateForm()/*: void*/ {
		foreach ($this->fields as $key => $field) {
			$item = $this->items_cache[$key];

			$value = $this->getValueFromItem($item);

			$this->setValue($key, $value);

			if (is_array($field[self::PROPERTY_SUBITEMS])) {
				foreach ($field[self::PROPERTY_SUBITEMS] as $sub_key => $sub_field) {
					$sub_item = $this->items_cache[$sub_key];

					$sub_value = $this->getValueFromItem($sub_item);

					$this->setValue($sub_key, $sub_value);
				}
			}
		}
	}


	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected abstract function getValue(/*string*/
		$key);;


	/**
	 *
	 */
	protected abstract function initFields()/*: void*/
	;


	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	protected abstract function setValue(/*string*/
		$key, $value)/*: void*/
	;
}
