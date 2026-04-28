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

check-arch:
	@$(PHP_EXEC) vendor/bin/deptrac analyse --config-file=deptrac.php

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

# Накатить миграции на удалённую БД через SSH-туннель.
# Обязательные переменные (передаются через env или make-аргументы):
#   SSH_HOST              — user@remote-server
#   REMOTE_DB_HOST        — хост БД на стороне сервера (по умолчанию localhost)
#   REMOTE_DB_PORT        — порт БД на стороне сервера (по умолчанию 5432)
#   REMOTE_DB_NAME        — имя базы данных
#   REMOTE_DB_USER        — пользователь БД
#   REMOTE_DB_PASSWORD    — пароль БД
# Необязательные:
#   SSH_KEY               — путь к приватному ключу (по умолчанию ~/.ssh/id_rsa)
# Пример:
#   make migrate-remote SSH_HOST=user1@1.2.3.4 SSH_KEY=~/.ssh/prod_key \
#       REMOTE_DB_NAME=mydb REMOTE_DB_USER=myuser REMOTE_DB_PASSWORD=secret
SSH_HOST        ?=
SSH_KEY         ?= ~/.ssh/id_rsa
REMOTE_DB_HOST  ?= localhost
REMOTE_DB_PORT  ?= 5432
REMOTE_DB_NAME  ?=
REMOTE_DB_USER  ?=
REMOTE_DB_PASSWORD ?=
SSH_TUNNEL_LOCAL_PORT ?= 15432

migrate-remote:
	@test -n "$(SSH_HOST)"          || (echo "Ошибка: SSH_HOST не задан"          && exit 1)
	@test -n "$(REMOTE_DB_NAME)"    || (echo "Ошибка: REMOTE_DB_NAME не задан"    && exit 1)
	@test -n "$(REMOTE_DB_USER)"    || (echo "Ошибка: REMOTE_DB_USER не задан"    && exit 1)
	@test -n "$(REMOTE_DB_PASSWORD)"|| (echo "Ошибка: REMOTE_DB_PASSWORD не задан"&& exit 1)
	@echo "Открываем SSH-туннель $(SSH_TUNNEL_LOCAL_PORT) → $(SSH_HOST):$(REMOTE_DB_HOST):$(REMOTE_DB_PORT)..."
	@ssh -f -N \
		-i $(SSH_KEY) \
		-L 0.0.0.0:$(SSH_TUNNEL_LOCAL_PORT):$(REMOTE_DB_HOST):$(REMOTE_DB_PORT) \
		$(SSH_HOST) \
		-o ExitOnForwardFailure=yes \
		-o ServerAliveInterval=15
	@echo "Туннель открыт. Ждём готовности порта $(SSH_TUNNEL_LOCAL_PORT)..."
	@for i in $$(seq 1 15); do \
		nc -z 127.0.0.1 $(SSH_TUNNEL_LOCAL_PORT) 2>/dev/null && break; \
		echo "  Попытка $$i/15..."; \
		sleep 1; \
	done
	@nc -z 127.0.0.1 $(SSH_TUNNEL_LOCAL_PORT) 2>/dev/null || (echo "Ошибка: туннель не поднялся за 15 секунд" && pkill -f "ssh.*$(SSH_TUNNEL_LOCAL_PORT)" 2>/dev/null; exit 1)
	@echo "Порт готов. Запускаем миграции..."
	@docker run --rm \
		-v "$(PWD)/migrations:/liquibase/changelog" \
		liquibase/liquibase:4.27 \
		--url="jdbc:postgresql://host.docker.internal:$(SSH_TUNNEL_LOCAL_PORT)/$(REMOTE_DB_NAME)" \
		--username="$(REMOTE_DB_USER)" \
		--password='$(value REMOTE_DB_PASSWORD)' \
		--changelog-file=db.changelog-master.xml \
		--search-path=/liquibase/changelog \
		update \
	; EXIT_CODE=$$? \
	; pkill -f "ssh.*$(SSH_TUNNEL_LOCAL_PORT)" 2>/dev/null || true \
	; echo "SSH-туннель закрыт." \
	; exit $$EXIT_CODE

code-setup:
	make analyze-code
	make fix-style
	make check-arch

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
