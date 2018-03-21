<?php

namespace FreePBX\modules\Api\Includes;

use PDO;

class AuthCodes {
	public function __construct($database) {
		$this->database = $database;
	}

	public function add($code, $app_id, $ipAddress, $scopes=[], $expiry=null, $uid=null) {
		$sql = "INSERT INTO api_auth_codes (`code`,`aid`,`expiry`,`scopes`,`uid`, `ip_address`, `last_accessed`) VALUES (:code,:aid,:expiry,:scopes, :uid, :ip, :time)";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":code" => $code,
			":aid" => $app_id,
			":scopes" => json_encode($scopes),
			":expiry" => $expiry->getTimestamp(),
			":uid" => $uid,
			":ip" => $ipAddress,
			":time" => time()
		]);
	}

	public function getAll() {
		$sql = "SELECT c.*, a.name as app_name FROM api_auth_codes c, api_applications a WHERE c.aid = a.id";
		$sth = $this->database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function revoke($code) {
		return $this->remove($code);
	}

	public function remove($code) {
		$sql = "DELETE FROM api_auth_codes WHERE `code` = :code";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":code" => $code,
		]);
	}

	public function removeByAppId($aid) {
		$sql = "SELECT * FROM api_auth_codes WHERE `aid` = :id";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":id" => $aid
		]);
		$code = $sth->fetch(PDO::FETCH_ASSOC);
		$rf = new RefreshTokens($this->database);
		$rf->removeByAccessToken($code['code']);
		$sql = "DELETE FROM api_auth_codes WHERE `aid` = :id";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":id" => $aid
		]);
	}

	public function updateAccessed($code, $ip_address) {
		$sql = "UPDATE IGNORE api_auth_codes SET last_accessed = :time, ip_address = :ip  WHERE `code` = :code";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":code" => $code,
			":time" => time(),
			":ip" => $ip_address
		]);
	}

	public function get($code) {
		$sql = "SELECT * FROM api_auth_codes WHERE `code` = :code";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":code" => $code,
		]);
		$code = $sth->fetch(PDO::FETCH_ASSOC);
		if(!empty($code)) {
			$code['scopes'] = json_decode($code['scopes'],true);
		}
		return $code;
	}

	public function isRevoked($code) {
		$this->removeExpired();
		return empty($this->get($code));
	}

	private function removeExpired() {
		$sql = "DELETE FROM api_auth_codes WHERE `expiry` <= :time";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":time" => time()
		]);
	}
}
