PLUGIN=Monolog

default:
	@composer

composer: composer.json
	@if [ ! -f "composer.phar" ]; then make composer_install; else make composer_update; fi

composer_install: composer.json
	@echo "Installing composer"
	@curl -s http://getcomposer.org/installer | php
	@php composer.phar install

composer_update: composer.phar
	@php composer.phar update

jenkins:
	@make composer
	@if [ ! -d "../workspace.build" ]; then \
		git clone --depth=2 https://github.com/cakephp/cakephp.git ../workspace.build && \
		ln -s ${WORKSPACE} ../workspace.build/app/Plugin/${PLUGIN}; \
	fi
	@cd ../workspace.build/app; Console/cake test Monolog Lib/Log/Engine/MonologLogger
	@cd ${WORKSPACE}
