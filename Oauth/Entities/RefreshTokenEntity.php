<?php
namespace FreePBX\modules\Api\Oauth\Entities;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
class RefreshTokenEntity implements RefreshTokenEntityInterface {
	use RefreshTokenTrait, EntityTrait;
}
