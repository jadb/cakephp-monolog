# CakePHP Monolog Plugin

Despite the very advanced logging system offered in [CakePHP][1], I still would have had to write
a lot more code to be able to handle logs the way I needed. To write the least code possible, I
chose to go with the popular monolog library.

## Install

Because [monolog][2] is a [composer][3] [package][4] and to avoid having to manually write a lot of
includes (vs. auto-loading), I decided to release this also as a composer package and take advantage
of the auto-loading magic.

First, add this plugin as a requirement to your `composer.json`:

	{
		"require": {
			"cakephp/monolog": "*"
		}
	}

And then update:

	php composer.phar update

That's it! You should now be ready to start configuring your channels.

## Configuration

Start by creating a logging configuration file (i.e. `app/Config/log.php`) that you will include early
in your `app/Config/bootstrap.php`:

```
include 'log.php';
```

A basic configuration, to replicate what Cake does but using Monolog (to give you a good starting
example), would look something like this:

```
CakePlugin::load('Monolog');

CakeLog::config('debug', array(
	'engine' => 'Monolog.Monolog',
	'channel' => 'app',
	'handlers' => array(
		'Stream' => array(
			LOGS . 'debug.log',
			'formatters' => array(
				'Line' => array("%datetime% %channel% %level_name%: %message%\n")
			)
		)
	)
));
```

Note that with CakePHP versions < 2.4 the engine name should instead be `Monolog.MonologLog`.

Simple, no? But let's really do some serious logging, otherwise why bother moving away from the
default [CakePHP logging][5] system?

The example below shows how to setup:

* rotating logs that are kept from 30 days and readable by [logstash][6] with memory peak usage info
* normal log file with much more details about the request
* email notifications for critical and alert levels including only the error message

```
CakeLog::config('logstash', array(
	'engine' => 'Monolog.Monolog',
	'channel' => 'app',
	'handlers' => array(
		'RotatingFile' => array(
			LOGS . 'logstash.log',
			30,
			'formatters' => array(
				'Logstash' => array('web', env('SERVER_ADDR'))
			),
			'processors' => array('MemoryPeakUsage')
		),
		'Stream' => array(
			LOGS . 'logstash.log',
			'formatters' => array(
				'Line' => array("%datetime% %channel% %level_name%: %message% %context% %extra%\n")
			),
			'processors' => array('MemoryUsage', 'Web')
		),
		'CakeEmail' => array(
			'admin@domain.com',
			'ALERT: APPLICATION REQUIRES IMMEDIATE ATTENTION.',
			'default'
		)
	)
));
```

The [`CakeEmailHandler`][7] was [just submitted][8] to the main [monolog][2] repo today. If it is not
merged by the time you are reading this, just use [my fork][9].

[1]:http://cakephp.org
[2]:https://github.com/Seldaek/monolog
[3]:http://getcomposer.org
[4]:https://packagist.org/packages/monolog/monolog
[5]:http://book.cakephp.org/2.0/en/core-libraries/logging.html
[6]:http://logstash.net
[7]:https://github.com/jadb/monolog/blob/master/src/Monolog/Handler/CakeEmailHandler.php
[8]:https://github.com/Seldaek/monolog/pull/162
[9]:https://github.com/jadb/monolog
