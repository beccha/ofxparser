# Docker
.PHONY: init
init: start composer-install

.PHONY: shell
shell:
	docker exec -ti $(shell docker ps -qf "name=php") /bin/sh

.PHONY: down
down:
	@echo -e '\n\e[1;96m>>Stop containers\e[0m'
	docker compose down --remove-orphans

.PHONY: start
start:
	@echo -e '\n\e[1;96m>> Start containers\e[0m'
	docker compose up -d

.PHONY: build
build:
	@echo -e '\n\e[1;96m>> Build containers\e[0m'
	docker compose build --no-cache


# Deploy vendor
.PHONY: composer-install
composer-install:
	@echo -e '\n\e[1;96m>> Install composer dependencies\e[0m'
	docker exec $(shell docker ps -qf "name=php") composer install --prefer-source --no-interaction


# Code quality
.PHONY: unit
unit:
	@echo -e '\n\e[1;96m>> Run unit tests\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpunit

.PHONY: fix
fix:
	@echo -e '\n\e[1;96m>> Fix code style\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpcbf

.PHONY: phpstan
phpstan:
	@echo -e '\n\e[1;96m>> Check code quality\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpstan

.PHONY: phpcs
phpcs:
	@echo -e '\n\e[1;96m>> Check code style\e[0m'
	docker exec $(shell docker ps -qf "name=php") touch ./var/.phpcs.cache
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpcs

.PHONY: composer
composer:
	@echo -e '\n\e[1;96m>> Check composer\e[0m'
	docker exec $(shell docker ps -qf "name=php") composer validate --strict
	docker exec $(shell docker ps -qf "name=php") composer audit

.PHONY: coverage
coverage:
	@echo -e '\n\e[1;96m>> Test coverage\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpunit --coverage-text

.PHONY: coverage-html
coverage-html:
	@echo -e '\n\e[1;96m>> Test coverage - HTML Report\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpunit --coverage-html var/report

.PHONY: quality-check
quality-check: phpstan phpcs coverage composer

.PHONY: sonar-reports
sonar-reports:
					docker exec $(shell docker ps -qf "name=php") \
				/bin/sh -c "XDEBUG_MODE=coverage php vendor/bin/phpunit ./tests --coverage-clover=var/artifacts/coverage.xml --log-junit=var/artifacts/unit.xml" \
				/bin/sh -c "sed -i 's+/app/+./+g' var/artifacts/coverage.xml" \
				/bin/sh -c "sed -i 's+/app/+./+g' var/artifacts/unit.xml"
					docker exec $(shell docker ps -qf "name=php") \
				/bin/sh -c "vendor/bin/phpstan analyse --memory-limit 2048M --error-format=json > var/artifacts/phpstan.json" \
				/bin/sh -c "sed -i 's+/app/+./+g' var/artifacts/phpstan.json"
