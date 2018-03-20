<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\ClientEntity;

class ClientRepository implements ClientRepositoryInterface {
	public function __construct($api) {
		$this->api = $api;
	}
	public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true) {
		$application = $this->api->applications->getByClientId($clientIdentifier);
		if(empty($application)) {
			return;
		}
		if($grantType !== "refresh_token" && $application['grant_type'] !== $grantType) {
			return;
		}
		if (
			$mustValidateSecret === true
			&& $this->api->applications->authenticate($clientIdentifier,$clientSecret) === false
		) {
			return;
		}
		$c = new ClientEntity();
		$c->setIdentifier($clientIdentifier);
		$c->setName($application['name']);
		$c->setRedirectUri($application['redirect_uri']);
		return $c;
	}
}
