<?php
use Monolog\Logger;
use Monolog\Handler\MailHandler;

/**
 * CakeEmailHandler uses CakeEmail to send the emails.
 *
 * Use it like so:
 *
 * CakeLog::config('web', array(
 *    'engine' => 'Monolog.MonologLogger',
 *    'channel' => 'web',
 *    'handlers' => array(
 *        'CakeEmailHandler' => array(
 *            "webmaster@domain.com",
 *            "ALERT: IMMEDIATE ACTION REQUIRED.",
 *            'default',
 *            'search' => CakePlugin::path('Monolog') . DS . 'Lib' . DS . 'Log' . DS . 'Handler'
 *        )
 *    )
 *));
 *
*/
class CakeEmailHandler extends \Monolog\Handler\MailHandler {

    protected $_to;
    protected $_subject;
    protected $_config;

    /**
     * @param string|array $to      The receiver of the mail
     * @param string       $subject The subject of the mail
     * @param string       $from    The CakeEmail configuration to use
     * @param integer      $level   The minimum logging level at which this handler will be triggered
     * @param boolean      $bubble  Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($to, $subject, $config = 'default', $level = Logger::ERROR, $bubble = true) {
        parent::__construct($level, $bubble);
        $this->_to = $to;
        $this->_subject = $subject;
        $this->_config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function send($content, array $records) {
        $email = 'CakeEmail';
        if (Configure::check('Email.classname')) {
            $email = Configure::read('Email.classname');
        }
        $email::deliver($this->_to, $this->_subject, $content, $this->_config);
    }

}
