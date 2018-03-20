<?php
namespace FreePBX\modules\Api\Oauth\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
class UserEntity implements UserEntityInterface {
	private $user;

	public function __construct($user) {
		$this->user = $user;
	}
	public function getIdentifier() {
		return $this->user['id'];
	}
}
