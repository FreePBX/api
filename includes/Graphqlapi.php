<?php

namespace FreePBX\modules\Gqlapi\includes;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\Type;

class Graphqlapi {
	public function __construct($freepbx) {
		$this->freepbx = $freepbx;
	}
	public function execute() {
		$classes = $this->getAPIClasses();

		$this->initalizeReferences();

		$queryFields = [];
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$query = $object->constructQuery();
				foreach($query as &$q) {
					if(isset($q['type']) && is_string($q['type'])) {
						$q = $this->replaceTypes($q);
					}
				}
				$queryFields = array_merge($queryFields,$query);
			}
		}

		$queryType = new ObjectType([
			'name' => 'Query',
			'fields' => $queryFields
		]);

		$mutationFields = [];
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$mutation = $object->constructMutation();
				foreach($mutation as &$q) {
					if(isset($q['type']) && is_string($q['type'])) {
						$q = $this->replaceTypes($q);
					}
				}
				$mutationFields = array_merge($mutationFields,$mutation);
			}
		}

		$mutationType = new ObjectType([
			'name' => 'Mutation',
			'fields' => $mutationFields
		]);

		$schema = new Schema([
			'query' => $queryType,
			'mutation' => $mutationType
		]);

		//$schema->assertValid();

		$rawInput = file_get_contents('php://input');
		$input = json_decode($rawInput, true);
		$query = $input['query'];
		$variableValues = !empty($input['variables']) ? $input['variables'] : null;

		try {
			$result = GraphQL::execute(
				$schema,
				$query,
				null,
				null,
				$variableValues
			);
		} catch (\Exception $e) {
			$result = [
				'error' => [
					'message' => $e->getMessage()
				]
			];
		}
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($result);
	}

	private function initalizeReferences() {
		$classes = $this->getAPIClasses();
		$fieldTypes = [];
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$object->initReferences();
			}
		}
		foreach($classes as $module => $objects) {
			foreach($objects as $name => $object) {
				$object->postInitReferences();
			}
		}
	}

	private function generateQuery() {

	}

	private function getAPIClasses() {
		if(empty($this->classes)) {
			$webrootpath = $this->freepbx->Config->get('AMPWEBROOT');

			$this->objectReferences = new TypeStore();

			$fwcpath = $webrootpath . '/admin/libraries/Gqlapi';
			foreach (new \DirectoryIterator($fwcpath) as $fileInfo) {
				if($fileInfo->isDot()) { continue; };
				$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
				$class = "FreePBX\\Gqlapi\\".$name;
				$this->classes['framework'][$name] = new $class($this->freepbx,$this->objectReferences);
			}

			$amodules = $this->freepbx->Modules->getActiveModules();
			foreach($amodules as $module){
				//Module Path
				$mpath = $webrootpath . '/admin/modules/' . $module['rawname'] . '/Gqlapi/';
				if (file_exists($mpath)){
					//Class files
					foreach (new \DirectoryIterator($mpath) as $fileInfo) {
						if($fileInfo->isDot()) { continue; };
						$name = pathinfo($fileInfo->getFilename(),PATHINFO_FILENAME);
						$class = "FreePBX\\modules\\".$module['rawname']."\\Gqlapi\\".$name;
						$this->classes[$module['rawname']][$name] = new $class($this->freepbx,$this->objectReferences);
					}
				}
			}
			return $this->classes;
		} else {
			return $this->classes;
		}
	}

	private function replaceTypes($q) {
		if(isset($q['type']) && is_string($q['type'])) {
			if(preg_match('/^objectReference-(.*)/',$q['type'],$matches)) {
				if(!$this->objectReferences->get($matches[1])->isObject()) {
					$fields = $this->objectReferences->get($matches[1])->getFieldsArray();
					foreach($fields as &$a) {
						$a = $this->replaceTypes($a);
					}
					$this->objectReferences->get($matches[1])->replaceFields($fields);
				}
				$q['type'] = $this->objectReferences->get($matches[1])->getObject();
			}
			if(preg_match('/^objectListReference-(.*)/',$q['type'],$matches)) {
				if(!$this->objectReferences->get($matches[1])->isObject()) {
					$fields = $this->objectReferences->get($matches[1])->getFieldsArray();
					foreach($fields as &$a) {
						$a = $this->replaceTypes($a);
					}
					$this->objectReferences->get($matches[1])->replaceFields($fields);
				}
				$q['type'] = Type::listOf($this->objectReferences->get($matches[1])->getObject());
			}
		}
		return $q;
	}
}
