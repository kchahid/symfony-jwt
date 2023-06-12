DEFAULT_GOAL := help

DOCKER_COMPOSE_FILE=./docker/docker-compose.yml
PROJECT_NAME=app

# COLORS
RESET = \033[0m
BLUE = \033[36m
GREEN = \033[0;32m
RED = \033[0;31m

.PHONY: all clean fclean re

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make ${BLUE}[target]${RESET}\n"}/^[a-zA-Z0-9_-]+:.*?##/ \
	{ printf "  ${RED}%-10s${RESET}%s\n", $$1, $$2 } /^##@/ \
	{ printf "\n${BLUE}%s${RESET}\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

all: ## build & run & install
	@$(MAKE) build
	@$(MAKE) start
	@$(MAKE) install

build: ## build images and containers
	@docker-compose -f $(DOCKER_COMPOSE_FILE) -p ${PROJECT_NAME} build --compress --force-rm --no-cache --pull
	@$(MAKE) clean

install: ## install dependencies
	@docker exec -it ${PROJECT_NAME} composer install

start: ## start the containers
	@docker-compose -f $(DOCKER_COMPOSE_FILE) -p ${PROJECT_NAME} up -d

stop: ## stop the containers
	@docker-compose -f $(DOCKER_COMPOSE_FILE) -p ${PROJECT_NAME} down

restart: ## restart the containers
	@$(MAKE) stop
	@$(MAKE) start

exec: ## access to the container
	@docker exec -it ${PROJECT_NAME} /bin/sh

logs: ## display the logs in bash
	@docker-compose -f $(DOCKER_COMPOSE_FILE) -p ${PROJECT_NAME} logs -f

clean: ## clean docker
	@docker system prune --volumes --force

fclean: clean
	@rm -rf ./var
	@rm -rf ./vendor/

re:
	@$(MAKE) stop
	@$(MAKE) fclean
	@$(MAKE) all
