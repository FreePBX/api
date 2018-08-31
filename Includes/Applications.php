<?php

namespace FreePBX\modules\Api\Includes;

use PDO;

class Applications {
	private $secretHashAlgo = 'sha256';

	public function __construct($database) {
		$this->database = $database;
	}

	public function regenerate($owner, $client_id) {
		$app = $this->getByClientId($client_id);
		if(empty($app)) {
			throw new \Exception("The application does not exist");
		}
		$this->remove($owner, $client_id);
		return $this->add($owner,$app['grant_type'],$app['name'],$app['description'],$app['website'],$app['redirect_uri'],$app['allowed_scopes']);
	}

	public function add($ownerid,$type,$name,$description,$website=null,$redirect=null,$allowed_scopes=null) {
		switch($type) {
			case "implicit": //Authorization Code Grant (Implicit) //implicit
				$client_id = bin2hex(random_bytes(32));
				$client_secret = null;
			break;
			case "authorization_code": //Authorization Code Grant (Explicit) //authorization_code
			case "password": //Password Grant //password
			case "client_credentials": //Client Credentials Grant //client_credentials
				$client_id = bin2hex(random_bytes(32));
				$client_secret = bin2hex(random_bytes(16));
			break;
			default:
				//refresh_token
				throw new \Exception("Invalid Grant Type");
			break;
		}
		$sql = "INSERT INTO api_applications (`owner`,`name`,`description`,`grant_type`,`client_id`,`client_secret`,`redirect_uri`,`website`,`algo`,`allowed_scopes`) VALUES (:owner,:name,:description,:type,:client_id,:client_secret,:redirect_uri,:website,:algo,:allowed_scopes)";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":owner" => $ownerid,
			":name" => $name,
			":description" => $description,
			":type" => $type,
			":client_id" => $client_id,
			":client_secret" => ($type !== "browser") ? hash($this->secretHashAlgo, $client_secret) : null,
			":redirect_uri" => $redirect,
			":website" => $website,
			":algo" => $this->secretHashAlgo,
			":allowed_scopes" => $allowed_scopes
		]);
		return ["client_id" => $client_id, "owner" => $ownerid, "type" => $type, "client_secret" => $client_secret, "id" => $this->database->lastInsertId(), "name" => $name, "description" => $description, "allowed_scopes" => $allowed_scopes];
	}

	public function getAll() {
		$sql = "SELECT api_applications.*, userman_users.username FROM api_applications LEFT JOIN userman_users ON api_applications.owner = userman_users.id";
		$sth = $this->database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getAllByOwnerId($owner) {
		if(is_null($owner)) {
			$sql = "SELECT * FROM api_applications WHERE `owner` is null";
			$sth = $this->database->prepare($sql);
		} else {
			$sql = "SELECT * FROM api_applications WHERE `owner` = :owner";
			$sth = $this->database->prepare($sql);
			$sth->bindParam(':owner', $owner);
		}
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getByClientId($client_id) {
		$sql = "SELECT * FROM api_applications WHERE `client_id` = :id";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":id" => $client_id
		]);
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function authenticate($client_id,$client_secret) {
		$token = $this->getByClientId($client_id);
		if(empty($token)) {
			return false;
		}
		if($token['grant_type'] === "browser") {
			return true;
		}
		if(hash($token['algo'], $client_secret) !== $token['client_secret']) {
			return false;
		}
		return true;
	}

	public function remove($owner, $client_id) {
		if(is_null($owner)) {
			$sql = "SELECT * FROM api_applications WHERE `owner`is NULL AND `client_id` = :id";
			$sth = $this->database->prepare($sql);
		} else {
			$sql = "SELECT * FROM api_applications WHERE `owner` = :owner AND `client_id` = :id";
			$sth = $this->database->prepare($sql);
			$sth->bindParam(':owner', $owner);
		}
		$sth->bindParam(':id', $client_id);
		$sth->execute();
		$application = $sth->fetch(PDO::FETCH_ASSOC);
		if(empty($application)) {
			return false;
		}
		$at = new AccessTokens($this->database);
		$at->removeByAppId($application['id']);
		$sql = "DELETE FROM api_applications WHERE `client_id` = :id";
		$sth = $this->database->prepare($sql);
		$sth->bindParam(':id', $client_id);
		$sth->execute();
		return true;
	}
}
