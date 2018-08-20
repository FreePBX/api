<?php
namespace FreePBX\modules\%ucmodule%\Api\Rest;

use FreePBX\modules\Api\Rest\Base;

class %uctable% extends Base {
	public function setupRoutes($app) {
		$app->get('/%lctable%', function ($request, $response, $args) {
			return $response->withJson($this->getData());
		});

		$app->get('/%lctable%/:id', function ($request, $response, $args) {
			return $response->withJson($this->getSingleData($args['id']));
		});
	}

	private function getData() {
		$sth = $this->freepbx->Database('%sqlstatement%');
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function getSingleData($id) {
		return null;
	}
}
