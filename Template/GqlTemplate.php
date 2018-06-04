<?php

namespace FreePBX\modules\%ucmodule%\Api\Gql;

use GraphQLRelay\Relay;
use GraphQL\Type\Definition\Type;
use FreePBX\modules\Api\Gql\Base;

class %uctable% extends Base {
	protected $module = '%lcmodule%';

	public function constructQuery() {
		if($this->checkAllReadScope()) {
			return [
				'all%uctable%s' => [
					'type' => $this->typeContainer->get('%lctable%')->getConnectionReference(),
					'description' => '%description%',
					'args' => Relay::connectionArgs(),
					'resolve' => function($root, $args) {
						return Relay::connectionFromArray($this->getData(), $args);
					},
				],
				'%lctable%' => [
					'type' => $this->typeContainer->get('%lctable%')->getReference(),
					'args' => [
						'id' => [
							'type' => Type::id(),
							'description' => 'The ID',
						]
					],
					'resolve' => function($root, $args) {
						return $this->getSingleData(Relay::fromGlobalId($args['id'])['id']);
					}
				]
			];
		}
	}

	private function getData() {
		$sth = $this->freepbx->Database('%sqlstatement%');
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function getSingleData($id) {
		return null;
	}

	public function initTypes() {
		$user = $this->typeContainer->create('%lctable%');
		$user->setDescription('%description%');

		$user->addInterfaceCallback(function() {
			return [$this->getNodeDefinition()['nodeInterface']];
		});

		$user->setGetNodeCallback(function($id) {
			return $this->getSingleData($id);
		});

		$user->addFieldCallback(function() {
			return %fieldcallback%
			];
		});

		$user->setConnectionResolveNode(function ($edge) {
			return $edge['node'];
		});

		$user->setConnectionFields(function() {
			return [
				'totalCount' => [
					'type' => Type::int(),
					'resolve' => function($value) {
						return count($this->getData());
					}
				],
				'%lctable%s' => [
					'type' => Type::listOf($this->typeContainer->get('%lctable%')->getObject()),
					'resolve' => function($root, $args) {
						$data = array_map(function($row){
							return $row['node'];
						},$root['edges']);
						return $data;
					}
				]
			];
		});
	}
}
