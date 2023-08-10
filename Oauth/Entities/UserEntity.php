<?php
namespace FreePBX\modules\Api\Oauth\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
class UserEntity implements UserEntityInterface {
	public function __construct(private $user)
 {
 }
	public function getIdentifier() {
		return $this->user['id'];
	}
}
