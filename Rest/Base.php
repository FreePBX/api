<?php

namespace FreePBX\modules\Api\Rest;
use FreePBX\modules\Api\Includes\ApiBase;

abstract class Base extends ApiBase {
	protected $type = 'rest';
	public function setupRoutes($app) {
	}
}
