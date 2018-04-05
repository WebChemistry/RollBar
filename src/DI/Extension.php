<?php

namespace WebChemistry\RollBar\DI;

use Nette;
use Nette\DI\CompilerExtension;
use WebChemistry\RollBar\RollBar;
use Nette\Application\Application;

class Extension extends CompilerExtension {

	/** @var array */
	private $defaults = [
		'accessToken' => NULL,
		'logging' => FALSE,
		'enable' => '%productionMode%',
		'config' => [
			'included_errno' => -1,
		],
	];

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();

		$this->defaults['enable'] = $builder->parameters['productionMode'];
		$config = $this->validateConfig($this->defaults);
		if (!$config['accessToken']) {
			throw new \Exception('Access token for Rollback must be set.');
		}
		$builder->addDefinition($this->prefix('rollbar'))
			->setFactory(RollBar::class, [$config['accessToken'], $config['logging'], $config['enable'], $config['config']]);
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$init = $class->getMethod('initialize');

		$init->addBody('$this->getByType(?)->onError[] = [$this->getService(?), "onApplicationError"];',
			[Application::class, $this->prefix('rollbar')]);
	}

}
