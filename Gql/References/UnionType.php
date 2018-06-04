<?php
namespace FreePBX\modules\Api\Gql\References;

use GraphQL\Type\Definition\UnionType as UT;
class UnionType extends BaseType {
	protected $typeCallbacks = null;
	protected $resolveTypeCallbacks = [];
	protected $resolveValueCallbacks = [];
	protected $type = 'union';

	public function __construct($name) {
		parent::__construct($name);
		$this->state = [
			'name' => $name,
			'description' => '',
			'types' => [],
			'resolveType' => null
		];
		return $this;
	}

	public function addResolveTypeCallback($callback) {
		if($this->isObject()) {
			throw new \Exception("Can not add a resolver after objectifying!");
		}
		$this->resolveTypeCallbacks[] = $callback;
	}

	public function getResolveTypeCallbacks() {
		return $this->resolveTypeCallbacks;
	}

	public function addResolveValueCallback($callback) {
		if($this->isObject()) {
			throw new \Exception("Can not add a resolver after objectifying!");
		}
		$this->resolveValueCallbacks[] = $callback;
	}

	public function getResolveValueCallbacks() {
		return $this->resolveValueCallbacks;
	}

	public function resolveValue($value) {
		foreach($this->getResolveValueCallbacks() as $cb) {
			$out = $cb($value);
			if(!is_null($out)) {
				return $out;
			}
		}
		return $value;
	}

	/**
	 * Add Resolver for this object
	 * @method addResolve
	 * @param  callback     $callback Callback function
	 */
	public function addResolveType($callback) {
		if($this->isObject()) {
			throw new \Exception("Can not add a resolver after objectifying!");
		}
		if(!empty($this->state['resolveType'])) {
			throw new \Exception("Can not add a resolver if one already exists!");
		}
		$this->state['resolveType'] = $callback;
	}

	/**
	 * Add array of types
	 * @method addtypes
	 * @param  callable    $callback [description]
	 */
	public function addTypeCallback($callback) {
		$this->typeCallbacks[] = $callback;
	}

	/**
	 * Get types as an Array
	 * @method gettypesArray
	 * @return mixed         [description]
	 */
	public function getTypesArray() {
		if($this->isObject()) {
			throw new \Exception("Can not get array of {$this->name} after objectifying!");
		}
		if(!empty($this->typeCallbacks)) {
			$this->state['types'] = function() {
				$final = [];
				foreach($this->typeCallbacks as $cb) {
					$final = array_merge($final, $cb());
				}
				return $final;
			};
		}
		return $this->state['types'];
	}

	/**
	 * Get finalized object
	 * @method getObject
	 * @return [type]    [description]
	 */
	public function getObject() {
		if (!$this->isObject()) {
			$this->getTypesArray();
			$this->object = new UT($this->state);
		}
		return $this->object;
	}
}
