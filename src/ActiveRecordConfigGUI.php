<?php

namespace srag\ActiveRecordConfig;

use ilPluginConfigGUI;
use ilUtil;
use srag\ActiveRecordConfig\Exception\ActiveRecordConfigException;
use srag\DIC\DICTrait;

/**
 * Class ActiveRecordConfigGUI
 *
 * @package srag\ActiveRecordConfig
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class ActiveRecordConfigGUI extends ilPluginConfigGUI {

	use DICTrait;
	/**
	 * @var string
	 */
	const CMD_APPLY_FILTER = "applyFilter";
	/**
	 * @var string
	 */
	const CMD_RESET_FILTER = "resetFilter";
	/**
	 * @var string
	 */
	const CMD_CONFIGURE = "configure";
	/**
	 * @var string
	 */
	const CMD_UPDATE_CONFIGURE = "updateConfigure";
	/**
	 * @var string
	 */
	const LANG_MODULE_CONFIG = "config";
	/**
	 * @var string
	 */
	const TAB_CONFIGURATION = "configuration";
	/**
	 * @var array
	 *
	 * @abstract
	 */
	protected static $tabs = [ self::TAB_CONFIGURATION => ActiveRecordConfigFormGUI::class ];
	/**
	 * @var array
	 */
	protected static $custom_commands = [];


	/**
	 * ActiveRecordConfigGUI constructor
	 */
	public function __construct() {

	}


	/**
	 * @param string $cmd
	 *
	 * @throws ActiveRecordConfigException Unknown command $cmd!
	 * @throws ActiveRecordConfigException Class $config_gui_class_name not extends ActiveRecordConfigFormGUI or ActiveRecordConfigTableGUI!
	 */
	public final function performCommand(/*string*/
		$cmd)/*: void*/ {
		$next_class = self::dic()->ctrl()->getNextClass($this);

		switch (strtolower($next_class)) {
			default:
				$this->setTabs();

				switch (true) {
					case (in_array($cmd, static::$custom_commands)):
						$this->{$cmd}();
						break;

					case ($cmd === self::CMD_CONFIGURE):
						$this->configure(key(static::$tabs));
						break;

					case (strpos($cmd, self::CMD_CONFIGURE . "_") === 0):
						$tab_id = substr($cmd, strlen(self::CMD_CONFIGURE . "_"));

						$this->configure($tab_id);
						break;

					case (strpos($cmd, self::CMD_UPDATE_CONFIGURE . "_") === 0):
						$tab_id = substr($cmd, strlen(self::CMD_UPDATE_CONFIGURE . "_"));

						$this->updateConfigure($tab_id);
						break;

					case (strpos($cmd, self::CMD_APPLY_FILTER . "_") === 0):
						$tab_id = substr($cmd, strlen(self::CMD_APPLY_FILTER . "_"));

						$this->applyFilter($tab_id);
						break;

					case (strpos($cmd, self::CMD_RESET_FILTER . "_") === 0):
						$tab_id = substr($cmd, strlen(self::CMD_RESET_FILTER . "_"));

						$this->resetFilter($tab_id);
						break;

					default:
						throw new ActiveRecordConfigException("Unknown command $cmd!");
						break;
				}
				break;
		}
	}


	/**
	 *
	 */
	private final function setTabs() {
		foreach (static::$tabs as $tab_id => $config_gui_class_name) {
			self::dic()->tabs()->addTab($tab_id, $this->txt($tab_id), self::dic()->ctrl()->getLinkTarget($this, self::CMD_CONFIGURE . "_" . $tab_id));
		}
	}


	/**
	 * @param string $tab_id
	 *
	 * @throws ActiveRecordConfigException Class $config_gui_class_name not extends ActiveRecordConfigFormGUI or ActiveRecordConfigTableGUI!
	 */
	private final function configure(/*string*/
		$tab_id)/*: void*/ {
		self::dic()->tabs()->activateTab($tab_id);

		$gui = $this->getConfigurationGUI($tab_id);

		self::plugin()->output($gui);
	}


	/**
	 * @param string $tab_id
	 *
	 * @throws ActiveRecordConfigException Class $config_gui_class_name not extends ActiveRecordConfigFormGUI!
	 */
	private final function updateConfigure(/*string*/
		$tab_id)/*: void*/ {
		self::dic()->tabs()->activateTab($tab_id);

		$form = $this->getConfigurationFormGUI(static::$tabs[$tab_id], $tab_id);

		$form->setValuesByPost();

		if (!$form->checkInput()) {
			self::plugin()->output($form);

			return;
		}

		$form->updateConfig();

		ilUtil::sendSuccess($this->txt($tab_id . "_saved"));

		self::plugin()->output($form);
	}


	/**
	 * @param string $tab_id
	 *
	 * @throws ActiveRecordConfigException Class $config_form_gui_class_name not extends ActiveRecordConfigTableGUI!
	 */
	protected function applyFilter(/*string*/
		$tab_id)/*: void*/ {
		$table = $this->getConfigurationTable(static::$tabs[$tab_id], self::CMD_APPLY_FILTER . "_" . $tab_id, $tab_id);

		$table->writeFilterToSession();

		self::dic()->ctrl()->redirect($this, self::CMD_CONFIGURE . "_" . $tab_id);
	}


	/**
	 * @param string $tab_id
	 *
	 * @throws ActiveRecordConfigException Class $config_form_gui_class_name not extends ActiveRecordConfigTableGUI!
	 */
	protected function resetFilter(/*string*/
		$tab_id)/*: void*/ {
		$table = $this->getConfigurationTable(static::$tabs[$tab_id], self::CMD_RESET_FILTER . "_" . $tab_id, $tab_id);

		$table->resetFilter();

		$table->resetOffset();

		self::dic()->ctrl()->redirect($this, self::CMD_CONFIGURE . "_" . $tab_id);
	}


	/**
	 * @param string $tab_id
	 *
	 * @return ActiveRecordConfigFormGUI|ActiveRecordConfigTableGUI
	 *
	 * @throws ActiveRecordConfigException Class $config_gui_class_name not extends ActiveRecordConfigFormGUI or ActiveRecordConfigTableGUI!
	 */
	private final function getConfigurationGUI(/*string*/
		$tab_id) {
		$config_gui_class_name = static::$tabs[$tab_id];

		switch (true) {
			case (substr($config_gui_class_name, - strlen("FormGUI")) === "FormGUI"):
				$config_gui = $this->getConfigurationFormGUI($config_gui_class_name, $tab_id);
				break;

			case (substr($config_gui_class_name, - strlen("TableGUI")) === "TableGUI"):
				$config_gui = $this->getConfigurationTable($config_gui_class_name, self::CMD_CONFIGURE . "_" . $tab_id, $tab_id);
				break;

			default:
				throw new ActiveRecordConfigException("Class $config_gui_class_name not extends ActiveRecordConfigFormGUI or ActiveRecordConfigTableGUI!");
				break;
		}

		return $config_gui;
	}


	/**
	 * @param string $config_form_gui_class_name
	 * @param string $tab_id
	 *
	 * @return ActiveRecordConfigFormGUI
	 *
	 * @throws ActiveRecordConfigException Class $config_form_gui_class_name not exists!
	 * @throws ActiveRecordConfigException Class $config_form_gui_class_name not extends ActiveRecordConfigFormGUI!
	 */
	private final function getConfigurationFormGUI(/*string*/
		$config_form_gui_class_name, /*string*/
		$tab_id)/*: ActiveRecordConfigFormGUI*/ {
		if (!class_exists($config_form_gui_class_name)) {
			throw new ActiveRecordConfigException("Class $config_form_gui_class_name not exists!");
		}

		$config_form_gui = new $config_form_gui_class_name($this, $tab_id);

		if (!$config_form_gui instanceof ActiveRecordConfigFormGUI) {
			throw new ActiveRecordConfigException("Class $config_form_gui_class_name not extends ActiveRecordConfigFormGUI!");
		}

		return $config_form_gui;
	}


	/**
	 * @param string $config_table_gui_class_name
	 * @param string $parent_cmd
	 * @param string $tab_id
	 *
	 * @return ActiveRecordConfigTableGUI
	 *
	 * @throws ActiveRecordConfigException Class $config_form_gui_class_name not exists!
	 * @throws ActiveRecordConfigException Class $config_form_gui_class_name not extends ActiveRecordConfigTableGUI!
	 */
	private final function getConfigurationTable(/*string*/
		$config_table_gui_class_name,/*string*/
		$parent_cmd, /*string*/
		$tab_id)/*: ActiveRecordConfigTableGUI*/ {
		if (!class_exists($config_table_gui_class_name)) {
			throw new ActiveRecordConfigException("Class $config_table_gui_class_name not exists!");
		}

		$config_table_gui = new $config_table_gui_class_name($this, $parent_cmd, $tab_id);

		if (!$config_table_gui instanceof ActiveRecordConfigTableGUI) {
			throw new ActiveRecordConfigException("Class $config_table_gui_class_name not extends ActiveRecordConfigTableGUI!");
		}

		return $config_table_gui;
	}


	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private final function txt(/*string*/
		$key)/*: string*/ {
		return self::plugin()->translate($key, self::LANG_MODULE_CONFIG);
	}
}
