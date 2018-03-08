<?php

//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013-2015 Sangoma Technologies Inc.
//
namespace FreePBX\modules;
include __DIR__."/vendor/autoload.php";

use FreePBX\modules\Gqlapi\includes\Graphqlapi;
use Slim\App;
use League\OAuth2\Server\AuthorizationServer;
use FreePBX\modules\Gqlapi\Oauth;

class Gqlapi implements \BMO {
	private $classes = [];
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->freepbx = $freepbx;
	}

	/* Assorted stubs to validate the BMO Interface */
	public function install() {

	}

	public function uninstall() {

	}

	public function backup() {
	}
	public function restore($config) {
	}


	public function ajaxRequest($req, &$setting) {
		switch($req) {
			case "access_token":
			case "api":
				$setting['authenticate'] = false;
				$setting['allowremote'] = true;
				return true;
			break;
		}
	}

	public function ajaxHandler(){

	}

	public function ajaxCustomHandler() {
		switch($_REQUEST['command']) {
			case "api":
				$gql = new Graphqlapi($this->freepbx);
				$gql->execute();
				return true;
			break;
			case "access_token":
				$_SERVER['QUERY_STRING'] = str_replace('module=gqlapi&command='.$_GET['command'].'&restofpath='.$_GET['restofpath'],'',$_SERVER['QUERY_STRING']);
				$_SERVER['REQUEST_URI'] = '/'.$_GET['command'].(!empty($_GET['restofpath']) ? '/'.$_GET['restofpath'] : '');

				/**
				 * 	--data-urlencode "grant_type=password" \
				 *	--data-urlencode "client_id=myawesomeapp" \
				 *	--data-urlencode "client_secret=abc123" \
				 *	--data-urlencode "username=alex" \
				 *	--data-urlencode "password=whisky" \
				 *	--data-urlencode "scope=basic email"
				 */

				$config = [
					'settings' => [
						'displayErrorDetails' => true,
					],
					AuthorizationServer::class => function () {
						// Setup the authorization server
						$server = new \League\OAuth2\Server\AuthorizationServer(
								new Oauth\ClientRepository(),                 // instance of ClientRepositoryInterface
								new Oauth\AccessTokenRepository(),            // instance of AccessTokenRepositoryInterface
								new Oauth\ScopeRepository(),                  // instance of ScopeRepositoryInterface
								'file://' . __DIR__ . '/private.key',    // path to private key
								'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'      // encryption key
						);
						$grant = new \League\OAuth2\Server\Grant\PasswordGrant(
								new Oauth\UserRepository(),           // instance of UserRepositoryInterface
								new Oauth\RefreshTokenRepository()    // instance of RefreshTokenRepositoryInterface
						);
						$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month
						// Enable the password grant on the server with a token TTL of 1 hour
						$server->enableGrantType(
								$grant,
								new \DateInterval('PT1H') // access tokens will expire after 1 hour
						);

						$grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant(new Oauth\RefreshTokenRepository());
						$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month

						// Enable the refresh token grant on the server
						$server->enableGrantType(
							$grant,
							new \DateInterval('PT1H') // new access tokens will expire after an hour
						);

						return $server;
					}
				];

				$app = new App($config);
				$app->post('/access_token', function ($request, $response, $args) use ($app) {
					$s = $app->getContainer()->get(AuthorizationServer::class);
					try {

						// Try to respond to the request
						return $s->respondToAccessTokenRequest($request, $response);

					} catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

						// All instances of OAuthServerException can be formatted into a HTTP response
						return $exception->generateHttpResponse($response);

					} catch (\Exception $exception) {

						// Unknown exception
						$body = new Stream('php://temp', 'r+');
						$body->write($exception->getMessage());
						return $response->withStatus(500)->withBody($body);

					}
				});
				$app->run();
				return true;
			break;
		}
		return false;
	}


}
