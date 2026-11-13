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
		return function ($request, $handler) use ($self, $scope) {
			$allowedScopes = $request->getAttribute('oauth_scopes');
			if (!is_array($allowedScopes)) {
				$response = new \GuzzleHttp\Psr7\Response(401);
				$response = $response->withHeader('Content-Type', 'application/json');
				$response->getBody()->write(json_encode(["status" => false, "message" => "unauthorized"]));
				return $response;
			}
			$self->setAllowedScopes($allowedScopes);
			if(!$self->checkScope($scope)) {
					$response = new \GuzzleHttp\Psr7\Response();
					$response = $response->withHeader('Content-Type', 'application/json');
					$response->getBody()->write(json_encode(["status" => false, "message" => "unauthorized"]));
					return $response;
			} else {
				return $handler->handle($request); // Call the next middleware or route handler
			}
		};
	}
}
