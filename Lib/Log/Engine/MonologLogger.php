<?php
App::uses('CakeLogInterface', 'Log');
App::uses('String', 'Utility');

use Monolog\Logger;

use Monolog\Handler\AmqpHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\CouchDBHandler;
use Monolog\Handler\CubeHandler;
use Monolog\Handler\DoctrineCouchDBHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\PushoverHandler;
use Monolog\Handler\RavenHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailHandler;
use Monolog\Handler\SyslogHandler;

use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakProcessor;
use Monolog\Processor\MemoryProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;

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
				$class .= $type;
			}

			switch(strtolower($name)) {
				// HANDLERS
				case 'amqp':
					$params += array(null, 'log', Logger::DEBUG, true);
					$this->log->pushHandler(new AmqpHandler($params[0], $params[1], $params[2], $params[3]));
				break;

				case 'buffer':
					$params += array(null, 0, Logger::DEBUG, true);
					$this->log->pushHandler(new BufferHandler($params[0], $params[1], $params[2], $params[3]));
				break;

				case 'chromephp':
					$params += array(Logger::DEBUG, true);
					$this->log->pushHandler(new ChromePHPHandler($params[0], $params[1]));
				break;

				case 'couchdb':
					$params += array(array(), Logger::DEBUG, true);
					$this->log->pushHandler(new CouchDBHandler($params[0], $params[1], $params[2]));
				break;

				case 'cube':
					$params += array(null, Logger::DEBUG, true);
					$this->log->pushHandler(new CubeHandler($params[0], $params[1], $params[2]));
				break;

				case 'doctrinecouchdb':
					$params += array(null, Logger::DEBUG, true);
					$this->log->pushHandler(new DoctrineCouchDBHandler($params[0], $params[1], $params[2]));
				break;

				case 'fingerscrossed':
					$params += array(null, null, 0, true, true);
					$this->log->pushHandler(new FingersCrossedHandler($params[0], $params[1], $params[2], $params[3], $params[4]));
				break;

				case 'firephp':
					$this->log->pushHandler(new FirePHPHandler());
				break;

				case 'gelf':
					$params += array(null, Logger::DEBUG, true);
					$this->log->pushHandler(new GelfHandler($params[0], $params[1], $params[2]));
				break;

				case 'group':
					$params += array(array(), true);
					$this->log->pushHandler(new GroupHandler($params[0], $params[1], $params[2]));
				break;

				case 'mongodb':
					$params += array(null, null, null, Logger::DEBUG, true);
					$this->log->pushHandler(new MongoDBHandler($params[0], $params[1], $params[2], $params[3], $params[4]));
				break;

				case 'nativemailer':
					$params += array(null, null, null, Logger::ERROR, true);
					$this->log->pushHandler(new NativeMailerHandler($params[0], $params[1], $params[2], $params[3], $params[4]));
				break;

				case 'null':
					$params += array(Logger::DEBUG);
					$this->log->pushHandler(new NullHandler($params[0]));
				break;

				case 'pushover':
					$params += array(null, null, null, Logger::DEBUG, true);
					$this->log->pushHandler(new PushoverHandler($params[0], $params[1], $params[2], $params[3], $params[4]));
				break;

				case 'raven':
					$params += array(null, Logger::DEBUG, true);
					$this->log->pushHandler(new RavenHandler($params[0], $params[1], $params[2]));
				break;

				case 'rotatingfile':
					$params += array(LOGS . 'monolog-rotate.log', 0, Logger::DEBUG, true);
					$this->log->pushHandler(new RotatingFileHandler($params[0], $params[1], $params[2], $params[3]));
				break;

				case 'socket':
					$params += array(null, Logger::DEBUG, true);
					$this->log->pushHandler(new SocketHandler($params[0], $params[1], $params[2]));
				break;

				case 'stream':
				debug($params);
					$params += array(LOGS . 'monolog.log', Logger::DEBUG, true);
					$this->log->pushHandler(new StreamHandler($params[0], $params[1], $params[2]));
				break;

				case 'swiftmailer':
					$params += array(null, null, Logger::DEBUG, true);
					$this->log->pushHandler(new SwiftMailerHandler($params[0], $params[1], $params[2], $params[3]));
				break;

				case 'syslog':
					$params += array(null, LOG_USER, Logger::DEBUG, true, LOG_PID);
					$this->log->pushHandler(new SyslogHandler($params[0], $params[1], $params[2], $params[3], $params[4]));
				break;


				// PROCESSORS
				case 'introspection':
					$this->log->pushProcessor(new IntrospectionProcessor());
				break;

				case 'memorypeakusage':
					$this->log->pushProcessor(new MemoryPeakUsageProcessor((bool) $params));
				break;

				case 'memory':
					$this->log->pushProcessor(new MemoryProcessor((bool) $params));
				break;

				case 'memoryusage':
					$this->log->pushProcessor(new MemoryUsageProcessor());
				break;

				case 'psrlogmessage':
					$this->log->pushProcessor(new PsrLogMessageProcessor());
				break;

				case 'web':
					if (empty($params)) {
						$params = null;
					}
					$this->log->pushProcessor(new WebProcessor($params));
				break;

			}
		}
	}
}
