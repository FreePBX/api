<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\RefreshTokenEntity;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface {
	private $api;

	public function __construct($api) {
		$this->api = $api;
	}

	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void {
		$this->api->refreshTokens->add($refreshTokenEntity->getIdentifier(), $refreshTokenEntity->getAccessToken()->getIdentifier(), $refreshTokenEntity->getExpiryDateTime(), $_SERVER['REMOTE_ADDR']);
	}

	public function revokeRefreshToken(string $tokenId): void {
		$this->api->refreshTokens->revoke($tokenId);
	}

	public function isRefreshTokenRevoked(string $tokenId): bool {
		if ($this->api->refreshTokens->isRevoked($tokenId)) {
			return true;
		}
		$this->api->refreshTokens->updateAccessed($tokenId, $_SERVER['REMOTE_ADDR']);
		return false;
	}

	public function getNewRefreshToken(): ?RefreshTokenEntityInterface {
		return new RefreshTokenEntity();
	}
}
