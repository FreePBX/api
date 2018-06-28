<?php

namespace FreePBX\modules\%ucmodule%\Api\Gql;

use GraphQLRelay\Relay;
use GraphQL\Type\Definition\Type;
use FreePBX\modules\Api\Gql\Base;

class %ucclassname% extends Base {
	protected $module = '%lcmodule%';

	public function mutationCallback() {
		if($this->checkAllWriteScope()) {
			return function() {
				return [
					'add%ucclassname%' => Relay::mutationWithClientMutationId([
						'name' => 'add%ucclassname%',
						'description' => 'Add a new entry to %modulename%',
						'inputFields' => $this->getMutationFields(),
						'outputFields' => [
							'%objectname%' => [
								'type' => $this->typeContainer->get('%objectname%')->getObject(),
								'resolve' => function ($payload) {
									return count($payload) > 1 ? $payload : null;
								}
							]
						],
						'mutateAndGetPayload' => function ($input) {
							$sql = "INSERT INTO %tablename% (%insertcols%) VALUES (%insertvals%)";
							$sth = $this->freepbx->Database->prepare($sql);
							$sth->execute($this->getMutationExecuteArray($input));
							$item = $this->getSingleData($input['id']);
							return !empty($item) ? $item : [];
						}
					]),
					'update%ucclassname%' => Relay::mutationWithClientMutationId([
						'name' => 'update%ucclassname%',
						'description' => 'Update an entry in %modulename%',
						'inputFields' => $this->getMutationFields(),
						'outputFields' => [
							'%objectname%' => [
								'type' => $this->typeContainer->get('%objectname%')->getObject(),
								'resolve' => function ($payload) {
									return count($payload) > 1 ? $payload : null;
								}
							]
						],
						'mutateAndGetPayload' => function ($input) {
							$item = $this->getSingleData($input['id']);
							if(empty($tiem)) {
								return null;
							}
							$sql = "UPDATE %tablename% SET %updatesetters% WHERE `%idcol%` = :id";
							$sth = $this->freepbx->Database->prepare($sql);
							$sth->execute($this->getMutationExecuteArray($input));
							$item = $this->getSingleData($input['id']);
							return !empty($item) ? $item : [];
						}
					]),
					'remove%ucclassname%' => Relay::mutationWithClientMutationId([
						'name' => 'remove%ucclassname%',
						'description' => 'Remove an entry from %modulename%',
						'inputFields' => [
							'id' => [
								'type' => Type::nonNull(Type::id())
							]
						],
						'outputFields' => [
							'deletedId' => [
								'type' => Type::nonNull(Type::id()),
								'resolve' => function ($payload) {
									return $payload['id'];
								}
							]
						],
						'mutateAndGetPayload' => function ($input) {
							$sql = "DELETE FROM %tablename% WHERE `%idcol%` = :id";
							$sth = $this->freepbx->Database->prepare($sql);
							$sth->execute([
								":id" => $input['id']
							]);
							return ['id' => $input['id']];
						}
					])
				];
			};
		}
	}

	public function queryCallback() {
		if($this->checkAllReadScope()) {
			return function() {
				return [
					'all%ucclassname%s' => [
						'type' => $this->typeContainer->get('%objectname%')->getConnectionType(),
						'description' => '%moduledescription%',
						'args' => Relay::forwardConnectionArgs(),
						'resolve' => function($root, $args) {
							$after = !empty($args['after']) ? Relay::fromGlobalId($args['after'])['id'] : null;
							$first = !empty($args['first']) ? $args['first'] : null;
							return Relay::connectionFromArraySlice(
								$this->getData($after,$first),
								$args,
								[
									'sliceStart' => !empty($after) ? $after : 0,
									'arrayLength' => $this->getTotal()
								]
							);
						},
					],
					'%objectname%' => [
						'type' => $this->typeContainer->get('%objectname%')->getObject(),
						'description' => '%moduledescription%',
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
			};
		}
	}

	private function getTotal() {
		$sql = "SELECT count(*) as count FROM %tablename%";;
		$sth = $this->freepbx->Database->prepare($sql);
		$sth->execute();
		return $sth->fetchColumn();
	}

	private function getData($after, $first) {
		$sql = 'SELECT * FROM %tablename%';
		$sql .= " " . (!empty($first) && is_numeric($first) ? "LIMIT ".$first : "LIMIT 18446744073709551610");
		$sql .= " " . (!empty($after) && is_numeric($after) ? "OFFSET ".$after : "OFFSET 0");

		$sth = $this->freepbx->Database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function getSingleData($id) {
		$sth = $this->freepbx->Database->prepare('SELECT * FROM %tablename% WHERE `%idcol%` = :id');
		$sth->execute([
			":id" => $id
		]);
		return $sth->fetch(\PDO::FETCH_ASSOC);
	}

	public function initializeTypes() {
		$user = $this->typeContainer->create('%objectname%');
		$user->setDescription('%description%');

		$user->addInterfaceCallback(function() {
			return [$this->getNodeDefinition()['nodeInterface']];
		});

		$user->setGetNodeCallback(function($id) {
			return $this->getSingleData($id);
		});

		$user->addFieldCallback(function() {
			return [
%fieldcallback%
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
						return $this->getTotal();
					}
				],
				'%objectname%s' => [
					'type' => Type::listOf($this->typeContainer->get('%objectname%')->getObject()),
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

	private function getMutationFields() {
		return [
%inputfields%
		];
	}

	private function getMutationExecuteArray($input) {
		return [
%mutationexecutearray%
		];
	}
}
