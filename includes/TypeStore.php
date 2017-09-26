<?php
namespace FreePBX\modules\Gqlapi\includes;
class TypeStore {
	private $types = [];

	public function get($name) {
		if (!isset($this->types[$name])) {
			$this->types[$name] = new ObjectReference($name);
		}
		return $this->types[$name];
	}
}
