.PHONY: help
help: #! Show this help message
	@echo 'Usage: make [target] ... '
	@echo ''
	@echo 'Targets:'
	@grep -h -F '#!' $(MAKEFILE_LIST) | grep -v grep | sed 's/:.*#!/:/' | column -t -s":"

.PHONY: test
test: #! Run unit tests
	php vendor/bin/phpunit

.PHONY: coverage
coverage: #! Run unit tests with test coverage
	php -d xdebug.mode=coverage vendor/bin/phpunit

.PHONY: lint
lint: #! Run quality control tools (e.g. psalm)
	./vendor/bin/psalm --no-cache
	composer cs-check
