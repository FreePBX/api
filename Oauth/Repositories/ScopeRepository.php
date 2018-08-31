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
		if(empty($application['allowed_scopes'])) {
			//no scope restrictions
			return $scopes;
		}
		$applicationScopes = explode(" ",$application['allowed_scopes']);
		foreach($scopes as $scope) {
			if(!$this->checkScope($scope->getIdentifier(),$applicationScopes)) {
				throw OAuthServerException::invalidScope($scope->getIdentifier());
			}
		}
		return $scopes;
	}

	private function checkScope($scope,$applicationScopes) {
		$parts = explode(":",$scope);
		//all of API type
		if(in_array($parts[0],$applicationScopes)) {
			return true;
		}
		//all of api type + module
		if(in_array($parts[0].":".$parts[1],$applicationScopes)) {
			return true;
		}
		//all of api type + module + write/read
		if(in_array($parts[0].":".$parts[1].":".$parts[2],$applicationScopes)) {
			return true;
		}
		//specific query
		return in_array($scope,$applicationScopes);
	}
}
