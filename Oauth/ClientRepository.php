<?php

namespace FreePBX\modules\Gqlapi\Oauth;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface {
	public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true) {
		$clients = [
			'myawesomeapp' => [
				'secret'          => password_hash('abc123', PASSWORD_BCRYPT),
				'name'            => 'My Awesome App',
				'redirect_uri'    => 'http://foo/bar',
				'is_confidential' => true,
			],
		];
		// Check if client is registered
		if (array_key_exists($clientIdentifier, $clients) === false) {
			return;
		}
		if (
			$mustValidateSecret === true
			&& $clients[$clientIdentifier]['is_confidential'] === true
			&& password_verify($clientSecret, $clients[$clientIdentifier]['secret']) === false
		) {
			return;
		}
		$client = new ClientEntity();
		$client->setIdentifier($clientIdentifier);
		$client->setName($clients[$clientIdentifier]['name']);
		$client->setRedirectUri($clients[$clientIdentifier]['redirect_uri']);
		return $client;
	}
}
