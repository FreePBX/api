<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface {
	public function __construct($api, $scopes) {
		$this->scopes = $scopes;
	}
	public function getScopeEntityByIdentifier($scopeIdentifier) {
		if (array_key_exists($scopeIdentifier, $this->scopes) === false) {
			return;
		}
		$scope = new ScopeEntity();
		$scope->setIdentifier($scopeIdentifier);
		return $scope;
	}

	public function finalizeScopes(
		array $scopes,
		$grantType,
		ClientEntityInterface $clientEntity,
		$userIdentifier = null
	) {
		// Example of programatically modifying the final scope of the access token
		/*
		if ((int) $userIdentifier === 1) {
			$scope = new ScopeEntity();
			$scope->setIdentifier('email');
			$scopes[] = $scope;
		}
		*/
		return $scopes;
	}
}
