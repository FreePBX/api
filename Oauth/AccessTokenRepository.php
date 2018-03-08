<?php

namespace FreePBX\modules\Gqlapi\Oauth;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface {
	public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity) {
		// Some logic here to save the access token to a database
	}

	public function revokeAccessToken($tokenId) {
		// Some logic here to revoke the access token
	}

	public function isAccessTokenRevoked($tokenId) {
		return false; // Access token hasn't been revoked
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
