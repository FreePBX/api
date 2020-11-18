<?php

namespace FreePBX\modules\Api\Gql;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;

use GraphQLRelay\Relay;

use GraphQL\Server\StandardServer;

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use FreePBX\modules\Api\Oauth\Repositories\AccessTokenRepository;
use League\OAuth2\Server\ResourceServer;

use GraphQL\Error\Debug;

use DirectoryIterator;

use Slim\App;

class Api {
	private $classes;
	private $safeMode = false;
	private $restricted = [
		'areminder',
		'framework',
		'announcement',
		'recordings',
		'blacklist',
		'arimanager',
		'callback',
		'callerid',
		'callforward',
		'callwaiting',
		'cdr',
		'core',
		'music',
		'parking',
		'parkpro'
	];

	public function __construct($freepbx, $publicKey) {
		$this->freepbx = $freepbx;
		$this->publicKey = $publicKey;
	}

	public function getValidScopes() {
		$modules = $this->getAPIClasses();
		$scopes = [];
		foreach($modules as $info) {
			if(!isset($scopes[$info['modname']])) {
				$scopes[$info['modname']] = [];
			}
			$scopes[$info['modname']] = array_merge($scopes[$info['modname']],$info['object']->getScopes());
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
				'schema' => call_user_func($this->setupGql, $request, $response, $args),
				'debug' => true
			]);
	
			$newResponse = $server->processPsrRequest($request, $response, $response->getBody());

			//handling the exception error response
			if (isset(json_decode($newResponse->getBody())->errors[0])) {
				$data['errors'][] = array("message" => json_decode($newResponse->getBody())->errors[0]->message,"status"=> false);
				return  $response->withJson($data, 400);
			}//handling the error response defined 
			elseif (isset(json_decode($newResponse->getBody())->data)) {
				$value = key(json_decode($newResponse->getBody())->data);
				if($value == 'extension' || $value == 'allExtensions') return;
				$res = json_decode($newResponse->getBody())->data->$value;
				//checking for the error case where status is false
				if (json_decode($res->status) == false) {
					$httpCode = 400; 
					$status = array("status"=> $res->status);
					if (isset($res->message)) {
						$message = array("message" => $res->message);
						$data['errors'][] = array_merge($message,$status);
					} else {
						$data['errors'][] = $status;
					}
				    return  $response->withJson($data, $httpCode);
				}
			}
			//default when proper response is true
			return $newResponse;
		});
		$app->run();

	}

	private function getUnusedPriority($array,$wantedPriority) {
		if(isset($array[$wantedPriority])) {
			while(true) {
				$wantedPriority = (int)$wantedPriority+1;
				if(!isset($array[$wantedPriority])) {
					break;
				}
			}
		}
		return $wantedPriority;
	}

	public function getAPIClasses() {
		if(empty($this->classes)) {
			$webrootpath = $this->freepbx->Config->get('AMPWEBROOT');

			$this->objectReferences = new TypeStore();

			$fwcpath = $webrootpath . '/admin/libraries/Api/Gql';

			$classes = [];

			foreach (new DirectoryIterator($fwcpath) as $fileInfo) {
				if($fileInfo->isDot()) { continue; };
				$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
				$class = "FreePBX\\Api\\Gql\\".$name;
				$pri = $this->getUnusedPriority($classes,$class::getPriority());
				$classes[$pri] = [
					'modname' => 'framework',
					'class' => $class,
					'name' => $name
				];
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
						$pri = $this->getUnusedPriority($classes,$class::getPriority());
						$classes[$pri] = [
							'modname' => $module['rawname'],
							'class' => $class,
							'name' => $name
						];
					}
				}
			}
			ksort($classes);
			foreach($classes as $class) {
				$cls = $class['class'];
				$class['object'] = new $cls($this->freepbx,$this->objectReferences,$class['modname']);
				$this->classes[] = $class;
			}
			return $this->classes;
		} else {
			return $this->classes;
		}
	}

	private function setupGql($request, $response, $args) {

		$allowedScopes = $request->getAttribute('oauth_scopes');
		$userId = $request->getAttribute('oauth_user_id');

		$classes = $this->getAPIClasses();
		foreach($classes as $object) {
			$object['object']->setAllowedScopes($allowedScopes);
			$object['object']->setUserId($userId);
		}

		$nodeDefinition = Relay::nodeDefinitions(
			function ($globalId) {
				$idComponents = Relay::fromGlobalId($globalId);
				$node = $this->objectReferences->get($idComponents['type'])->getNode($idComponents['id']);
				$node['gqlType'] = $idComponents['type'];
				return $node;
			},
			function ($object) {
				return $this->objectReferences->get($object['gqlType'])->getObject();
			}
		);

		$this->initalizeTypes($nodeDefinition);

		$queryFields = [];
		$mutationFields = [];

		foreach($classes as $object) {
			if($this->safeMode && !in_array($object['modname'],$this->restricted)) {
				continue;
			}
			$query = $object['object']->queryCallback();
			if(is_callable($query)) {
				$tmp = $query();
				if(!is_array($tmp)) {
					continue;
				}
				$queryFields = array_merge($queryFields,$tmp);
			}
		}

		foreach($classes as $object) {
			$mutation = $object['object']->mutationCallback();
			if(is_callable($mutation)) {
				$tmp = $mutation();
				if(!is_array($tmp)) {
					continue;
				}
				$mutationFields = array_merge($mutationFields,$tmp);
			}
		}

		$queryFields['node'] = $nodeDefinition['nodeField'];

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

	private function initalizeTypes($nodeDefinition) {
		$classes = $this->getAPIClasses();
		$fieldTypes = [];
		foreach($classes as $object) {
			if($this->safeMode && !in_array($object['modname'],$this->restricted)) {
				continue;
			}
			$object['object']->setNodeDefinition($nodeDefinition);
			$object['object']->initializeTypes();
		}
		foreach($classes as $object) {
			$object['object']->postInitializeTypes();
		}
	}

	private function generateQuery() {

	}
}
