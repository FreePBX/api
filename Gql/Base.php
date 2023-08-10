<?php

namespace FreePBX\modules\Api\Gql;

use FreePBX\modules\Api\Includes\ApiBase;

abstract class Base extends ApiBase {
	protected $type = 'gql';
	protected $nodeDefinition;
	protected static $priority = 500;
	public function __construct($freepbx, protected $typeContainer, protected $module) {
		$this->freepbx = $freepbx;
	}

	public function setNodeDefinition($nodeDefinition) {
		$this->nodeDefinition = $nodeDefinition;
	}

	protected function getNodeDefinition() {
		return $this->nodeDefinition;
	}

	/**
	 * Run before anything else
	 * @method initializeTypes
	 */
	public function initializeTypes() {
	}

	/**
	 * Run after initializeTypes
	 * @method postInitializeTypes
	 */
	public function postInitializeTypes() {
	}

	/**
	 * Run to generate callback query
	 * @method queryCallback
	 * @return callable        [description]
	 */
	public function queryCallback() {
	}

	/**
	 * Run to generate mutation query
	 * @method mutationCallback
	 * @return callable           [description]
	 */
	public function mutationCallback() {
	}

	public static function getPriority() {
		return static::$priority;
	}
}