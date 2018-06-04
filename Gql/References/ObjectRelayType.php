<?php
namespace FreePBX\modules\Api\Gql\References;

use GraphQLRelay\Relay;

class ObjectRelayType extends ObjectType {
	protected $connection = null;
	protected $connectionFields = [];
	protected $edgeFields = [];

	public function getConnection() {
		if (empty($this->connection)) {
			$this->connection = Relay::connectionDefinitions([
				'name' => ucfirst(strtolower($this->name)),
				'nodeType' => $this->getObject(),
				'resolveNode' => $this->resolveNode,
				'edgeFields' => $this->edgeFields,
				'connectionFields' => $this->connectionFields
			]);
		}
		return $this->connection;
	}

	public function setConnectionResolveNode($callback) {
		$this->resolveNode = $callback;
	}

	public function setConnectionEdgeFields($callback) {
		$this->edgeFields = $callback;
	}

	public function setConnectionFields($callback) {
		$this->connectionFields = $callback;
	}

	public function getConnectionType() {
		return $this->getConnection()['connectionType'];
	}

	public function getConnectionObject() {
		return $this->getConnectionType();
	}
}
