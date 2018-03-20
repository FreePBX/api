<?php

namespace FreePBX\modules\Api\Gql;

class Base {
	protected $freepbx;
	protected $typeContainer;
	public function __construct($freepbx,$typeContainer) {
		$this->freepbx = $freepbx;
		$this->typeContainer = $typeContainer;
	}

	public static function getScopes() {
		return [];
	}

	public function allowedScopes($scopes) {

	}

	protected function checkScope($scope) {
		return true;
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
