# CakePHP Monolog Plugin

Despite the very advanced logging system offered in CakePHP, I still would have had to write a lot more
code to be able to handle logs the way I needed. To write the least code possible, I chose to go with the
popular monolog library.

## Install

Because monolog is a composer package and to avoid having to manually write a lot of includes (vs. auto-
loading), I decided to release this also as a composer package and take advantage of the auto-loading
magic.

First, add this plugin as a requirement to your `composer.json`:

	{
		"require": {
			"cakephp/monolog": "*"
		}
	}

And then update:

	php composer.phar update

That's it!

## Setup

Now, here's the tricky part. You can either define to use all integrated handlers and processors at once:

	CakePlugin::load('Monolog');

Ok, you should now be ready to start configuring your channels.
