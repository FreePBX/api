<?php

namespace FreePBX\modules\Api\Oauth\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use FreePBX\modules\Api\Oauth\Entities\AuthCodeEntity;

class AuthCodeRepository implements AuthCodeRepositoryInterface {
	private $api;

	public function __construct($api) {
		$this->api = $api;
	}

	public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void {
		$application = $this->api->applications->getByClientId($authCodeEntity->getClient()->getIdentifier());
		$this->api->authCodes->add($authCodeEntity->getIdentifier(), $application['id'], $_SERVER['REMOTE_ADDR'], $authCodeEntity->getScopes(), $authCodeEntity->getExpiryDateTime(), $authCodeEntity->getUserIdentifier());
	}

	public function revokeAuthCode(string $codeId): void {
		$this->api->authCodes->revoke($codeId);
	}

	public function isAuthCodeRevoked(string $codeId): bool {
		if ($this->api->authCodes->isRevoked($codeId)) {
			return true;
		}
		$this->api->authCodes->updateAccessed($codeId, $_SERVER['REMOTE_ADDR']);
		return false;
	}

	public function getNewAuthCode(): AuthCodeEntityInterface {
		return new AuthCodeEntity();
	}
}
