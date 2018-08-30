<?php

namespace FreePBX\modules\Api\Rest;
use FreePBX\modules\Api\Includes\ApiBase;

abstract class Base extends ApiBase {
	protected $type = 'rest';
	public function setupRoutes($app) {
	}

	protected function checkAllReadScopeMiddleware() {
		return $this->checkScopeMiddleware("read");
	}

	protected function checkAllWriteScopeMiddleware() {
		return $this->checkScopeMiddleware("write");
	}

	protected function checkReadScopeMiddleware($scope) {
		return $this->checkScopeMiddleware("read:".$scope);
	}

	protected function checkWriteScopeMiddleware($scope) {
		return $this->checkScopeMiddleware("write:".$scope);
	}

	protected function checkScopeMiddleware($scope) {
		$self = $this;
		return function ($request, $response, $next) use ($self,$scope) {
			$allowedScopes = $request->getAttribute('oauth_scopes');
			$userId = $request->getAttribute('oauth_user_id');

			$self->setAllowedScopes($allowedScopes);
			if(!$self->checkScope($scope)) {
				$response = $response->withStatus(401)->withJson(array("status" => false, "message" => "unauthorized"));
			} else {
				$response = $next($request, $response);
			}
			return $response;
		};
	}
}
