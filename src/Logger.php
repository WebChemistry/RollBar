<?php

namespace WebChemistry\RollBar;

use Tracy\Debugger;

class Logger implements \iRollbarLogger {

	const LEVEL = 'rollbar';

	public function log($level, $msg) {
		Debugger::getLogger()->log($msg, self::LEVEL);
	}
}