<?php

namespace FreePBX\modules\Api\Gql;
use FreePBX\modules\Api\Includes\ApiBase;

abstract class Base extends ApiBase {
	protected $type = 'gql';
	protected $typeContainer;
	protected $module;
	public function __construct($freepbx,$typeContainer,$module) {
		$this->freepbx = $freepbx;
		$this->typeContainer = $typeContainer;
		$this->module = $module;
	}

	public function initReferences() {

	}

	public function postInitReferences() {

	}

	public function constructQuery() {
		return [];
	}

	public function constructMutation() {
		return [];
	}
}
