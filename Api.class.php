<?php

//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013-2015 Sangoma Technologies Inc.
//
namespace FreePBX\modules;
include __DIR__."/vendor/autoload.php";

use FreePBX\modules\Api\Gql;
use FreePBX\modules\Api\Rest;
use FreePBX\modules\Api\Oauth\Oauth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Api extends \FreePBX_Helpers implements \BMO {
	private $oauthKey = 'api_oauth';
	private $flattenedScopes = [];
	private static $gqlApi = false;

	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->freepbx = $freepbx;
	}

	public function getAPIAddress() {
		$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		return $protocol.'://'.preg_replace('/:\d+/', '', $_SERVER['HTTP_HOST'])."/admin";
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

	private function getPorts_api(){
		$res = [];
		$ports = \FreePBX::Sysadmin()->getPorts();
		if (isset($ports['restapi'])) {
			$res["API"]["HTTP"] = $ports['restapi'];
		}
		if (isset($ports['sslrestapi'])) {
			$res["API"]["HTTPS"] = $ports['sslrestapi'];
		}
		if (isset($ports['acp'])) {
			$res["ACP"]["HTTP"] = $ports['acp'];
		}
		if (isset($ports['sslacp'])) {
			$res["ACP"]["HTTPS"] = $ports['sslacp'];
		}
		return $res;
	}

	public function sysadmin_info(){
		$module = \module_functions::create();
		$result = $module->getinfo('sysadmin', MODULE_STATUS_ENABLED);
		return (empty($result["sysadmin"])) ? '' : $result;
	}

	public function showPage() {
		$sa = $this->sysadmin_info();
		$apiPorts["sa"] = "disabled";
		if(!empty($sa)){
			$apiPorts = $this->getPorts_api();
			$apiPorts["sa"] = "enabled";
		}
		return load_view(__DIR__."/views/system/overview.php",["url" => $this->getAPIAddress(), "data_api" => $apiPorts] );
	}

	public function doConfigPageInit($page) {
	}

	/* Assorted stubs to validate the BMO Interface */
	public function install() {
		$logdir = $this->freepbx->Config->get("ASTLOGDIR");
		if(!file_exists($logdir."/gql_api_error.log")){
			touch($logdir."/gql_api_error.log");
			chown($logdir."/gql_api_error.log", "asterisk");
			chgrp($logdir."/gql_api_error.log", "asterisk");
			out("log file created ".$logdir."/gql_api_error.log");
		}
		if($this->freepbx->Modules->checkStatus("sysadmin")) {
			touch("/var/spool/asterisk/incron/api.logrotate");
		}
		$this->freepbx->PKCS->generateKey($this->oauthKey);
		$this->freepbx->PKCS->extractPublicKey($this->oauthKey);

		$this->freepbx->Pm2->installNodeDependencies(__DIR__."/node",function($data) {
			outn($data);
		});
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
		$files[] = array(
			'type' => 'file',
			'path' => __DIR__."/node/index.js",
			'perms' => 0775
		);
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
			case "getJSTreeScopes":
			case "generatedocs":
			case "getAccessToken":
				return true;
			break;
		}
	}

	public function ajaxHandler(){
		switch($_REQUEST['command']) {
			case "getAccessToken":
				$token = $this->getDeveloperAccessToken($_POST['scopes'], $_POST['host']);
				return ["status" => true, "token" => $token];
			break;
			case "generatedocs":
				$this->generateDocumentation($_POST['scopes'], $_POST['host']);
				return ["status" => true];
			break;
			case "getJSTreeScopes":
				return $this->getJSTreeScopes();
			break;
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
				$res = $this->applications->add((!empty($_POST['user']) ? $_POST['user'] : null),$_POST['type'],$_POST['name'],$_POST['description'],$_POST['website'],$_POST['redirect'],$_POST['allowed_scopes']);
				return ["status" => true, "type" => $res['type'], "owner" => $res['owner'], "client_id" => $res['client_id'], "client_secret" => $res['client_secret'], "id" => $res['id'], "allowed_scopes" => $res['allowed_scopes']];
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
				'description' => sprintf(_("Read/Write for all modules [%s]"),$this->niceType('gql')),
				'typeName' => $this->niceType('gql'),
				'type' => 'gql',
				'module' => null,
				'moduleName' => null
			],
			'rest' => [
				'description' => sprintf(_("Read/Write for all modules [%s]"),$this->niceType('rest')),
				'typeName' => $this->niceType('rest'),
				'type' => 'rest',
				'module' => null,
				'moduleName' => null
			]
		];

		$activeModules = $this->freepbx->Modules->getActiveModules();

		foreach(['rest','gql'] as $type) {
			if(!empty($validScopes[$type])) {
				foreach($validScopes[$type] as $module => $scope) {
					$scopes[$type.':'.$module] = [
						'description' => sprintf(_("Read/Write for %s"),$activeModules[$module]['name']),
						'type' => $type,
						'typeName' => $this->niceType($type),
						'module' => $module,
						'moduleName' => $activeModules[$module]['name'],
						'moduleDescription' => !empty($activeModules[$module]['description']) ? $activeModules[$module]['description'] : '',
					];
					$defaultTypes = ['read','write'];
					foreach($defaultTypes as $default) {
						$scopes[$type.':'.$module.':'.$default] = [
							'description' => sprintf(_("All %s for %s"),$default,$activeModules[$module]['name']),
							'type' => $type,
							'typeName' => $this->niceType($type),
							'module' => $module,
							'moduleName' => $activeModules[$module]['name'],
							'moduleDescription' => !empty($activeModules[$module]['description']) ? $activeModules[$module]['description'] : '',
						];
					}

					foreach($scope as $scopeKey => $scopeData) {
						$parts = explode(":",$scopeKey);

						//Write out scopes we dont know about
						if(!isset($scopes[$type.':'.$module.':'.$parts[0]])) {
							$scopes[$type.':'.$module.':'.$parts[0]] = [
								'description' => sprintf(_("All %s for %s"),$parts[0],$activeModules[$module]['name']),
								'type' => $type,
								'typeName' => $this->niceType($type),
								'module' => $module,
								'moduleName' => $activeModules[$module]['name'],
								'moduleDescription' => !empty($activeModules[$module]['description']) ? $activeModules[$module]['description'] : '',
							];
						}

						$scopeData['module'] = $module;
						$scopeData['moduleName'] = $activeModules[$module]['name'];
						$scopeData['moduleDescription'] = !empty($activeModules[$module]['description']) ? $activeModules[$module]['description'] : '';
						$scopeData['typeName'] = $this->niceType($type);
						$scopeData['type'] = $type;
						$scopes[$type.':'.$module.':'.$scopeKey] = $scopeData;
					}
				}
			}
		}
		$this->flattenedScopes = $scopes;
		return $this->flattenedScopes;
	}

	private function niceType($type) {
		return ($type === 'gql') ? 'GraphQL' : 'REST';
	}

	public function getJSTreeScopes() {
		$result = array();

		foreach($this->getFlattenedScopes() as $path => $value) {
			$temp = &$result;

			foreach(explode(':', $path) as $key) {
				if(!in_array($key,['description','gql','rest'])) {
					$temp =& $temp['children'][$key];
				} else {
					$temp =& $temp[$key];
				}

			}
			$value['id'] = $path;
			$value['text'] = $value['description'];
			unset($value['module'],$value['description']);
			$temp = $value;
		}

		$fix = function ($array) use(&$fix) {
			foreach($array as $key => &$value) {
				if(isset($value['children'])) {
					$value['children'] = array_values($value['children']);
				}
				if(is_array($value)) {
					$value = $fix($value);
				}
			}
			return $array;
		};

		return array_values($fix($result));
	}

	public function isScopeValid($scope) {
		return isset($this->getFlattenedScopes()[$scope]);
	}

	public function getVisualScopes($filter=[]) {
		$scopes = $this->getFlattenedScopes();
		$visual = [];
		$activeModules = $this->freepbx->Modules->getActiveModules();
		foreach($scopes as $scopeKey => $scope) {
			if(in_array($scopeKey,$filter)) {
				if(!empty($scope['module'])) {
					$scope['modData'] = [
						"name" => $activeModules[$scope['module']]['name'],
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

	public function getDeveloperAccessToken($scope, $host='http://localhost') {
		$devApplication = $this->getConfig("devApplication");
		if(empty($devApplication['clientId']) || empty($devApplication['clientSecret']) || empty($this->applications->getByClientId($devApplication['clientId']))) {
			$application = $this->applications->add(null,'client_credentials','GQL Developer Explorer','Used for the GraphQL Documentation and GraphQL Explorer tabs');

			$devApplication = [
				"clientId" => $application['client_id'],
				'clientSecret' => $application['client_secret']
			];
		}

		//TODO: need to figure out a way to validate tokens
		if(empty($devApplication['accessToken']) || empty($this->accessTokens->get($devApplication['accessToken']['access_token'])) || time() > $devApplication['accessToken']['expires'] || $scope !== $devApplication['accessToken']['scope']) {
			$provider = new \League\OAuth2\Client\Provider\GenericProvider([
					'clientId'                => $devApplication['clientId'],    // The client ID assigned to you by the provider
					'clientSecret'            => $devApplication['clientSecret'],    // The client password assigned to you by the provider
					'redirectUri'             => 'http://my.example.com/your-redirect-url/',
					'urlAuthorize'            => $host.'/admin/api/api/authorize',
					'urlAccessToken'          => $host.'/admin/api/api/token',
					'urlResourceOwnerDetails' => $host.'/admin/api/api/resource',
					'verify'                  => false
			]);

			$options = [
				'scope' => $scope
			];

			$accessToken = $provider->getAccessToken('client_credentials', $options);
			$devApplication['accessToken'] = json_decode(json_encode($accessToken),true);
			$devApplication['accessToken']['scope'] = $scope;
			$this->setConfig("devApplication",$devApplication);
		}

		return $devApplication['accessToken']['access_token'];
	}

	public function generateDocumentation($scope, $host='http://localhost') {
		$ht = file_get_contents(__DIR__."/docs.htaccess");

		$ht = str_replace('%ipaddress%',$_SERVER['REMOTE_ADDR'],$ht);
		$process = new Process (['rm', '-Rf', __DIR__.'/docs']);
		$process->mustRun();

		$process = new Process(['NODE_TLS_REJECT_UNAUTHORIZED=0','node', __DIR__.'/node/index.js', '-e', $host.'/admin/api/api/gql', '-o', __DIR__.'/docs', '-x', 'Authorization: Bearer '.$this->getDeveloperAccessToken($scope, $host)]);
		$process->mustRun();

		file_put_contents(__DIR__."/docs/.htaccess",$ht);
	}

	public function setTransactionStatus($transactionId,$status,$failureReason) {
		$this->transactionStatus = new Api\Includes\TransactionStatus($this->freepbx->Database);
		return $this->transactionStatus->updateStatus($transactionId,$status,$failureReason);	
	}

	public function addTransaction($status,$moduleName,$eventName) {
		$this->transactionStatus = new Api\Includes\TransactionStatus($this->freepbx->Database);
		return $this->transactionStatus->add($status,$moduleName,$eventName);	
	}

	public function getTransactionStatus($txnId) {
		$this->transactionStatus = new Api\Includes\TransactionStatus($this->freepbx->Database);
		$response = $this->transactionStatus->get($txnId);	
		return $response;
	}

		//injecting for utest
	public function setObj($obj){
		self::$gqlApi = $obj;
	}

	public function setGqlApiHelper() {
		if(!self::$gqlApi){
			 self::$gqlApi = \FreePBX::Api();	
		}
		return self::$gqlApi;
	}

	// run as background job	
	public function initiateGqlAPIProcess($args) {
		$bin = $this->freepbx->Config()->get('AMPSBIN');
		shell_exec($bin.'/fwconsole api gql '.$args[0].' '.$args[1].' '.$args[2].' '.$args[3].' >/dev/null 2>/dev/null &');
	}
	
	/**
	 * doreload
	 *
	 * @param  mixed $txnId
	 * @return void
	 */
	public function doreload($txnId){
		$bin = $this->freepbx->Config()->get('AMPSBIN');
		shell_exec($bin.'/fwconsole api doreload '.$txnId.' >/dev/null 2>/dev/null &');
	}

	public function writelog($msg) {
		$log_dir = $this->freepbx->Config->get("ASTLOGDIR");
		$date = date("Y-m-d H:i:s",strtotime("now"));
		error_log($date." - ".$msg."\n", 3, $log_dir."/gql_api_error.log");
	}
}
