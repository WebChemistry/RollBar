<?php

namespace WebChemistry\RollBar\DI;

use Nette;
use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension {

	/** @var array */
	private $defaults = [
		'accessToken' => NULL,
		'logging' => FALSE,
		'enable' => '%productionMode%',
		'config' => [
			'included_errno' => -1
		]
	];

	public function loadConfiguration() {
		parent::loadConfiguration();

		$config = $this->getContainerBuilder()->expand($this->validateConfig($this->defaults, $this->getConfig()));
		$builder = $this->getContainerBuilder();
		if (!$config['accessToken']) {
			throw new \Exception('Access token for Rollback must be set.');
		}
		$builder->addDefinition($this->prefix('rollbar'))
			->setClass('WebChemistry\RollBar\RollBar', [$config['accessToken'], $config['logging'],
														$config['enable'], $config['config']]);
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$init = $class->getMethod('initialize');

		$init->addBody('$this->getByType(?)->onError[] = [$this->getService(?), "onApplicationError"];',
			['Nette\Application\Application', $this->prefix('rollbar')]);
	}

}