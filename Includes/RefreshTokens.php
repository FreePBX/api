<?php

namespace FreePBX\modules\Api\Includes;

use PDO;

class RefreshTokens {
	public function __construct($database) {
		$this->database = $database;
	}

	public function add($token, $access_token, $expiry, $ipAddress) {
		$sql = "INSERT INTO api_refresh_tokens (`token`,`access_token`,`expiry`, `ip_address`, `last_accessed`) VALUES (:token,:access_token,:expiry, :ip, :time)";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":token" => $token,
			":access_token" => $access_token,
			":expiry" => $expiry->getTimestamp(),
			":ip" => $ipAddress,
			":time" => time()
		]);
	}

	public function getAll() {
		$sql = "SELECT rt.*, a.name as app_name FROM api_refresh_tokens rt, api_access_tokens at, api_applications a WHERE rt.access_token = at.token AND at.aid = a.id";
		$sth = $this->database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function revoke($token) {
		return $this->remove($token);
	}

	public function remove($token) {
		$sql = "DELETE FROM api_refresh_tokens WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":token" => $token,
		]);
	}

	public function removeByAccessToken($accessToken) {
		$sql = "DELETE FROM api_refresh_tokens WHERE `access_token` = :access_token";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":access_token" => $accessToken
		]);
	}

	public function updateAccessed($token, $ip_address) {
		$sql = "UPDATE IGNORE api_refresh_tokens SET last_accessed = :time, ip_address = :ip  WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":token" => $token,
			":time" => time(),
			":ip" => $ip_address
		]);
	}

	public function get($token) {
		$sql = "SELECT * FROM api_refresh_tokens WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":token" => $token,
		]);
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function isRevoked($token) {
		$this->removeExpired();
		return empty($this->get($token));
	}

	private function removeExpired() {
		$sql = "DELETE FROM api_refresh_tokens WHERE `expiry` <= :time";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":time" => time()
		]);
	}
}
