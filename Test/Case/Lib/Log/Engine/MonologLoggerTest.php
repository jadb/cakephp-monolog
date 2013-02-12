<?php
App::uses('MonologLogger', 'Monolog.Log/Engine');

use Monolog\Logger;

class MonologLoggerTest extends CakeTestCase {

	public $logs = null;

	public function setUp() {
		$this->logs = LOGS;
		$this->rotate = sprintf('rotate-%s-%s-%s', date('Y'), date('m'), date('d'));
		$this->tearDown();
	}

	public function tearDown() {
		$files = array(
			'error',
			'monolog',
			$this->rotate
		);
		foreach ($files as $file) {
			if (file_exists($this->logs . $file . '.log')) {
				unlink($this->logs . $file . '.log');
			}
		}
	}

	public function testWritingWithDefaultHandler() {
		$filename = $this->logs . 'monolog.log';
		$log = new MonologLogger();
		$log->write('warning', 'Test warning');
		$this->assertTrue(file_exists($filename));

		$result = file_get_contents($filename);
		$this->assertRegExp('/^\[2[0-9]{3}-[0-9]+-[0-9]+ [0-9]+:[0-9]+:[0-9]+\] monolog\.WARNING: Test warning \[\] \[\]/', $result);
	}

	public function testWritingWithCustomHandlers() {
		$options = array(
			'channel' => 'database',
			'handlers' => array(
				'Stream' => array($this->logs . 'error.log'),
				'RotatingFile' => array($this->logs . 'rotate.log', 0, 400, false),
			),
			'processors' => array('Web')
		);

		$log = new MonologLogger($options);

		$log->write('warning', 'Test warning');
		$this->assertTrue(file_exists($this->logs . 'error.log'));
		$this->assertFalse(file_exists($this->logs . $this->rotate . '.log'));

		$this->tearDown();

		$log->write('critical', 'Test critical');
		$this->assertFalse(file_exists($this->logs . 'error.log'));
		$this->assertTrue(file_exists($this->logs . $this->rotate . '.log'));
	}

}
