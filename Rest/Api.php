<?php

namespace FreePBX\modules\Api\Rest;

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use FreePBX\modules\Api\Oauth\Repositories\AccessTokenRepository;
use League\OAuth2\Server\ResourceServer;

use DirectoryIterator;

use Slim\App;

class Api {
	public function __construct($freepbx, $publicKey) {
		$this->freepbx = $freepbx;
		$this->publicKey = $publicKey;
	}

	public function getValidScopes() {
		$modules = $this->getAPIClasses();
		$scopes = [];
		foreach($modules as $module => $classes) {
			foreach($classes as $class) {
				$scopes = array_merge($scopes,$class->getScopes());
			}
		}
		return $scopes;
	}

	public function execute() {
		$_SERVER['QUERY_STRING'] = str_replace('module=api&command='.$_GET['command'].'&route='.$_GET['route'],'',$_SERVER['QUERY_STRING']);
		$_SERVER['REQUEST_URI'] = '/api/rest'.(!empty($_GET['route']) ? '/'.$_GET['route'] : '');

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

		$container = $app->getContainer();

		$app->add(new ResourceServerMiddleware($server));

		$container = $app->getContainer();
		$container['setupRest'] = $container->protect(function($app) {
			return $this->setupRest($app);
		});

		$app->group('/api/rest', function () {
			$this->setupRest($this);
		});
		$app->run();
	}

	public function getAPIClasses() {
		if(empty($this->classes)) {
			$webrootpath = $this->freepbx->Config->get('AMPWEBROOT');

			$fwcpath = $webrootpath . '/admin/libraries/Api/Rest';
			foreach (new DirectoryIterator($fwcpath) as $fileInfo) {
				if($fileInfo->isDot()) { continue; };
				$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
				$class = "FreePBX\\Api\\Rest\\".$name;
				$this->classes['framework'][$name] = new $class($this->freepbx);
			}

			$amodules = $this->freepbx->Modules->getActiveModules();
			foreach($amodules as $module){
				//Module Path
				$mpath = $webrootpath . '/admin/modules/' . $module['rawname'] . '/Api/Rest/';
				if (file_exists($mpath)){
					//Class files
					foreach (new DirectoryIterator($mpath) as $fileInfo) {
						if($fileInfo->isDot()) { continue; };
						$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
						$class = "FreePBX\\modules\\".$module['rawname']."\\Api\\Rest\\".$name;
						$this->classes[$module['rawname']][$name] = new $class($this->freepbx);
					}
				}
			}
			return $this->classes;
		} else {
			return $this->classes;
		}
	}

	private function setupRest($app) {
		$classes = $this->getAPIClasses();
		foreach($classes as $module => $objects) {
			$app->group('/'.$module, function () use ($objects) {
				foreach($objects as $name => $object) {
					$object->setupRoutes($this);
				}
			});
		}
	}
}
