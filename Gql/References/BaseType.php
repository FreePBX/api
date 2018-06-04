<?php
namespace FreePBX\modules\Api\Gql\References;

abstract class BaseType {
	protected $state = null;
	protected $object = null;
	protected $name = null;

	public function __construct($name) {
		$this->name = $name;
		$this->state = [
			'name' => $name,
			'description' => ''
		];
		return $this;
	}

	/**
	 * Set description for this object
	 * @method setDescription
	 * @param  string         $description The description (used in documentation)
	 */
	public function setDescription($description) {
		if($this->isObject()) {
			throw new \Exception("Can not add a description after objectifying!");
		}
		$this->state['description'] = $description;
	}

	/**
	 * Check if this has been converted to an object
	 * @method isObject
	 * @return boolean  [description]
	 */
	public function isObject() {
		return is_object($this->object);
	}
}
