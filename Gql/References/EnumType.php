<?php
namespace FreePBX\modules\Api\Gql\References;

use GraphQL\Type\Definition\EnumType as ET;
class EnumType extends BaseType {

	public function __construct($name) {
		parent::__construct($name);
		$this->state = [
			'name' => $name,
			'description' => '',
			'values' => []
		];
		return $this;
	}

	/**
	 * Add array of fields
	 * @method addFields
	 * @param  [type]    $fields [description]
	 */
	public function addFields($values) {
		if($this->isObject()) {
			throw new \Exception("Can not add a field after objectifying!");
		}
		$this->state['values'] = array_merge($fields,$this->state['values']);
	}
	/**
	 * Add Single Field
	 * @method addField
	 * @param  string   $fieldName The Field Name
	 * @param  [type]   $fieldData [description]
	 */
	public function addField($valueName,$valueData) {
		if($this->isObject()) {
			throw new \Exception("Can not add a field after objectifying!");
		}
		$this->state['values'][$fieldName] = $fieldData;
	}

	/**
	 * Get finalized object
	 * @method getObject
	 * @return [type]    [description]
	 */
	public function getObject() {
		if (!$this->isObject()) {
			$this->object = new ET($this->state);
		}
		return $this->object;
	}

}
