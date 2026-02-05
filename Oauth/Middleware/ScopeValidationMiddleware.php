<?php

namespace FreePBX\modules\Api\Oauth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FreePBX\modules\Api\Oauth\Repositories\AccessTokenRepository;

/**
 * Middleware to validate scopes from database instead of trusting JWT claims
 * This prevents JWT tampering attacks where someone reuses a jti with modified scopes
 */
class ScopeValidationMiddleware implements MiddlewareInterface
{
	private AccessTokenRepository $accessTokenRepository;

	public function __construct(AccessTokenRepository $accessTokenRepository)
	{
		$this->accessTokenRepository = $accessTokenRepository;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// Security fix: Validate scopes from database instead of trusting JWT claims
		// This prevents tampering with JWT tokens to escalate privileges by reusing the same jti
		// Even if someone tampers with the JWT and changes scopes, we use the database scopes
		$jti = $request->getAttribute('oauth_access_token_id');
		if ($jti) {
			$tokenData = $this->accessTokenRepository->getTokenById($jti);
			if ($tokenData && isset($tokenData['scopes']) && is_array($tokenData['scopes'])) {
				// Convert scope objects to identifiers if needed (scopes are stored as JSON array)
				$dbScopes = [];
				foreach ($tokenData['scopes'] as $scope) {
					if (is_object($scope) && method_exists($scope, 'getIdentifier')) {
						$dbScopes[] = $scope->getIdentifier();
					} elseif (is_string($scope)) {
						$dbScopes[] = $scope;
					}
				}
				// Replace JWT scopes with database scopes to prevent scope escalation
				$request = $request->withAttribute('oauth_scopes', $dbScopes);
			}
		}

		return $handler->handle($request);
	}
}

