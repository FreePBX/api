<?php

namespace FreePBX\modules\Api\Oauth\Middleware;

use GuzzleHttp\Psr7\Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Authenticate a bearer token before the route runs.
 *
 * The vendored ResourceServerMiddleware invokes the route from its error
 * handler. Protected routes then read a missing oauth_scopes value and throw,
 * so a missing or invalid token becomes a 500 that includes the file path.
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
	private ResourceServer $server;

	public function __construct(ResourceServer $server)
	{
		$this->server = $server;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try {
			$request = $this->server->validateAuthenticatedRequest($request);
		} catch (OAuthServerException $exception) {
			return $exception->generateHttpResponse(new Response());
		}

		return $handler->handle($request);
	}
}
