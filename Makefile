PHPUNIT = ./vendor/bin/phpunit
PHPCS = ./vendor/bin/phpcs
PHPCBF = ./vendor/bin/phpcbf

# Get calid composer executable
COMPOSER = $(shell which composer)
ifeq ($(findstring composer, $(COMPOSER)), )
    COMPOSER = $(shell which composer.phar)
    ifeq ($(findstring composer.phar, $(COMPOSER)), )
        ifneq ($(wildcard composer.phar), )
            COMPOSER = php composer.phar
        else
            COMPOSER =
        endif
    endif
endif

default:
	@echo "Typical targets your could want to reach:"
	@echo ""
	@echo "-->   make deploy ............... Install OntoWiki <-- in doubt, use this"
	@echo "      make install .............. deploy and install are equivalent"
	@echo ""
	@echo "      make help ................. Show more (developer related) make targets"
	@echo ""
	@echo "      make help-cs .............. Show help for code sniffing targets"
	@echo ""
	@echo "      make help-test ............ Show help for test related targets"

help:
	@echo "Please use: (e.g. make deploy)"
	@echo "     deploy ..................... Runs everything which is needed for a deployment"
	@echo "     install .................... Equivalent to deploy"
	@echo "     help ....................... This help screen"
	@echo "     help-cs .................... Show help for code sniffing targets"
	@echo "     help-test .................. Show help for test related targets"
	@echo "     -------------------------------------------------------------------"
	@echo "     directories ................ Create cache/log dir and chmod environment"
	@echo "     clean ...................... Deletes all log and cache files"
	@echo "     odbctest ................... Executes some tests to check the Virtuoso connection"

help-cs:
	@echo "Please use: (e.g. make codesniffer)"
	@echo "     codesniffer ............................ Run CodeSniffer except for the FileCommentSniff"
	@echo "			codesniffer_year ....................... Run CodeSniffer including the FileCommentSniff"
	@echo "     codebeautifier ......................... Run CodeBeautifier"

help-test:
	@echo "  test ......................... Execute unit, integration and extension tests"
	@echo "  test-unit .................... Run OntoWiki unit tests"
	@echo "  test-integration-virtuoso .... Run OntoWiki integration tests with virtuoso"
	@echo "  test-integration-mysql ....... Run OntoWiki integration tests with mysql"
	@echo "  test-integration-stardog ..... Run OntoWiki integration tests with stardog"
	@echo "  test-extensions .............. Run tests for extensions"

getcomposer:
	curl -o composer.phar "https://getcomposer.org/composer.phar"
	php composer.phar self-update

install: deploy

ifdef COMPOSER
deploy: directories clean composer-install
else
deploy: getcomposer
	make deploy
endif

clean:
	rm -rf cache/* logs/* libraries/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

ifdef COMPOSER
composer-install: #add difference for user and dev (with phpunit etc and without)
	$(COMPOSER) install
else
composer-install:
	@echo
	@echo
	@echo "!!! make $@ failed !!!"
	@echo
	@echo "Sorry, there doesn't seem to be a PHP composer (dependency manager for PHP) on your system!"
	@echo "Please have a look at http://getcomposer.org/ for further information,"
	@echo "or just run 'make getcomposer' to download the composer locally"
	@echo "and run 'make $@' again"
endif
# test stuff

devenv:
	git clone "https://github.com/pfrischmuth/ontowiki-devenv.git" devenv
	cp -i devenv/config.ini.dist ./config.ini
	cp -i devenv/config-test.ini.dist ./application/tests/config.ini

test-directories:
	rm -rf application/tests/cache application/tests/unit/cache application/tests/integration/cache
	mkdir -p application/tests/cache application/tests/unit/cache application/tests/integration/cache

test-unit: test-directories
	$(PHPUNIT) --testsuite "OntoWiki Unit Tests"

test-integration-virtuoso: test-directories
	EF_STORE_ADAPTER=virtuoso $(PHPUNIT) --testsuite "OntoWiki Virtuoso Integration Tests"

test-integration-mysql: test-directories
	EF_STORE_ADAPTER=zenddb $(PHPUNIT) --testsuite "OntoWiki Virtuoso Integration Tests"

test-integration-stardog: test-directories
	EF_STORE_ADAPTER=stardog $(PHPUNIT) --testsuite "OntoWiki Virtuoso Integration Tests"

test-extensions: #directories
	$(PHPUNIT) --testsuite "OntoWiki Extensions Tests"

test:
	make test-unit
	@echo ""
	@echo "-----------------------------------"
	@echo ""
	make test-integration-virtuoso
	@echo ""
	@echo "-----------------------------------"
	@echo ""
	make test-integration-mysql
	@echo ""
	@echo "-----------------------------------"
	@echo ""
	make test-integration-stardog
	@echo ""
	@echo "-----------------------------------"
	@echo ""
	make test-extensions


odbctest:
	@application/scripts/odbctest.php

# packaging

debianize:
	rm extensions/markdown/parser/License.text
	rm extensions/markdown/parser/PHP_Markdown_Readme.txt
	rm extensions/markdown/parser/markdown.php
	rm extensions/queries/resources/codemirror/LICENSE
	rm extensions/themes/silverblue/scripts/libraries/jquery-1.9.1.js
	rm libraries/RDFauthor/libraries/jquery.js
	rm Makefile
	@echo "now do: cp -R application/scripts/debian debian"

# #### config ####

codesniffer:
	$(PHPCS) -p

codebeautifier:
	$(PHPCBF)

# other stuff

list-events:
	@grep -R "new Erfurt_Event" * 2> /dev/null | sed "s/.*new Erfurt_Event('//;s/');.*//" | sort -u
