<?php

namespace FreePBX\modules\Api\Oauth;

use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;
use League\OAuth2\Server\AuthorizationServer;
use FreePBX\modules\Api\Oauth\Repositories;
use FreePBX\modules\Api\Oauth\Entities\UserEntity;
use Slim\Http\Stream;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerInterface;

#[\AllowDynamicProperties]
class Oauth {
	public function __construct($api, $privateKey) {
		$this->freepbx = $api->freepbx;
		$this->api = $api;
		$this->privateKey = $privateKey;
	}
	public function access_token() {
		$_SERVER['QUERY_STRING'] = str_replace('module=api&command='.$_GET['command'].'&route='.$_GET['route'],'', $_SERVER['QUERY_STRING']);
		$_SERVER['REQUEST_URI'] = '/'.$_GET['command'].(!empty($_GET['route']) ? '/'.$_GET['route'] : '');

		$config = [
			'settings' => [
				'displayErrorDetails' => true,
			],
			AuthorizationServer::class => function () {
				// Setup the authorization server
				$server = new \League\OAuth2\Server\AuthorizationServer(
						new Repositories\ClientRepository($this->api),                 // instance of ClientRepositoryInterface
						new Repositories\AccessTokenRepository($this->api),            // instance of AccessTokenRepositoryInterface
						new Repositories\ScopeRepository($this->api),                  // instance of ScopeRepositoryInterface
						'file://' . $this->privateKey,    // path to private key
						'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'      // encryption key
				);

				//Client Grant
				$server->enableGrantType(
					new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
					new \DateInterval('PT1H') // access tokens will expire after 1 hour
				);

				//Password Grant
				$grant = new \League\OAuth2\Server\Grant\PasswordGrant(
						new Repositories\UserRepository($this->api),           // instance of UserRepositoryInterface
						new Repositories\RefreshTokenRepository($this->api)    // instance of RefreshTokenRepositoryInterface
				);
				$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month
				// Enable the password grant on the server with a token TTL of 1 hour
				$server->enableGrantType(
						$grant,
						new \DateInterval('PT1H') // access tokens will expire after 1 hour
				);

				//Refresh Token Grant
				$grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant(new Repositories\RefreshTokenRepository($this->api));
				$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month

				// Enable the refresh token grant on the server
				$server->enableGrantType(
					$grant,
					new \DateInterval('PT1H') // new access tokens will expire after an hour
				);

				// Enable the implicit grant on the server
				$server->enableGrantType(
					new \League\OAuth2\Server\Grant\ImplicitGrant(new \DateInterval('PT1H')),
					new \DateInterval('PT1H') // access tokens will expire after 1 hour
				);

				// Enable the authentication code grant on the server
				$grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
					new Repositories\AuthCodeRepository($this->api),
					new Repositories\RefreshTokenRepository($this->api),    // instance of RefreshTokenRepositoryInterface
					new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
				);

				$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

				// Enable the authentication code grant on the server
				$server->enableGrantType(
					$grant,
					new \DateInterval('PT1H') // access tokens will expire after 1 hour
				);

				return $server;
			},
			'freepbx' => [
				'identity' => $this->freepbx->Config->get("FREEPBX_SYSTEM_IDENT"),
				'brand_image' => $this->freepbx->Config->get("BRAND_IMAGE_FREEPBX_FOOT"),
				'flattenedScopes' => $this->api->getFlattenedScopes(),
				'visualScopes' => $this->api->getVisualScopes()
			],
			'api' => $this->api
		];

		$ident = $this->freepbx->Config->get("FREEPBX_SYSTEM_IDENT");
		$image = $this->freepbx->Config->get("BRAND_IMAGE_FREEPBX_FOOT");
		$flattenedScopes = $this->api->getFlattenedScopes();
		// AppFactory::setSlimHttpDecoratorsAutomaticDetection(false);
		$container = new Container();
		$container->set(AuthorizationServer::class,
    	function (ContainerInterface $container) {
				// Setup the authorization server
				$server = new \League\OAuth2\Server\AuthorizationServer(
					new Repositories\ClientRepository($this->api),                 // instance of ClientRepositoryInterface
					new Repositories\AccessTokenRepository($this->api),            // instance of AccessTokenRepositoryInterface
					new Repositories\ScopeRepository($this->api),                  // instance of ScopeRepositoryInterface
					'file://' . $this->privateKey,    // path to private key
					'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'      // encryption key
			);

			//Client Grant
			$server->enableGrantType(
				new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
				new \DateInterval('PT1H') // access tokens will expire after 1 hour
			);

			//Password Grant
			$grant = new \League\OAuth2\Server\Grant\PasswordGrant(
					new Repositories\UserRepository($this->api),           // instance of UserRepositoryInterface
					new Repositories\RefreshTokenRepository($this->api)    // instance of RefreshTokenRepositoryInterface
			);
			$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month
			// Enable the password grant on the server with a token TTL of 1 hour
			$server->enableGrantType(
					$grant,
					new \DateInterval('PT1H') // access tokens will expire after 1 hour
			);

			//Refresh Token Grant
			$grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant(new Repositories\RefreshTokenRepository($this->api));
			$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month

			// Enable the refresh token grant on the server
			$server->enableGrantType(
				$grant,
				new \DateInterval('PT1H') // new access tokens will expire after an hour
			);

			// Enable the implicit grant on the server
			$server->enableGrantType(
				new \League\OAuth2\Server\Grant\ImplicitGrant(new \DateInterval('PT1H')),
				new \DateInterval('PT1H') // access tokens will expire after 1 hour
			);

			// Enable the authentication code grant on the server
			$grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
				new Repositories\AuthCodeRepository($this->api),
				new Repositories\RefreshTokenRepository($this->api),    // instance of RefreshTokenRepositoryInterface
				new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
			);

			$grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

			// Enable the authentication code grant on the server
			$server->enableGrantType(
				$grant,
				new \DateInterval('PT1H') // access tokens will expire after 1 hour
			);

				return $server;
			}
		);
		AppFactory::setContainer($container);
		$app = AppFactory::create();
		$app->get('/authorize', function ($request, $response, $args) use ($app) {
			try {
				$api = $app->getContainer()->get('api');
				$freepbx = $app->getContainer()->get('freepbx');
				$server = $app->getContainer()->get(AuthorizationServer::class);
				if(empty($_SESSION['authorize'])) {
					// Validate the HTTP request and return an AuthorizationRequest object.
					$authRequest = $server->validateAuthorizationRequest($request);
					$_SESSION['authorize'] = serialize($authRequest);
				} else {
					$authRequest = unserialize($_SESSION['authorize']);
					session_destroy();
				}

				// The auth request object can be serialized and saved into a user's session.
				// You will probably want to redirect the user at this point to a login endpoint.

				if(!$authRequest->isAuthorizationApproved()) {
					if(!empty($_REQUEST['username']) && !empty($_REQUEST['password'])) {
						if($api->freepbx->Userman->checkCredentials($_REQUEST['username'], $_REQUEST['password'])) {
							$user = $api->freepbx->Userman->getUserByUsername($_REQUEST['username']);
							// Once the user has logged in set the user on the AuthorizationRequest
							$authRequest->setUser(new UserEntity($user));
							// Once the user has approved or denied the client update the status
							// (true = approved, false = denied)
							$authRequest->setAuthorizationApproved(true);
							// Return the HTTP redirect response
							return $server->completeAuthorizationRequest($authRequest, $response);
						}
					}
					$body = $response->getBody();
					$body->write(load_view(dirname(__DIR__)."/views/authorize.php",[
						"app_name" => $authRequest->getClient()->getName(),
						"server" => $freepbx['identity'],
						"image" => $freepbx['brand_image'],
						"flattenedScopes" => $freepbx['flattenedScopes'],
						"scopes" => $authRequest->getScopes(),
						//"visualScopes" => $api->getVisualScopes(json_decode(json_encode($authRequest->getScopes(), JSON_THROW_ON_ERROR),true, 512, JSON_THROW_ON_ERROR))
						"visualScopes" => $api->getVisualScopes(json_decode(json_encode($authRequest->getScopes()),true))
					]));
					return;
				}
			} catch (OAuthServerException $exception) {

				// All instances of OAuthServerException can be formatted into a HTTP response
				return $exception->generateHttpResponse($response);

			} catch (\Exception $exception) {
				// Unknown exception
				$body = new Stream(fopen('php://temp', 'r+'));
				$body->write($exception->getMessage());
				return $response->withStatus(500)->withBody($body);

			}
		});
		$app->post('/token', function ($request, $response, $args) use ($app) {
			// dbug($request->getParsedBody());
			$authorizationServer = $app->getContainer()->get(AuthorizationServer::class);
			try {
				// Try to respond to the request
				return $authorizationServer->respondToAccessTokenRequest($request, $response);

			} catch (OAuthServerException $exception) {
				// All instances of OAuthServerException can be formatted into a HTTP response
				return $exception->generateHttpResponse($response);

			} catch (\Exception $exception) {
				// dbug($exception->getMessage());
				// Unknown exception
				$body = new Stream('php://temp', 'r+');
				$body->write($exception->getMessage());
				return $response->withStatus(500)->withBody($body);

			}
		});
		$app->run();
	}
}
