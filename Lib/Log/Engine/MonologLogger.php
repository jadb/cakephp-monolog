<?php
App::uses('CakeLogInterface', 'Log');
App::uses('String', 'Utility');

use Monolog\Logger;

class MonologLogger implements CakeLogInterface {

	public $search = null;

	private $__config = array(
		'channel' => 'monolog',
		'handlers' => array(),
		'processors' => array()
	);

	public function __construct($options = array()) {
		extract(array_merge($this->__config, $options));
		if (!isset($search) || empty($search) || !is_dir($search)) {
			$search = dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendor' . DS;
		}

		include $search . 'autoload.php';

		$this->log = new Logger($channel);
		$this->__push($handlers);
		$this->__push($processors, 'Processor');
	}

	public function write($level, $message) {
		$levels = array(
			Logger::DEBUG => 'debug',
			Logger::INFO => 'info',
			Logger::NOTICE => 'notice',
			Logger::WARNING => 'warning',
			Logger::ERROR => 'error',
			Logger::CRITICAL => 'critical',
			Logger::ALERT => 'alert',
			Logger::EMERGENCY => 'emergency'
		);

		if (is_numeric($level)) {
			if (isset($levels[$level])) {
				$level = $levels[$level];
			} else {
				$level = 'error'; // Cake's default level.
			}
		}

		$this->log->$level($message);
	}

	private function __push($list, $type = 'Handler') {
		if (empty($list)) {
			if ('Handler' == $type) {
				$list = array('Stream' => array(LOGS . 'monolog.log'));
			}
		}

		foreach ($list as $name => $params) {
			if (is_numeric($name)) {
				$name = $params;
				$params = array();
			}

			$class = $name;
			if (strpos($class, $type) === false) {
				$class = "\Monolog\\$type\\$name$type";
			}

			$method = "push$type";

			switch(count($params)) {
				case 1:
					$this->log->$method(new $class($params[0]));
				break;

				case 2:
					$this->log->$method(new $class($params[0], $params[1]));
				break;

				case 3:
					$this->log->$method(new $class($params[0], $params[1], $params[2]));
				break;

				case 4:
					$this->log->$method(new $class($params[0], $params[1], $params[2], $params[3]));
				break;

				case 5:
					$this->log->$method(new $class($params[0], $params[1], $params[2], $params[3], $params[4]));
				break;

				default:
					$this->log->$method(new $class(null));
			}

		}
	}
}
