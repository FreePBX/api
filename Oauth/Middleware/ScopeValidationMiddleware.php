<?php

namespace FreePBX\modules\Api\Oauth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FreePBX\modules\Api\Oauth\Repositories\AccessTokenRepository;

/**
 * Middleware to validate scopes from database instead of trusting JWT claims.
 * Compatible with both Slim 3.x and PSR-15.
 */
class ScopeValidationMiddleware implements MiddlewareInterface
{
	private $accessTokenRepository;

	public function __construct(AccessTokenRepository $accessTokenRepository)
	{
		$this->accessTokenRepository = $accessTokenRepository;
	}

	/**
	 * PSR-15 middleware interface for Slim 4.x and newer frameworks
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$request = $this->validateScopes($request);
		return $handler->handle($request);
	}

	/**
	 * Slim 3.x middleware interface (invokable middleware)
	 * This is called by Slim 3.x when middleware is added with $app->add()
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		$request = $this->validateScopes($request);
		return $next($request, $response);
	}

	/**
	 * Common scope validation logic used by both middleware interfaces
	 */
	private function validateScopes(ServerRequestInterface $request)
	{
		// Clear scopes from JWT so only DB scopes are used.
		$request = $request->withoutAttribute('oauth_scopes');

		$jti = $request->getAttribute('oauth_access_token_id');
		if ($jti) {
			$tokenData = $this->accessTokenRepository->getTokenById($jti);
			if ($tokenData && isset($tokenData['scopes']) && is_array($tokenData['scopes'])) {
				$dbScopes = [];
				foreach ($tokenData['scopes'] as $scope) {
					if (is_object($scope) && method_exists($scope, 'getIdentifier')) {
						$dbScopes[] = $scope->getIdentifier();
					} elseif (is_string($scope)) {
						$dbScopes[] = $scope;
					}
				}
				$request = $request->withAttribute('oauth_scopes', $dbScopes);
			}
		}

		return $request;
	}
}
