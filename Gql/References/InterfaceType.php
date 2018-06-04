<?php
namespace FreePBX\modules\Api\Gql\References;

use GraphQL\Type\Definition\InterfaceType as IT;
class InterfaceType extends BaseType {
	protected $typeCallbacks = null;
	protected $type = 'interface';

	public function __construct($name) {
		parent::__construct($name);
		$this->state = [
			'name' => $name,
			'description' => '',
			'fields' => [],
			'resolveType' => null
		];
		return $this;
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
	 * Add Resolver for this object
	 * @method addResolve
	 * @param  callback     $callback Callback function
	 */
	public function addResolve($callback) {
		if($this->isObject()) {
			throw new \Exception("Can not add a resolver after objectifying!");
		}
		if(!empty($this->state['resolveType'])) {
			throw new \Exception("Can not add a resolver if one already exists!");
		}
		$this->state['resolveType'] = $callback;
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
		if(!empty($this->typeCallbacks)) {
			$this->state['fields'] = function() {
				$final = [];
				foreach($this->typeCallbacks as $cb) {
					$final = array_merge($final, $cb());
				}
				return $final;
			};
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
			$this->getFieldsArray();
			$this->object = new IT($this->state);
		}
		return $this->object;
	}
}
