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
class Api extends Command {
	protected function configure() {
		$this->setName('api')
			->setDescription(_('API'))
			->setDefinition(array(
				new InputOption('template', null, InputOption::VALUE_REQUIRED, _('Template type rest or gql')),
				new InputOption('table', null, InputOption::VALUE_REQUIRED, _('The sql table to generate the template from'))
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		if($input->getOption('template') && $input->getOption('table')) {
			$template = $input->getOption('template');
			$table = $input->getOption('table');

			$table = \FreePBX::Database()->migrate($table);
			$generate = $table->generateUpdateArray();
			foreach($generate['columns'] as $col => $data) {
			}
		}
		if(!$input->getOption('template') && !$input->getOption('table')) {
			$this->outputHelp($input,$output);
			exit(4);
		}
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
}
