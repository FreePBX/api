<?php
namespace FreePBX\modules\Api\Gql;
use FreePBX\modules\Api\Gql\References\ObjectType;
use FreePBX\modules\Api\Gql\References\UnionType;
use FreePBX\modules\Api\Gql\References\InterfaceType;
use FreePBX\modules\Api\Gql\References\ObjectRelayType;
use FreePBX\modules\Api\Gql\References\EnumType;
class TypeStore {
	private array $types = [];

	public function exists($name) {
		return isset($this->types[$name]);
	}

	public function get($name) {
		if (!isset($this->types[$name])) {
			throw new \Exception("Type $name doesnt exist");
		}
		return $this->types[$name];
	}

	public function create($name,$type='objectrelay') {
		if (isset($this->types[$name])) {
			throw new \Exception("Type $name has already been created!");
		}
		$this->types[$name] = match ($type) {
      "objectrelay" => new ObjectRelayType($name),
      "object" => new ObjectType($name),
      "union" => new UnionType($name),
      "interface" => new InterfaceType($name),
      "enum" => new EnumType($name),
      default => throw new \Exception("Type $type is not valid!"),
  };

		return $this->types[$name];
	}
}
