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
		$sql = "SELECT * FROM api_access_tokens";
		$sth = $this->database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
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
		$rf = new RefreshTokens($this->database);
		$rf->removeByAccessToken($token['token']);
		$sql = "DELETE FROM api_access_tokens WHERE `aid` = :id";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":id" => $aid
		]);
	}

	public function get($token) {
		$sql = "SELECT * FROM api_access_tokens WHERE `token` = :token";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":token" => $token,
		]);
		$token = $sth->fetch(PDO::FETCH_ASSOC);
		if(!empty($token)) {
			$token['scopes'] = json_decode($token['scopes'],true);
		}
		return $token;
	}

	public function isRevoked($token) {
		return empty($this->get($token));
	}
}
