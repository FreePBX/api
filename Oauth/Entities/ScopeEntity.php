<?php

namespace FreePBX\modules\Api\Oauth\Entities;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ScopeEntity implements ScopeEntityInterface {
	use EntityTrait;
	public function jsonSerialize() {
		return $this->getIdentifier();
	}
}
