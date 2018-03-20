<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\AccessTokenEntity;

class AccessTokenRepository implements AccessTokenRepositoryInterface {
	public function __construct($api) {
		$this->api = $api;
	}
	public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity) {
		$application = $this->api->applications->getByClientId($accessTokenEntity->getClient()->getIdentifier());
		$this->api->accessTokens->add($accessTokenEntity->getIdentifier(), $application['id'], $_SERVER['REMOTE_ADDR'], $accessTokenEntity->getScopes(), $accessTokenEntity->getExpiryDateTime(), $accessTokenEntity->getUserIdentifier());
	}

	public function revokeAccessToken($tokenId) {
		$this->api->accessTokens->revokeToken($tokenId);
	}

	public function isAccessTokenRevoked($tokenId) {
		return $this->api->accessTokens->isRevoked($tokenId);
	}

	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null) {
		$accessToken = new AccessTokenEntity();
		$accessToken->setClient($clientEntity);
		foreach ($scopes as $scope) {
			$accessToken->addScope($scope);
		}
		$accessToken->setUserIdentifier($userIdentifier);
		return $accessToken;
	}
}
