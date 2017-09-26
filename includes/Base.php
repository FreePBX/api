<?php

namespace FreePBX\modules\Gqlapi\includes;

class Base {
	public function __construct($freepbx,$typeContainer) {
		$this->freepbx = $freepbx;
		$this->typeContainer = $typeContainer;
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
