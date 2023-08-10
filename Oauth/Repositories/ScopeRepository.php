<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\ScopeEntity;
use League\OAuth2\Server\Exception\OAuthServerException;

class ScopeRepository implements ScopeRepositoryInterface {
	public function __construct($api) {
		$this->api = $api;
	}
	public function getScopeEntityByIdentifier($scopeIdentifier) {
		if ($this->api->isScopeValid($scopeIdentifier) === false) {
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
		$application = $this->api->applications->getByClientId($clientEntity->getIdentifier());
		$applicationAllowedScopes = trim((string) $application['allowed_scopes']);

		// If application scope is empty, then that means there are no restrictions
		if (empty($applicationAllowedScopes)) {
			// Allow access to both GraphQL and Rest
			$applicationScopes = ['gql', 'rest'];
		} else {
			$applicationScopes = explode(" ", $applicationAllowedScopes);
		}

		// If no scopes are defined, then use the scopes from the application
		if (empty($scopes)) {
			foreach($applicationScopes as $scopeIdentifier) {
				$scopes[] = $this->getScopeEntityByIdentifier($scopeIdentifier);
			}
			return $scopes;
		}

		foreach($scopes as $scope) {
			if(!$this->checkScope($scope->getIdentifier(),$applicationScopes)) {
				throw OAuthServerException::invalidScope($scope->getIdentifier());
			}
		}

		return $scopes;
	}

	private function checkScope($scope,$applicationScopes) {
		$parts = explode(":",(string) $scope);
		$scopeString = '';
		foreach($parts as $part) {
			if(empty($scopeString)) {
				$scopeString = $part;
			} else {
				$scopeString .= ':'.$part;
			}
			if(in_array($scopeString,$applicationScopes)) {
				return true;
			}
		}
		return false;
	}
}
