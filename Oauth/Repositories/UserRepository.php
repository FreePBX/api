<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\UserEntity;

#[\AllowDynamicProperties]
class UserRepository implements UserRepositoryInterface {
	public function __construct($api) {
		$this->api = $api;
	}

	public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity) {
		if ($this->api->freepbx->Userman->checkCredentials($username, $password)) {
			return new UserEntity($this->api->freepbx->Userman->getUserByUsername($username));
		}
		return;
	}
}
