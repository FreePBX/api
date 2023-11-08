<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\RefreshTokenEntity;

#[\AllowDynamicProperties]
class RefreshTokenRepository implements RefreshTokenRepositoryInterface {
	public function __construct($api) {
		$this->api = $api;
	}
	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntityInterface) {
		$this->api->refreshTokens->add($refreshTokenEntityInterface->getIdentifier(), $refreshTokenEntityInterface->getAccessToken()->getIdentifier(), $refreshTokenEntityInterface->getExpiryDateTime(), $_SERVER['REMOTE_ADDR']);
	}

	public function revokeRefreshToken($tokenId) {
		$this->api->refreshTokens->revoke($tokenId);
	}

	public function isRefreshTokenRevoked($tokenId) {
		if($this->api->refreshTokens->isRevoked($tokenId)) {
			return true;
		}
		$this->api->refreshTokens->updateAccessed($tokenId, $_SERVER['REMOTE_ADDR']);
		return false;
	}

	public function getNewRefreshToken() {
		return new RefreshTokenEntity();
	}
}
