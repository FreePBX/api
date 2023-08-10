<?php
namespace FreePBX\modules\Api\Gql\References;

use GraphQL\Type\Definition\ObjectType as OT;
class ObjectType extends BaseType {
	protected $resolveNode = null;
	protected $interfaces = [];
	protected $getNodeCallback = null;
	protected $typeCallbacks = [];
	protected $resolveValueCallback = null;

	public function __construct($name) {
		parent::__construct($name);
		$this->state = [
			'name' => $name,
			'description' => '',
			'fields' => [],
			'interfaces' => []
		];
		return $this;
	}

	public function setGetNodeCallback($callback) {
		$this->getNodeCallback = $callback;
	}

	public function getNode($id) {
		return call_user_func_array($this->getNodeCallback,[$id]);
	}

	public function addResolveValueCallback($callback) {
		if($this->isObject()) {
			throw new \Exception("Can not add a resolver after objectifying!");
		}
		$this->resolveValueCallback = $callback;
	}

	public function resolveValue($value) {
		$out = $this->resolveValueCallback($value);
		if(!is_null($out)) {
			return $out;
		}
		return null;
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
		if(!empty($this->state['resolveField'])) {
			throw new \Exception("Can not add a resolver if one already exists!");
		}
		$this->state['resolveField'] = $callback;
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
	 * Replace all Fields with input
	 * @method replaceFields
	 * @param  mixed        $fields [description]
	 * @return [type]                [description]
	 */
	public function replaceFields(mixed $fields) {
		if($this->isObject()) {
			throw new \Exception("Can not add a field after objectifying!");
		}
		$this->state['fields'] = $fields;
		return $this->state['fields'];
	}

	public function addFieldCallback($callback) {
		$this->typeCallbacks[] = $callback;
	}

	public function addInterfaceCallback($callback) {
		$this->interfaces[] = $callback;
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

	public function getInterfacesArray() {
		if($this->isObject()) {
			throw new \Exception("Can not get array of {$this->name} after objectifying!");
		}
		if(!empty($this->interfaces)) {
			$this->state['interfaces'] = function() {
				$final = [];
				foreach($this->interfaces as $cb) {
					$final = array_merge($final, $cb());
				}
				return $final;
			};
		}
		return $this->state['interfaces'];
	}

	/**
	 * Get finalized object
	 * @method getObject
	 * @return [type]    [description]
	 */
	public function getObject() {
		if (!$this->isObject()) {
			$this->getFieldsArray();
			$this->getInterfacesArray();
			$this->object = new OT($this->state);
		}
		return $this->object;
	}
}
