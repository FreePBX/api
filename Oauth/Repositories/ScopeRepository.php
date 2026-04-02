<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\ScopeEntity;
use League\OAuth2\Server\Exception\OAuthServerException;

class ScopeRepository implements ScopeRepositoryInterface {
	public $api=null;
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

		if (empty($application)) {
			throw OAuthServerException::serverError('API application not found for client');
		}

		$applicationAllowedScopes = trim((string) ($application['allowed_scopes'] ?? ''));

		// If application scope is empty, then that means there are no restrictions
		if ($applicationAllowedScopes === '') {
			// Allow access to both GraphQL and Rest
			$applicationScopes = ['gql', 'rest'];
		} else {
			$applicationScopes = array_values(array_filter(array_map('trim', explode(' ', $applicationAllowedScopes))));
		}

		// If no scopes are defined, then use the scopes from the application
		if ($scopes === []) {
			foreach($applicationScopes as $scopeIdentifier) {
				$entity = $this->getScopeEntityByIdentifier($scopeIdentifier);
				if ($entity instanceof ScopeEntityInterface) {
					$scopes[] = $entity;
				}
			}
			return $scopes;
		}

		foreach($scopes as $scope) {
			if (!$scope instanceof ScopeEntityInterface) {
				throw OAuthServerException::invalidScope('');
			}
			if(!$this->checkScope($scope->getIdentifier(),$applicationScopes)) {
				throw OAuthServerException::invalidScope($scope->getIdentifier());
			}
		}

		return $scopes;
	}

	private function checkScope(string $scope, array $applicationScopes): bool {
		$parts = explode(":", $scope);
		$scopeString = '';
		foreach($parts as $part) {
			$scopeString = $scopeString === '' ? $part : $scopeString . ':' . $part;
			if (in_array($scopeString, $applicationScopes, true)) {
				return true;
			}
		}
		return false;
	}
}
