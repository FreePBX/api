<?php
//Namespace should be FreePBX\Console\Command
namespace FreePBX\Console\Command;

//Symfony stuff all needed add these
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//la mesa
use Symfony\Component\Console\Helper\Table;
//Process
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\HelpCommand;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
class Api extends Command {
	protected function configure() {
		$this->setName('api')
			->setDescription(_('API'))
			->setDefinition(array(
				new InputOption('type', null, InputOption::VALUE_REQUIRED, _('Generate GQL from a database table'), 'gql'),
				new InputOption('generatefromtable', null, InputOption::VALUE_REQUIRED, _('Generate GQL from a database table')),
				new InputOption('module', null, InputOption::VALUE_REQUIRED, _('Module to place the API file if using generatefromtable')),
				new InputOption('generatefrommodule', null, InputOption::VALUE_REQUIRED, _('Generate GQL from a modules xml database definition')),
				new InputOption('path', null, InputOption::VALUE_REQUIRED, _('Module location path'),\FreePBX::Config()->get('AMPWEBROOT').'/admin/modules'),
				new InputArgument('args', InputArgument::IS_ARRAY, _('Execute Gql command'),null)
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$this->input = $input;
	
		$args = $input->getArgument('args');
		if(!empty($args) && $args[0] == 'gql'){
			/* API module normal console command handling */
			$this->handleArgs($args,$output);
			return;
		}	

		if($input->getOption('type') !== "gql") {
			$output->writeln(_("Only GQL type is supported at this time"));
			return;
		}
		if($input->getOption('generatefromtable') && $input->getOption('module')) {
			$tablename = $input->getOption('generatefromtable');
			$module = $input->getOption('module');

			$dir = $input->getOption('path').'/'.basename($module);
			if(!file_exists($dir)) {
				$output->writeln("<error>Module directory $dir does not exist!</error>");
				return;
			}

			$this->generateGQLFile($module, $tablename, $input, $output);
			return;
		}
		if($input->getOption('generatefrommodule')) {
			$module = $input->getOption('generatefrommodule');

			$dir = $input->getOption('path').'/'.basename($module);
			if(!file_exists($dir)) {
				$output->writeln("<error>Module directory $dir does not exist!</error>");
				return;
			}

			$xml = simplexml_load_file($dir.'/module.xml');
			if(!empty($xml->database)) {
				$tables = array();
				foreach($xml->database->table as $table) {
					$tname = (string)$table->attributes()->name;
					$tables[] = $tname;
				}
				$helper = $this->getHelper('question');
				$question = new ChoiceQuestion(
						"Please select the table you'd like to use",
						$tables
				);
				$question->setErrorMessage("Table '%s' is invalid.");
				$tablename = $helper->ask($input, $output, $question);

				$this->generateGQLFile($module, $tablename, $input, $output);
			} else {
				$output->writeln("No Database definitions in module.xml");
			}
			return;
		}
		$this->outputHelp($input,$output);
	}

	private function handleArgs($args,$output){
		$action = array_shift($args);
		switch($action){
			case 'gql':
			if(isset($args[0]) && $args[0] == 'genclientcred'){
				 $output->writeln(json_encode($this->generateAPICredentials($args),JSON_UNESCAPED_SLASHES));
				break;
			}else{ 
				include_once __DIR__ . '/../ApiGqlHelper.class.php';
				\FreePBX::ApiGqlHelper()->execGqlApi($args);
				break;
			}
		}
	}
	
	/**
	 * generateAPICredentials
	 *
	 * @param  mixed $args
	 * @return void
	 */
	private function generateAPICredentials($args){
		$this->freepbx = \FreePBX::Api();
		$this->db = \FreePBX::Database();
   	//clear all before generate new
      $query = "DELETE from api_applications Where `name`='System_Internal_GqlAll' And `grant_type`='client_credentials' AND `allowed_scopes`='gql'";
      $stmt = $this->db->prepare($query);
      $stmt->execute();
      //generate the api
      $res = $this->freepbx->applications->add('','client_credentials','System_Internal_GqlAll','System internal generated token so please do not delete','','','gql');
		
		$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$serverip = $protocol.'://'. $args[1] ;

		$obj = new \stdClass();
		$obj->token_url = $serverip.'/admin/api/api/token';
		$obj->authorization_url = $serverip.'/admin/api/api/authorize';
		$obj->graphql_url = $serverip.'/admin/api/api/gql';
		$obj->rest_url = $serverip.'/admin/api/api/rest';
		$obj->client_id = $res['client_id'];
		$obj->allowed_scopes = $res['allowed_scopes'];
		$obj->client_secret = $res['client_secret'];

		return $obj;
	}
	 
	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 * @throws \Symfony\Component\Console\Exception\ExceptionInterface
	 */
	protected function outputHelp(InputInterface $input, OutputInterface $output)	 {
		$help = new HelpCommand();
		$help->setCommand($this);
		return $help->run($input, $output);
	}

	private function generateGQLFile($module,$tablename, InputInterface $input, OutputInterface $output) {
		$helper = $this->getHelper('question');
		$question = new Question("Please enter the name you'd like to use for the singular object [$tablename]", $tablename);
		$object = $helper->ask($input, $output, $question);

		$ucObject = ucfirst($object);
		$lcObject = lcfirst($object);

		if(file_exists($dir.'/Api/Gql/'.$ucObject.'.php')) {
			$question = new ConfirmationQuestion($ucObject.'.php already exists, Would you like to continue with this action?', false);

			if (!$helper->ask($input, $output, $question)) {
				return;
			}
		}

		$table = \FreePBX::Database()->migrate($tablename);
		$generate = $table->generateUpdateArray();
		$idCol = null;
		$fields = [];
		$allowable = array('id', 'string', 'int', 'boolean', 'float');
		foreach($generate['columns'] as $col => $data) {
			switch($data['type']) {
				case 'integer':
				case 'smallint':
					if(!empty($data['autoincrement'])) {
						$default = 'id';
					} else {
						$default = 'int';
					}
				break;
				case 'boolean':
					$default = 'boolean';
				break;
				case 'text':
				case 'string':
				default:
					$default = 'string';
				break;
			}

			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion(
					"Please select the scalar type for column '$col' [$default]",
					$allowable,
					$default
			);
			$question->setErrorMessage("Scalar type '%s' is invalid.");
			$type = $helper->ask($input, $output, $question);

			while(empty($name) || isset($fields[$name])) {
				$question = new Question("Please enter the field name you'd like for column '$col' [$col]", $col);
				$name = $helper->ask($input, $output, $question);
				if(isset($fields[$name])) {
					$output->writeln("<error>You've already used $name. Please try again</error>");
				}
			}

			$question = new Question('Please enter a description for this field []', '');
			$description = $helper->ask($input, $output, $question);

			$notnull =  (isset($data['primarykey']) && $data['primarykey'] === true) || !isset($data['notnull']) || $data['notnull'] === true ? true : false;

			switch($type) {
				case 'id':
					$objectType = 'Type::id()';
					$idCol = $col;
					$allowable = array_diff($allowable, ['id']);
					if($name === 'id') {
						$name = $tablename.'_'.$name;
					}
				break;
				case 'string':
					$objectType = 'Type::string()';
				break;
				case 'int':
					$objectType = 'Type::int()';
				break;
				case 'boolean':
					$objectType = 'Type::boolean()';
				break;
				case 'float':
					$objectType = 'Type::float()';
				break;
			}

			if($notnull) {
				$objectType = "Type::nonNull($objectType)";
			}

			$fields[$name] = [
				"type" => $type,
				"name" => $name,
				"description" => $description,
				'column' => $col,
				"objectType" => $objectType,
				'notnull' => $notnull,
				'default' => isset($data['default']) ? $data['default'] : null
			];
		}

		$fieldcallback = '';
		foreach($fields as $field) {
			$resolver = '';
			$field['description'] = addslashes($field['description']);
			if($field['type'] === 'id') {
				$fieldcallback .= <<<EOF
				'id' => Relay::globalIdField('{$objectname}', function(\$row) {
					return isset(\$row['{$field['column']}']) ? \$row['{$field['column']}'] : null;
				}),
				'{$field['name']}' => [
					'type' => Type::nonNull(Type::string()),
					'description' => '{$field['description']}',
					'resolver' => function(\$row) {
						return isset(\$row['{$field['column']}']) ? \$row['{$field['column']}'] : null;
					}
				],\n
EOF;
			} else {
				if($field['name'] !== $field['column']) {
					$resolver = <<<EOF
					'resolve' => function(\$row) {
						return \$row['{$field['column']}'];
					}
EOF;
				}
				$fieldcallback .= <<<EOF
				'{$field['name']}' => [
					'type' => {$field['objectType']},
					'description' => '{$field['description']}',
					{$resolver}
				],\n
EOF;
			}
		}

		$inputfields = '';
		$mutationExecuteArray = '';
		$insertcols = [];
		foreach($fields as $field) {
			if($field['type'] === 'id') {
				$field['name'] = 'id';
				$field['notnull'] = true;
			}
			$inputfields .= <<<EOF
			'{$field['name']}' => [
				'type' => {$field['objectType']},
				'description' => '{$field['description']}'
			],\n
EOF;

			if($field['notnull']) {
				$mutationExecuteArray .= <<<EOF
			":{$field['column']}" => isset(\$input['{$field['name']}']) ? \$input['{$field['name']}'] : '{$field['default']}',\n
EOF;
			} else {
				$mutationExecuteArray .= <<<EOF
			":{$field['column']}" => isset(\$input['{$field['name']}']) ? \$input['{$field['name']}'] : null,\n
EOF;
}


		$insertcols[] = "`{$field['column']}`";
		$insertvals[] = ":{$field['name']}";
		$updatesetters[] = "`{$field['column']}` = :{$field['name']}";
		}

		$info = \FreePBX::Modules()->getInfo($module)[$module];

		$template = file_get_contents(dirname(__DIR__)."/Template/GqlTemplate.tpl");
		$template = str_replace([
			'%tablename%',
			'%ucclassname%',
			'%objectname%',
			'%ucmodule%',
			'%lcmodule%',
			'%idcol%',
			'%fieldcallback%',
			'%inputfields%',
			'%mutationexecutearray%',
			'%moduledescription%',
			'%description%',
			'%modulename%',
			'%insertcols%',
			'%insertvals%',
			'%updatesetters%'
		],[
			$tablename,
			$ucObject,
			$lcObject,
			ucfirst(strtolower($module)),
			strtolower($module),
			$idCol,
			$fieldcallback,
			$inputfields,
			$mutationExecuteArray,
			$info['description'],
			$info['description'],
			$info['name'],
			rtrim(implode(",",$insertcols),','),
			rtrim(implode(",",$insertvals),','),
			rtrim(implode(",",$updatesetters),','),
		],
			$template
		);

		$dir = $input->getOption('path').'/'.basename($module);
		if(!file_exists($dir.'/Api/Gql')) {
			mkdir($dir.'/Api/Gql',0775,true);
		}
		file_put_contents($dir.'/Api/Gql/'.$ucObject.'.php',$template);
	}
}
