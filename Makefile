DOCKER_COMPOSE_COMMAND=docker compose
PHP_CONTAINER=$(DOCKER_COMPOSE_COMMAND) exec php
PHP_EXEC=$(PHP_CONTAINER) php -d memory_limit=1G -d xdebug.mode=off
COMPOSER=$(PHP_CONTAINER) composer

init:
	make init-env
	make up
	make composer-install

init-env:
	mkdir -p "var"
	mkdir -p "var/log"
	mkdir -p "data"
	cp -n .env.example .env

build:
	@$(DOCKER_COMPOSE_COMMAND) build

up:
	@$(DOCKER_COMPOSE_COMMAND) up -d --remove-orphans

down:
	@$(DOCKER_COMPOSE_COMMAND) down --remove-orphans

shell:
	@$(DOCKER_COMPOSE_COMMAND) exec -it php sh

analyze-code:
	@$(PHP_EXEC) vendor/bin/phpstan analyse --memory-limit 1G

fix-style:
	@PHP_CS_FIXER_IGNORE_ENV=1 $(PHP_EXEC) vendor/bin/php-cs-fixer --show-progress=dots -v fix

check-style:
	@PHP_CS_FIXER_IGNORE_ENV=1 $(PHP_EXEC) vendor/bin/php-cs-fixer --show-progress=dots -v fix --dry-run --diff

test:
	@$(PHP_EXEC) vendor/bin/phpunit

test-unit:
	@$(PHP_EXEC) vendor/bin/phpunit --testsuite Unit

migrate:
	@$(DOCKER_COMPOSE_COMMAND) --profile migrate run --rm liquibase

code-setup:
	make analyze-code
	make fix-style

restart:
	make down
	make up

composer-dump-autoload:
	@$(COMPOSER) dump-autoload --classmap-authoritative

composer-install:
	@$(COMPOSER) install --optimize-autoloader

composer-update:
	@$(COMPOSER) update --optimize-autoloader

cache-clear:
	@$(PHP_EXEC) bin/console cache:clear

routes:
	@$(PHP_EXEC) bin/console debug:router
