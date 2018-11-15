<?php

namespace srag\ActiveRecordConfig;

use srag\ActiveRecordConfig\Exception\ActiveRecordConfigException;
use srag\ActiveRecordConfig\PropertyForm\PropertyFormGUI;

/**
 * Class ActiveRecordConfigFormGUI
 *
 * @package srag\ActiveRecordConfig
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class ActiveRecordConfigFormGUI extends PropertyFormGUI {

	/* *
	 * @var string
	 *
	 * @abstract
	 *
	 * TODO: Implement Constants in Traits in PHP Core
	 * /
	const CONFIG_CLASS_NAME = "";*/
	/**
	 * @var string
	 */
	protected $tab_id;


	/**
	 * ActiveRecordConfigFormGUI constructor
	 *
	 * @param ActiveRecordConfigGUI $parent
	 * @param string                $tab_id
	 */
	public function __construct(ActiveRecordConfigGUI $parent, /*string*/
		$tab_id) {
		$this->tab_id = $tab_id;

		parent::__construct($parent);
	}


	/**
	 * @inheritdoc
	 */
	protected final function getValue(/*string*/
		$key) {
		return (static::CONFIG_CLASS_NAME)::getField($key);
	}


	/**
	 * @inheritdoc
	 */
	protected final function initCommands()/*: void*/ {
		$this->setFormAction(self::dic()->ctrl()->getFormAction($this->parent));

		$this->setTitle($this->txt($this->tab_id));

		$this->addCommandButton(ActiveRecordConfigGUI::CMD_UPDATE_CONFIGURE . "_" . $this->tab_id, $this->txt("save"));
	}


	/**
	 * @inheritdoc
	 */
	protected final function initItems()/*: void*/ {
		$this->checkConfigClassNameConst();

		parent::initItems();
	}


	/**
	 * @inheritdoc
	 */
	protected final function setValue(/*string*/
		$key, $value)/*: void*/ {
		return (static::CONFIG_CLASS_NAME)::setField($key, $value);
	}


	/**
	 * @inheritdoc
	 */
	protected final function txt(/*string*/
		$key)/*: string*/ {
		return self::plugin()->translate($key, ActiveRecordConfigGUI::LANG_MODULE_CONFIG);
	}


	/**
	 * @inheritdoc
	 */
	public final function updateForm()/*: void*/ {
		parent::updateForm();
	}


	/**
	 * @throws ActiveRecordConfigException Your class needs to implement the CONFIG_CLASS_NAME constant!
	 */
	private final function checkConfigClassNameConst()/*: void*/ {
		if (!defined("static::CONFIG_CLASS_NAME") || empty(static::CONFIG_CLASS_NAME)) {
			throw new ActiveRecordConfigException("Your class needs to implement the CONFIG_CLASS_NAME constant!");
		}
	}
}
