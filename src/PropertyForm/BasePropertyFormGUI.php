<?php

namespace srag\ActiveRecordConfig\PropertyForm;

use ilPropertyFormGUI;
use srag\DIC\DICTrait;

/**
 * Class BasePropertyFormGUI
 *
 * @package srag\ActiveRecordConfig\PropertyForm
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class BasePropertyFormGUI extends ilPropertyFormGUI {

	use DICTrait;
	/**
	 * @var object
	 */
	protected $parent;


	/**
	 * BasePropertyFormGUI constructor
	 *
	 * @param object $parent
	 */
	public function __construct($parent) {
		parent::__construct();

		$this->parent = $parent;

		$this->initForm();
	}


	/**
	 *
	 */
	protected final function initForm()/*: void*/ {
		$this->setFormAction(self::dic()->ctrl()->getFormAction($this->parent));

		$this->initCommands();

		$this->initItems();
	}


	/**
	 *
	 */
	protected abstract function initCommands()/*: void*/
	;


	/**
	 *
	 */
	protected abstract function initItems()/*: void*/
	;


	/**
	 *
	 */
	public abstract function updateForm()/*: void*/
	;
}
