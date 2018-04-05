<?php

namespace WebChemistry\RollBar;

use Nette\Application\Application;
use Tracy\Debugger;

class RollBar {

	const ERROR = 'error';
	const INFO = 'info';

	/** @var bool */
	private $enable = FALSE;

	public function __construct($accessToken, $logging, $enable, array $config = []) {
		if (!defined('BASE_EXCEPTION')) {
			define('BASE_EXCEPTION', version_compare(phpversion(), '7.0', '<')? '\Exception': '\Throwable');
		}
		\Rollbar::init(['access_token' => $accessToken] + $config, false, false, false);
		if (!$enable) {
			return;
		}
		$this->registerHandlers();
		if ($logging) {
			\Rollbar::$instance->logger = new Logger;
		}
		$this->enable = $enable;
	}

	public function sendTest() {
		$this->report('testing123', 'info');
		$this->exception(new \Exception('test exception'));
		$this->exception(new \Exception('test 2'));
	}

	private function registerHandlers() {
		set_error_handler([$this, 'errorHandler']);
		set_exception_handler([$this, 'exceptionHandler']);
		register_shutdown_function([$this, 'shutdownHandler']);
	}

	public function shutdownHandler() {
		$error = error_get_last();
		if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_RECOVERABLE_ERROR, E_USER_ERROR], TRUE)) {
			$this->error($error['type'], $error['message'], $error['file'], $error['line']);
		}
		$this->flush();
	}

	public function exceptionHandler($e, $exit = TRUE) {
		if (!is_a($e, BASE_EXCEPTION)) {
			throw new \Exception(sprintf('Report exception requires an instance of %s.', BASE_EXCEPTION ));
		}
		$this->exception($e);
		Debugger::exceptionHandler($e, $exit);
	}

	public function errorHandler($severity, $message, $file, $line, $context) {
		$this->error($severity, $message, $file, $line);
		Debugger::errorHandler($severity, $message, $file, $line, $context);
	}

	public function report($message, $level = self::ERROR, $extraData = NULL, $payloadData = NULL) {
		\Rollbar::report_message($message, $level, $extraData, $payloadData);
	}

	public function error($errno, $errstr, $errfile, $errline) {
		return \Rollbar::report_php_error($errno, $errstr, $errfile, $errline);
	}

	public function exception($exception, $extraData = NULL, $payloadData = NULL) {
		if (!is_a($exception, BASE_EXCEPTION)) {
			throw new \Exception(sprintf('Report exception requires an instance of %s.', BASE_EXCEPTION ));
		}
		\Rollbar::report_exception($exception, $extraData, $payloadData);
	}

	public function flush() {
		\Rollbar::flush();
	}

	public function onApplicationError(Application $application, \Exception $exception) {
		if ($this->enable) {
			$this->exception($exception);
		}
	}

}
