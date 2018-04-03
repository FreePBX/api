<?php

//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013-2015 Sangoma Technologies Inc.
//
namespace FreePBX\modules;
include __DIR__."/vendor/autoload.php";

use FreePBX\modules\Api\Gql;
use FreePBX\modules\Api\Rest;
use FreePBX\modules\Api\Oauth\Oauth;

class Api implements \BMO {
	private $oauthKey = 'api_oauth';
	private $flattenedScopes = [];

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->freepbx = $freepbx;
	}

	public function __get($var) {
		switch($var) {
			case "gql":
				$location = $this->freepbx->PKCS->getKeysLocation();
				$this->gql = new Gql\Api($this->freepbx,$location.'/'.$this->oauthKey.'_public.key');
				return $this->gql;
			break;
			case "rest":
				$location = $this->freepbx->PKCS->getKeysLocation();
				$this->rest = new Rest\Api($this->freepbx,$location.'/'.$this->oauthKey.'_public.key');
				return $this->rest;
			break;
			case "refreshTokens":
				$this->refreshTokens = new Api\Includes\RefreshTokens($this->freepbx->Database);
				return $this->refreshTokens;
			break;
			case "accessTokens":
				$this->accessTokens = new Api\Includes\AccessTokens($this->freepbx->Database);
				return $this->accessTokens;
			break;
			case "applications":
				$this->applications = new Api\Includes\Applications($this->freepbx->Database);
				return $this->applications;
			break;
			case "authCodes":
				$this->authCodes = new Api\Includes\AuthCodes($this->freepbx->Database);
				return $this->authCodes;
			break;
		}
	}

	public function showPage() {
		return load_view(__DIR__."/views/system/overview.php",[]);
	}

	public function doConfigPageInit($page) {
	}

	/* Assorted stubs to validate the BMO Interface */
	public function install() {
		$this->freepbx->PKCS->generateKey($this->oauthKey);
		$this->freepbx->PKCS->extractPublicKey($this->oauthKey);
		$encryption_key = base64_encode(random_bytes(32)); //client_id as well
	}

	public function uninstall() {

	}

	public function backup() {
	}
	public function restore($config) {
	}

	public function chownFreepbx() {
		$location = $this->freepbx->PKCS->getKeysLocation();
		$files = array();
		foreach(array($this->oauthKey.'.key',$this->oauthKey.'_public.key') as $file) {
			$filename = $location."/".$file;
			$files[] = array('type' => 'file',
				'path' => $filename,
				'perms' => 0600);
		}
		return $files;
	}


	public function ajaxRequest($req, &$setting) {
		switch($req) {
			case "authorize":
				$setting['changesession'] = true;
			case "token":
			case "rest":
			case "gql":
				$setting['authenticate'] = false;
				$setting['allowremote'] = true;
			case "add_application":
			case "remove_refresh_token":
			case "remove_access_token":
			case "remove_application":
			case "regenerate_application":
			case "getApplications":
			case "getTokens":
			case "getRefreshTokens":
			case "getScopes":
				return true;
			break;
		}
	}

	public function ajaxHandler(){
		switch($_REQUEST['command']) {
			case "getScopes":
				$scopes = [];
				foreach($this->getFlattenedScopes() as $key => $scope) {
					$scope['scope'] = $key;
					$scopes[] = $scope;
				}
				return $scopes;
			break;
			case "remove_access_token":
				$this->accessTokens->remove($_POST['id']);
				return ["status" => true];
			break;
			case "remove_refresh_token":
				$this->refreshTokens->remove($_POST['id']);
				return ["status" => true];
			break;
			case "getTokens":
				return $this->accessTokens->getAll();
			break;
			case "getRefreshTokens":
				return $this->refreshTokens->getAll();
			break;
			case "getApplications":
				return $this->applications->getAll();
			break;
			case "add_application":
				$res = $this->applications->add((!empty($_POST['user']) ? $_POST['user'] : null),$_POST['type'],$_POST['name'],$_POST['description'],$_POST['website'],$_POST['redirect']);
				return ["status" => true, "client_id" => $res['client_id'], "client_secret" => $res['client_secret'], "id" => $res['id']];
			break;
			case "remove_application":
				$this->applications->remove((!empty($_POST['user']) ? $_POST['user'] : null),$_POST['client_id']);
				return ["status" => true];
			break;
			case "regenerate_application":
				$res = $this->applications->regenerate((!empty($_POST['user']) ? $_POST['user'] : null),$_POST['client_id']);
				return ["status" => true, "client_id" => $res['client_id'], "client_secret" => $res['client_secret'], "id" => $res['id'], "name" => $res['name'], "description" => $res['description']];
			break;
		}
	}

	public function getScopes() {
		$validScopes = [
			"rest" => $this->rest->getValidScopes(),
			"gql" => $this->gql->getValidScopes()
		];

		return $validScopes;
	}

	public function getFlattenedScopes() {
		if(!empty($this->flattenedScopes)) {
			return $this->flattenedScopes;
		}
		$validScopes = $this->getScopes();
		$scopes = [
			'gql' => [
				'description' => _("All of GraphQL Read Write for all modules"),
				'module' => null
			],
			'rest' => [
				'description' => _("All of Rest Read Write for all modules"),
				'module' => null
			]
		];

		foreach(['rest','gql'] as $type) {
			if(!empty($validScopes[$type])) {
				foreach($validScopes[$type] as $module => $scope) {
					$scopes[$type.':'.$module] = [
						'description' => sprintf(_("All of %s Read Write for %s"),$type,$module),
						'module' => $module
					];
					foreach($scope as $scopeKey => $scopeData) {
						$parts = explode(":",$scopeKey);

						$scopes[$type.':'.$module.':'.$parts[0]] = [
							'description' => sprintf(_("All of %s %s for %s"),$type,$parts[0],$module),
							'module' => $module
						];
						$scopeData['module'] = $module;
						$scopes[$type.':'.$module.':'.$scopeKey] = $scopeData;
					}
				}
			}
		}
		$this->flattenedScopes = $scopes;
		return $this->flattenedScopes;
	}

	public function isScopeValid($scope) {
		return isset($this->getFlattenedScopes()[$scope]);
	}

	public function getVisualScopes($filter=[]) {
		$scopes = $this->getFlattenedScopes();
		$visual = [];
		foreach($scopes as $scopeKey => $scope) {
			if(in_array($scopeKey,$filter)) {
				if(!empty($scope['module'])) {
					$scope['modData'] = [
						"name" => $this->freepbx->Modules->getInfo($scope['module'])[$scope['module']]['name'],
						"description" => ""
					];
				}
				$visual[$scopeKey] = $scope;
			}
		}
		return $visual;
	}

	public function ajaxCustomHandler() {
		switch($_REQUEST['command']) {
			case "rest":
				$this->rest->execute();
				return true;
			break;
			case "gql":
				$this->gql->execute();
				return true;
			break;
			case "authorize":
			case "token":
				$location = $this->freepbx->PKCS->getKeysLocation();
				$oauth = new Oauth($this,$location.'/'.$this->oauthKey.'.key',$this->getFlattenedScopes());
				$oauth->access_token();
				return true;
			break;
		}
		return false;
	}

	public function usermanShowPage() {
		if(isset($_REQUEST['action'])) {
			switch($_REQUEST['action']) {
				case "showuser":
					return [
						[
							"title" => "API",
							"rawname" => "api",
							"content" => load_view(__DIR__.'/views/userman_config.php',["applications" => $this->applications->getAllByOwnerId($_REQUEST['user'])])
						]
					];
				break;
			}
		}
	}

	public function usermanDelGroup($id,$display,$data) {
	}

	public function usermanAddGroup($id, $display, $data) {
	}

	public function usermanUpdateGroup($id,$display,$data) {
	}

	public function usermanAddUser($id, $display, $data) {
	}

	public function usermanUpdateUser($id, $display, $data) {
	}

	public function usermanDelUser($id, $display, $data) {
	}
}
