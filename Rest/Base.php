<?php

namespace FreePBX\modules\Api\Rest;

class Base {
	protected $freepbx;
	public function __construct($freepbx) {
		$this->freepbx = $freepbx;
	}

	public function setupRoutes($app) {
	}

	public function getScopes() {
		return [];
	}

}
