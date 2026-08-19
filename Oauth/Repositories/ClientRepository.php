<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\ClientEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class ClientRepository implements ClientRepositoryInterface {
	public $api = null;
	public function __construct($api) {
		$this->api = $api;
	}

	public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface {
		$application = $this->api->applications->getByClientId($clientIdentifier);
		if (empty($application)) {
			return null;
		}
		$application['redirect_uri'] = 'http://my.example.com/your-redirect-url/';
		$c = new ClientEntity();
		$c->setIdentifier($clientIdentifier);
		$c->setName($application['name']);
		$c->setRedirectUri($application['redirect_uri']);
		// Confidential clients have a client secret (all grant types except implicit/browser)
		$c->setConfidential(($application['grant_type'] ?? '') !== 'implicit' && ($application['grant_type'] ?? '') !== 'browser');
		return $c;
	}

	public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool {
		return $this->api->applications->authenticate($clientIdentifier, $clientSecret) === true;
	}
}
