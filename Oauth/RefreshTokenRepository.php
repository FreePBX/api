<?php

namespace FreePBX\modules\Gqlapi\Oauth;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface {
	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntityInterface) {
		// Some logic to persist the refresh token in a database
	}

	public function revokeRefreshToken($tokenId) {
		// Some logic to revoke the refresh token in a database
	}

	public function isRefreshTokenRevoked($tokenId) {
		return false; // The refresh token has not been revoked
	}

	public function getNewRefreshToken() {
		return new RefreshTokenEntity();
	}
}
