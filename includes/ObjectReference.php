<?php
namespace FreePBX\modules\Gqlapi\includes;

use GraphQL\Type\Definition\ObjectType;
class ObjectReference {
	private $state = null;
	private $object = null;
	private $name = null;

	public function __construct($name) {
		$this->name = $name;
		$this->state = [
			'name' => $name,
			'description' => '',
			'fields' => []
		];
		return $this;
	}

	/**
	 * Quickly turn object to string reference
	 * @method __toString
	 * @return string     [description]
	 */
	public function __toString() {
		return $this->getReference();
	}

	public function addResolve($callback) {
		$this->state['resolveField'] = $callback;
	}

	/**
	 * Return Reference for Object
	 * @method getReference
	 * @return [type]       [description]
	 */
	public function getReference() {
		return 'objectReference-'.$this->name;
	}

	/**
	 * Return Reference for List of Objects
	 * @method getListReference
	 * @return [type]           [description]
	 */
	public function getListReference() {
		return 'objectListReference-'.$this->name;
	}

	/**
	 * Replace all Fields with input
	 * @method replaceFields
	 * @param  mixed        $fields [description]
	 * @return [type]                [description]
	 */
	public function replaceFields($fields) {
		if($this->isObject()) {
			throw new \Exception("Can not add a field after objectifying!");
		}
		$this->state['fields'] = $fields;
	}

	/**
	 * Add array of fields
	 * @method addFields
	 * @param  [type]    $fields [description]
	 */
	public function addFields($fields) {
		if($this->isObject()) {
			throw new \Exception("Can not add a field after objectifying!");
		}
		$this->state['fields'] = array_merge($fields,$this->state['fields']);
	}

	/**
	 * Add Single Field
	 * @method addField
	 * @param  string   $fieldName The Field Name
	 * @param  [type]   $fieldData [description]
	 */
	public function addField($fieldName,$fieldData) {
		if($this->isObject()) {
			throw new \Exception("Can not add a field after objectifying!");
		}
		$this->state['fields'][$fieldName] = $fieldData;
	}

	/**
	 * Check if this has been converted to an object
	 * @method isObject
	 * @return boolean  [description]
	 */
	public function isObject() {
		return is_object($this->object);
	}

	/**
	 * Get Fields as an Array
	 * @method getFieldsArray
	 * @return mixed         [description]
	 */
	public function getFieldsArray() {
		if($this->isObject()) {
			throw new \Exception("Can not get array of {$this->name} after objectifying!");
		}
		return $this->state['fields'];
	}

	/**
	 * Get finalized object
	 * @method getObject
	 * @return [type]    [description]
	 */
	public function getObject() {
		if (!$this->isObject()) {
			$this->object = new ObjectType($this->state);
		}
		return $this->object;
	}
}
