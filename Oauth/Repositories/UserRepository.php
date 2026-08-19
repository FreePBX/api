<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use FreePBX\modules\Api\Oauth\Entities\UserEntity;

class UserRepository implements UserRepositoryInterface {
	private $api;

	public function __construct($api) {
		$this->api = $api;
	}

	public function getUserEntityByUserCredentials(
		string $username,
		string $password,
		string $grantType,
		ClientEntityInterface $clientEntity
	): ?UserEntityInterface {
		if ($this->api->freepbx->Userman->checkCredentials($username, $password)) {
			return new UserEntity($this->api->freepbx->Userman->getUserByUsername($username));
		}
		return null;
	}
}
