.PHONY: init
init: code-init db-init ## Initialize project

.PHONY: code-init
code-init:
	mkdir -p -m 0777 vendor var/log var/cache
	docker-compose -p functionaltests up -d --build --force-recreate --no-deps
	docker-compose -p functionaltests exec php bin/console lexik:jwt:generate-keypair --overwrite
	docker-compose -p functionaltests exec php composer install

.PHONY: up
up: ## Starts project
	docker-compose -p functionaltests up -d

.PHONY: build
build: ## Rebuilds containers
	docker-compose -p functionaltests up -d --build

db-init:
	@echo "Creating databases"
	docker-compose -p functionaltests exec php bin/console doctrine:database:create
	docker-compose -p functionaltests exec php bin/console doctrine:migrations:migrate
	docker-compose -p functionaltests exec php bin/console doctrine:fixtures:load

.PHONY: Turns containers off
down: ## Turns containers off
	docker-compose -p functionaltests down

fixtures:
	docker-compose -p functionaltests exec php bin/console --env=test doctrine:fixtures:load

make-migration:
	docker-compose -p functionaltests exec php bin/console make:migration

migrate:
	docker-compose -p functionaltests exec php bin/console doctrine:migrations:migrate


setup-tests:
	docker-compose -p functionaltests exec php bin/console --env=test doctrine:database:create --if-not-exists
	docker-compose -p functionaltests exec php bin/console --env=test doctrine:schema:create
	docker-compose -p functionaltests exec php bin/console --env=test doctrine:fixtures:load

setup-tests-refresh:
	docker-compose -p functionaltests exec php bin/console --env=test doctrine:schema:create
	docker-compose -p functionaltests exec php bin/console --env=test doctrine:fixtures:load


run-paratest:
	docker-compose -p functionaltests exec php vendor/bin/paratest --no-test-tokens
