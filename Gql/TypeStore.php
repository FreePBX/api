<?php
namespace FreePBX\modules\Api\Gql;
use FreePBX\modules\Api\Gql\References\ObjectType;
use FreePBX\modules\Api\Gql\References\UnionType;
use FreePBX\modules\Api\Gql\References\InterfaceType;
use FreePBX\modules\Api\Gql\References\ObjectRelayType;
class TypeStore {
	private $types = [];

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
		switch($type) {
			case "objectrelay":
				$this->types[$name] = new ObjectRelayType($name);
			break;
			case "object":
				$this->types[$name] = new ObjectType($name);
			break;
			case "union":
				$this->types[$name] = new UnionType($name);
			break;
			case "interface":
				$this->types[$name] = new InterfaceType($name);
			break;
			case "enum":
				$this->types[$name] = new EnumType($name);
			break;
			default:
				throw new \Exception("Type $type is not valid!");
			break;
		}

		return $this->types[$name];
	}
}
