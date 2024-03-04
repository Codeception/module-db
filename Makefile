.PHONY: $(filter-out help, $(MAKECMDGOALS))
.DEFAULT_GOAL := help

DOCKER_COMPOSE := $(if $(shell command -v docker-compose 2> /dev/null),docker-compose,docker compose) -f docker-compose.yml
ARCH           := $(shell uname -m)
CURRENT_USER   := $(shell id -u):$(shell id -g)

# Check if docker-compose.override.yml exists and if so, add it to DOCKER_COMPOSE
ifneq (,$(wildcard ./docker-compose.override.yml))
    DOCKER_COMPOSE += -f docker-compose.override.yml
endif

# Support Apple Silicon
ifeq ($(ARCH),arm64)
    DOCKER_COMPOSE += -f docker-compose.amd64.yml
endif

DOCKER_EXEC_PHP_WITH_USER = $(DOCKER_COMPOSE) exec -u $(CURRENT_USER) php bash -c

help: ## Show this help message
	@echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@grep -hE '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-25s\033[0m %s\n", $$1, $$2}'

start: ## Start the containers for testing
	$(MAKE) -i stop
	CURRENT_USER=$(CURRENT_USER) $(DOCKER_COMPOSE) up -d --build --force-recreate --remove-orphans
	$(DOCKER_COMPOSE) run --rm wait -c mysql:3306,postgres:5432,mssql:1433 -t 60
	$(MAKE) vendor

stop: ## Stop and remove containers
	$(DOCKER_COMPOSE) down --remove-orphans --volumes

php-cli: ## Open bash in PHP container
	$(DOCKER_COMPOSE) exec -u $(CURRENT_USER) php bash

vendor: ## Install dependencies
	$(DOCKER_EXEC_PHP_WITH_USER) "composer install --no-interaction --prefer-dist"

test: ## Run the tests
	$(DOCKER_EXEC_PHP_WITH_USER) "php vendor/bin/codecept run"
