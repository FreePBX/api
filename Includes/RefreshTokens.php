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
		$sql = "SELECT * FROM api_refresh_tokens";
		$sth = $this->database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
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

	public function get($token) {
		$sql = "SELECT * FROM api_refresh_tokens WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":token" => $token,
		]);
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function isRevoked($token) {
		return empty($this->get($token));
	}
}
