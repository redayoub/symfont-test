DOCKER_COMPOSE?=docker-compose
EXEC?=$(DOCKER_COMPOSE) exec php-fpm-nginx
COMPOSER=$(EXEC) composer
CONSOLE=bin/console

.DEFAULT_GOAL := help
.PHONY: help install up sh db-create db-migrations run-messenger

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

vendor: composer.json
	$(COMPOSER) install

composer.lock:
	$(COMPOSER) update

install: up vendor db-create db-migrations 				

up: 													
	$(DOCKER_COMPOSE) up -d

sh:														
	$(EXEC) sh

db-create:
	$(EXEC) php bin/console d:d:c

db-migrations:
	$(EXEC) php bin/console d:m:m

run-messenger:
	$(EXEC) php bin/console messenger:consume async -vvv