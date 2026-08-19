<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\AccessTokenEntity;

class AccessTokenRepository implements AccessTokenRepositoryInterface {
	private $api;

	public function __construct($api) {
		$this->api = $api;
	}

	public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void {
		$application = $this->api->applications->getByClientId($accessTokenEntity->getClient()->getIdentifier());
		$this->api->accessTokens->add($accessTokenEntity->getIdentifier(), $application['id'], $_SERVER['REMOTE_ADDR'], $accessTokenEntity->getScopes(), $accessTokenEntity->getExpiryDateTime(), $accessTokenEntity->getUserIdentifier());
	}

	public function revokeAccessToken(string $tokenId): void {
		$this->api->accessTokens->revoke($tokenId);
	}

	public function isAccessTokenRevoked(string $tokenId): bool {
		if ($this->api->accessTokens->isRevoked($tokenId)) {
			return true;
		}
		$this->api->accessTokens->updateAccessed($tokenId, $_SERVER['REMOTE_ADDR']);
		return false;
	}

	/**
	 * Get token information including scopes from database by jti
	 * This ensures scopes are validated against database, not JWT claims
	 *
	 * @param string $tokenId The JWT ID (jti)
	 * @return array|null Token data including scopes, or null if not found
	 */
	public function getTokenById($tokenId) {
		return $this->api->accessTokens->get($tokenId);
	}

	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, string|null $userIdentifier = null): AccessTokenEntityInterface {
		$accessToken = new AccessTokenEntity();
		$accessToken->setClient($clientEntity);
		foreach ($scopes as $scope) {
			$accessToken->addScope($scope);
		}
		// client_credentials tokens have no user, and setUserIdentifier() only accepts a non-empty string
		if ($userIdentifier !== null && $userIdentifier !== '') {
			$accessToken->setUserIdentifier($userIdentifier);
		}
		return $accessToken;
	}
}
