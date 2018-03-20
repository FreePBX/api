<?php

namespace FreePBX\modules\Api\Gql;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\Type;

use GraphQL\Server\StandardServer;

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use FreePBX\modules\Api\Oauth\Repositories\AccessTokenRepository;
use League\OAuth2\Server\ResourceServer;

use DirectoryIterator;

use Slim\App;

class Api {
	private $classes;

	public function __construct($freepbx, $publicKey) {
		$this->freepbx = $freepbx;
		$this->publicKey = $publicKey;
	}

	public function getValidScopes() {
		$modules = $this->getAPIClasses();
		$scopes = [];
		foreach($modules as $module => $classes) {
			$scopes[$module] = [];
			foreach($classes as $class) {
				$scopes[$module] = array_merge($scopes[$module],$class->getScopes());
			}
		}
		return $scopes;
	}

	public function execute() {
		$_SERVER['QUERY_STRING'] = str_replace('module=api&command='.$_GET['command'].'&route='.$_GET['route'],'',$_SERVER['QUERY_STRING']);
		$_SERVER['REQUEST_URI'] = '/api/gql'.(!empty($_GET['route']) ? '/'.$_GET['route'] : '');

		$config = [
			'settings' => [
				'displayErrorDetails' => !empty($_REQUEST['debug']),
			]
		];

		$accessTokenRepository = new AccessTokenRepository($this->freepbx->api);
		$publicKeyPath = 'file://' . $this->publicKey;
		$server = new ResourceServer(
			$accessTokenRepository,
				$publicKeyPath
		);

		$app = new App($config);

		$app->add(new ResourceServerMiddleware($server));

		$container = $app->getContainer();
		$container['setupGql'] = $container->protect(function($request, $response, $args) {
			return $this->setupGql($request, $response, $args);
		});

		$app->post('/api/gql', function ($request, $response, $args) {
			$server = new StandardServer([
				'schema' => call_user_func($this->setupGql, $request, $response, $args)
			]);
			$server->processPsrRequest($request, $response, $response->getBody());
		});
		$app->run();

	}

	public function getAPIClasses() {
		if(empty($this->classes)) {
			$webrootpath = $this->freepbx->Config->get('AMPWEBROOT');

			$this->objectReferences = new TypeStore();

			$fwcpath = $webrootpath . '/admin/libraries/Api/Gql';
			foreach (new DirectoryIterator($fwcpath) as $fileInfo) {
				if($fileInfo->isDot()) { continue; };
				$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
				$class = "FreePBX\\Api\\Gql\\".$name;
				$this->classes['framework'][$name] = new $class($this->freepbx,$this->objectReferences);
			}

			$amodules = $this->freepbx->Modules->getActiveModules();
			foreach($amodules as $module){
				//Module Path
				$mpath = $webrootpath . '/admin/modules/' . $module['rawname'] . '/Api/Gql/';
				if (file_exists($mpath)){
					//Class files
					foreach (new DirectoryIterator($mpath) as $fileInfo) {
						if($fileInfo->isDot()) { continue; };
						$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
						$class = "FreePBX\\modules\\".$module['rawname']."\\Api\\Gql\\".$name;
						$this->classes[$module['rawname']][$name] = new $class($this->freepbx,$this->objectReferences);
					}
				}
			}
			return $this->classes;
		} else {
			return $this->classes;
		}
	}

	private function setupGql($request, $response, $args) {

		//dbug(array_keys($request->getAttributes()));

		$allowedScopes = $request->getAttribute('oauth_scopes');
		$userId = $request->getAttribute('oauth_user_id');

		$classes = $this->getAPIClasses($allowedScopes);

		$this->initalizeReferences();

		$queryFields = [];
		$mutationFields = [];
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$query = $object->constructQuery();
				foreach($query as &$q) {
					if(isset($q['type']) && is_string($q['type'])) {
						$q = $this->replaceTypes($q);
					}
				}
				$queryFields = array_merge($queryFields,$query);
			}
		}

		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$mutation = $object->constructMutation();
				foreach($mutation as &$q) {
					if(isset($q['type']) && is_string($q['type'])) {
						$q = $this->replaceTypes($q);
					}
				}
				$mutationFields = array_merge($mutationFields,$mutation);
			}
		}

		$queryType = new ObjectType([
			'name' => 'Query',
			'fields' => $queryFields
		]);

		$mutationType = null;
		if(!empty($mutationFields)) {
			$mutationType = new ObjectType([
				'name' => 'Mutation',
				'fields' => $mutationFields
			]);
		}

		$schema = new Schema([
			'query' => $queryType,
			'mutation' => $mutationType
		]);

		return $schema;
	}

	private function initalizeReferences() {
		$classes = $this->getAPIClasses();
		$fieldTypes = [];
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$object->initReferences();
			}
		}
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$object->postInitReferences();
			}
		}
	}

	private function generateQuery() {

	}

	private function replaceTypes($q) {
		if(isset($q['type']) && is_string($q['type'])) {
			if(preg_match('/^objectReference-(.*)/',$q['type'],$matches)) {
				if(!$this->objectReferences->get($matches[1])->isObject()) {
					$fields = $this->objectReferences->get($matches[1])->getFieldsArray();
					foreach($fields as &$a) {
						$a = $this->replaceTypes($a);
					}
					$this->objectReferences->get($matches[1])->replaceFields($fields);
				}
				$q['type'] = $this->objectReferences->get($matches[1])->getObject();
			}
			if(preg_match('/^objectListReference-(.*)/',$q['type'],$matches)) {
				if(!$this->objectReferences->get($matches[1])->isObject()) {
					$fields = $this->objectReferences->get($matches[1])->getFieldsArray();
					foreach($fields as &$a) {
						$a = $this->replaceTypes($a);
					}
					$this->objectReferences->get($matches[1])->replaceFields($fields);
				}
				$q['type'] = Type::listOf($this->objectReferences->get($matches[1])->getObject());
			}
		}
		return $q;
	}
}
