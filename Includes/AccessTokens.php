<?php

namespace FreePBX\modules\Api\Includes;

use PDO;

class AccessTokens {
	public function __construct($database) {
		$this->database = $database;
	}

	public function add($token, $app_id, $ipAddress, $scopes=[], $expiry=null, $uid=null) {
		$sql = "INSERT INTO api_access_tokens (`token`,`aid`,`expiry`,`scopes`,`uid`, `ip_address`, `last_accessed`) VALUES (:token,:aid,:expiry,:scopes, :uid, :ip, :time)";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":token" => $token,
			":aid" => $app_id,
			":scopes" => json_encode($scopes),
			":expiry" => $expiry->getTimestamp(),
			":uid" => $uid,
			":ip" => $ipAddress,
			":time" => time()
		]);
	}

	public function getAll() {
		$sql = "SELECT t.*, a.name as app_name FROM api_access_tokens t, api_applications a WHERE t.aid = a.id";
		$sth = $this->database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function revoke($token) {
		return $this->remove($token);
	}

	public function remove($token) {
		$sql = "DELETE FROM api_access_tokens WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":token" => $token,
		]);
	}

	public function removeByAppId($aid) {
		$sql = "SELECT * FROM api_access_tokens WHERE `aid` = :id";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":id" => $aid
		]);
		$token = $sth->fetch(PDO::FETCH_ASSOC);
		if(is_array($token)){
			$rf = new RefreshTokens($this->database);
			$rf->removeByAccessToken($token['token']);
			$sql = "DELETE FROM api_access_tokens WHERE `aid` = :id";
			$sth = $this->database->prepare($sql);
			$sth->execute([
				":id" => $aid
			]);
		}
	}

	public function updateAccessed($token, $ip_address) {
		$sql = "UPDATE IGNORE api_access_tokens SET last_accessed = :time, ip_address = :ip  WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":token" => $token,
			":time" => time(),
			":ip" => $ip_address
		]);
	}

	public function get($token) {
		$sql = "SELECT * FROM api_access_tokens WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":token" => $token,
		]);
		$tokenResult = $sth->fetch(PDO::FETCH_ASSOC);
		if(!empty($tokenResult)) {
			$tokenResult['scopes'] = json_decode($tokenResult['scopes'], true);
		}
		return $tokenResult;
	}

	public function isRevoked($token) {
		$this->removeExpired();
		return empty($this->get($token));
	}

	private function removeExpired() {
		$sql = "DELETE FROM api_access_tokens WHERE `expiry` <= :time";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":time" => time()
		]);
	}
}
