<?php

namespace FreePBX\modules\Api\Oauth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FreePBX\modules\Api\Oauth\Repositories\AccessTokenRepository;

/**
 * Replace JWT scope and subject claims with the values stored for that token.
 *
 * Must run after ResourceServerMiddleware. Slim executes the last middleware
 * added first, so this middleware has to be added before the resource server.
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
		return $handler->handle($this->bindStoredAuthorization($request));
	}

	/**
	 * Drop JWT authorization claims and restore them from the access-token row.
	 * A client_credentials token has no user, so a rewritten subject is ignored.
	 */
	public function bindStoredAuthorization(ServerRequestInterface $request): ServerRequestInterface
	{
		$request = $request->withoutAttribute('oauth_scopes');
		$request = $request->withoutAttribute('oauth_user_id');

		$jti = $request->getAttribute('oauth_access_token_id');
		if (!$jti) {
			return $request;
		}

		$tokenData = $this->accessTokenRepository->getTokenById($jti);
		if (!is_array($tokenData)) {
			return $request;
		}

		$request = $request->withAttribute('oauth_scopes', $this->scopeIdentifiers($tokenData['scopes'] ?? []));

		$uid = $tokenData['uid'] ?? null;
		if ($uid !== null && $uid !== '') {
			$request = $request->withAttribute('oauth_user_id', $uid);
		}

		return $request;
	}

	private function scopeIdentifiers($scopes): array
	{
		if (!is_array($scopes)) {
			return [];
		}

		$identifiers = [];
		foreach ($scopes as $scope) {
			if (is_object($scope) && method_exists($scope, 'getIdentifier')) {
				$identifiers[] = $scope->getIdentifier();
			} elseif (is_string($scope)) {
				$identifiers[] = $scope;
			}
		}

		return $identifiers;
	}
}

