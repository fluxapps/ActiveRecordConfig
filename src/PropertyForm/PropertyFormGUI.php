<?php

namespace srag\ActiveRecordConfig\PropertyForm;

use ilCheckboxInputGUI;
use ilDateTimeInputGUI;
use ilFormPropertyGUI;
use ilFormSectionHeaderGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
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
	 * @param array                  $fields
	 * @param self|ilFormPropertyGUI $_this
	 *
	 * @throws ActiveRecordConfigException $fields needs to be an array!
	 * @throws ActiveRecordConfigException Class $class not exists!
	 * @throws ActiveRecordConfigException $item muss be an instance of ilFormPropertyGUI or ilFormSectionHeaderGUI!
	 * @throws ActiveRecordConfigException $options needs to be an array!
	 */
	private final function getFields(array $fields, $_this)/*: void*/ {
		if (!is_array($fields)) {
			throw new ActiveRecordConfigException("\$fields needs to be an array!");
		}

		foreach ($fields as $key => $field) {
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

			if ($item instanceof ilRadioGroupInputGUI) {
				if (!is_array($field[self::PROPERTY_OPTIONS])) {
					throw new ActiveRecordConfigException("\$options needs to be an array!");
				}

				foreach ($field[self::PROPERTY_OPTIONS] as $option_key => $items) {
					$option = new ilRadioOption($this->txt($option_key), $option_key);

					$this->getFields($items, $option);

					$item->addOption($option);
				}
			}

			if (is_array($field[self::PROPERTY_SUBITEMS])) {
				$this->getFields($field[self::PROPERTY_SUBITEMS], $item);
			}

			if ($_this instanceof self) {
				$_this->addItem($item);
			} else {
				$_this->addSubItem($item);
			}
		}
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

		$this->setPropertiesToItem($item, $field);

		$this->setValueToItem($item, $value);

		$this->items_cache[$key] = $item;

		return $item;
	}


	/**
	 * @param array $fields
	 */
	private final function getValueFromItems(array $fields)/*: void*/ {
		foreach ($fields as $key => $field) {
			$item = $this->items_cache[$key];

			$value = $this->getValueFromItem($item);

			$this->setValue($key, $value);

			if ($item instanceof ilRadioGroupInputGUI) {
				foreach ($field[self::PROPERTY_OPTIONS] as $option_key => $items) {
					$this->getValueFromItems($items);
				}
			}

			if (is_array($field[self::PROPERTY_SUBITEMS])) {
				$this->getValueFromItems($field[self::PROPERTY_SUBITEMS]);
			}
		}
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
				if ($item->getMulti()) {
					return $item->getMultiValues();
				} else {
					return $item->getValue();
				}
			}
		}
	}


	/**
	 * @inheritdoc
	 */
	protected final function initItems()/*: void*/ {
		$this->initFields();

		$this->getFields($this->fields, $this);
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
					if (!($item instanceof ilRadioGroupInputGUI)) {
						$item->setOptions($property_value);
					}
					break;

				case self::PROPERTY_REQUIRED:
					$item->setRequired($property_value);
					break;

				case self::PROPERTY_CLASS:
				case self::PROPERTY_SUBITEMS:
					break;

				default:
					$item->{ucfirst($property_key)}($property_value);
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
		$this->getValueFromItems($this->fields);
	}


	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected abstract function getValue(/*string*/
		$key);


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
