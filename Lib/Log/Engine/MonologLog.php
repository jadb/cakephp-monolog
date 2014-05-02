<?php
App::uses('BaseLog', 'Log/Engine');
App::uses('String', 'Utility');

use Monolog\Logger;

class MonologLog extends BaseLog {

	public $defaults = array(
		'channel' => 'monolog',
		'handlers' => array(),
		'processors' => array()
	);

	public function __construct($config = array()) {
		parent::__construct(array_merge($this->defaults, $config));
		extract($this->_config);

		if ( ! class_exists('Monolog\Logger'))
			$this->includeMonolog();

		$this->log = new Logger($channel);
		$this->__push($this->log, $handlers);
		$this->__push($this->log, $processors, 'Processor');
	}

	private function includeMonolog()
	{
		if (!isset($search) || empty($search) || !is_dir($search)) {
			$search = dirname(dirname(dirname(CakePlugin::path('Monolog')))) . DS . 'vendor' . DS;
			if (!is_dir($search . 'monolog')) {
				$search = $search = dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendor' . DS;
				if (!is_dir($search . 'monolog')) {
					throw new Exception("Missing the monolog/monolog composer package.");
				}
			}
		}

		include $search . 'autoload.php';
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

	private function __push($object, $list, $type = 'Handler') {
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

			$this->__run($object, $name, $type, $params);
		}
	}

	private function __run($object, $name, $type, $params) {
		$extras = array('formatters', 'processors');

		$class = $name;
		if (strpos($class, $type) === false) {
			$class = "\Monolog\\$type\\$name$type";
		} else if (isset($params['search'])) {
			if (strpos($params['search'], '.php') === false) {
				$params['search'] .= DS . $class . '.php';
			}
			require_once $params['search'];
			unset($params['search']);
		}

		if ('Handler' === $type) {
			foreach ($extras as $k) {
				if (isset($params[$k])) {
					$$k = $params[$k];
					unset($params[$k]);
				}
			}
		}

		$method = "push$type";
		if ('Formatter' === $type) {
			$method = 'setFormatter';
		}

		$params = array_values($params);

		$class_reflector = new ReflectionClass($class);
		$_class = $class_reflector->newInstanceArgs($params);
		$object->$method($_class);

		foreach ($extras as $k) {
			if (!empty($$k)) {
				$this->__push($_class, (array) $$k, ucfirst(substr($k, 0, strlen($k) - 1)));
			}
		}
	}
}
