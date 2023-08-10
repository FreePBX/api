<?php

namespace FreePBX\modules\Api\utests;

use FreePBX\modules\Core;
use PHPUnit_Framework_TestCase;
use Exception;
use FreePBX\modules\Api\Gql;
use FreePBX\modules\Api\Oauth\Oauth;
use Slim\Http\Environment;
use Slim\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;

class ApiBaseTestCase extends PHPUnit_Framework_TestCase
{
	protected static $freepbx;
	protected static $api;
	protected $testApplication;
	protected $accessToken;

	public static function setUpBeforeClass()
	{
		self::$freepbx = \FreePBX::create();

		// Simply accessing these attributes instantiates the modules using PHP Magic
		self::$api = self::$freepbx->Api;
	}

	public static function tearDownAfterClass() {
		foreach(self::$api->applications->getAll() as $application) {
			if ($application['name'] === 'Unit Tests') {
				self::$api->applications->remove($application['owner'], $application['client_id']);
			}
		}
	}

	public function setupGqlApp() {
		if ($this->testApplication) {
			return $this->testApplication;
		}

		$this->testApplication = self::$api->applications->add(null, 'client_credentials', 'Unit Tests', 'Used for unit tests');
		return $this->testApplication;
	}

	public function createAccessToken() {
		if ($this->accessToken) {
			return $this->accessToken;
		}

		$application = $this->setupGqlApp();

		$token = 'testAccessToken';
		$expireTimestamp = time() + 1440;

		// cleanup old tests
		self::$api->accessTokens->remove($token);

		$pkcs = \FreePBX::create()->PKCS;
		$location = $pkcs->getKeysLocation();
		$oauthKey = 'api_oauth';
		$privateKey = new CryptKey($location.'/'.$oauthKey.'.key');
		$daKey = new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase());

		$this->accessToken = (new Builder())
			->setAudience('abcdefg')
			->setId($token, true)
			->setIssuedAt(time())
			->setNotBefore(time())
			->setExpiration(time() + 1440)
			->setSubject(null)
			->set('scopes', ['gql'])
			->sign(new Sha256(), $daKey)
			->getToken();

		$ipAddress = '127.0.0.1';
		$scopes = ['gql'];
		$expiry = new \DateTime();
		$expiry->setTimestamp($expireTimestamp);
		self::$api->accessTokens->add($token, $application['id'], $ipAddress, $scopes, $expiry);

		return $this->accessToken;
	}

	public function request($requestData) {
		$_SERVER['QUERY_STRING'] = null;
		$_GET = ["module" => "api", "command" => "gql", "route" => ""];
		$envData = [
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI' => '/api/gql',
			'SERVER_NAME' => 'freepbx.test',
			'CONTENT_TYPE' => 'application/graphql',
		];

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		$accessToken = $this->createAccessToken();

		$pkcs = \FreePBX::create()->PKCS;
		$location = $pkcs->getKeysLocation();

		$oauthKey = 'api_oauth';
		$gqpApi = new Gql\Api(self::$freepbx, $location.'/'.$oauthKey.'_public.key');

		$env = Environment::mock($envData);
		$request = Request::createFromEnvironment($env);
		$request->getBody()->write($requestData);
		$request->getBody()->rewind();
		$request->reparseBody();
		$request = $request->withHeader('authorization', 'Bearer '.$accessToken);
		$app = $gqpApi->buildSlimApp();
		$app->getContainer()['request'] = $request;
		return $app->run(true);
	}
}
