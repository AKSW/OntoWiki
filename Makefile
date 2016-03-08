PHPUNIT = ./vendor/bin/phpunit
PHPCS = ./vendor/bin/phpcs
PHPCBF = ./vendor/bin/phpcbf
default:
	@echo "Typical targets your could want to reach:"
	@echo ""
	@echo "-->   make deploy ............... Install OntoWiki <-- in doubt, use this"
	@echo "                                  (use this for server installations)"
	@echo ""
	@echo "      make install .............. Install OntoWiki for developer"
	@echo "                                  (you will need github access and ssh for this)"
	@echo ""
	@echo "      make help ................. Show more (developer related) make targets"
	@echo ""
	@echo "      make help-cs .............. Show help for code sniffing targets"
	@echo ""
	@echo "      make help-test ............ Show help for test related targets"

help:
	@echo "Please use: (e.g. make deploy)"
	@echo "     deploy ..................... Runs everything which is needed for a deployment"
	@echo "     install .................... Make directories and libraries"
	@echo "     help ....................... This help screen"
	@echo "     help-cs .................... Show help for code sniffing targets"
	@echo "     help-test .................. Show help for test related targets"
	@echo "     -------------------------------------------------------------------"
	@echo "     vagrant .................... Prepare environment to run with Vagrant"
	@echo "     vagrant-clean .............. Removes owdev box in order to ensure you have the latest version"
	@echo "     directories ................ Create cache/log dir and chmod environment"
	@echo "     clean ...................... Deletes all log and cache files"
	@echo "     odbctest ................... Executes some tests to check the Virtuoso connection"

help-cs:
	@echo "Please use: (e.g. make codesniffer)"
	@echo "     codesniffer ............................ Run CodeSniffer"
	@echo "     codebeautifier ......................... Run CodeBeautifier"

help-test:
	@echo "  test ......................... Execute unit, integration and extension tests"
	@echo "  test-unit .................... Run OntoWiki unit tests"
	@echo "  test-integration-virtuoso .... Run OntoWiki integration tests with virtuoso"
	@echo "  test-integration-mysql ....... Run OntoWiki integration tests with mysql"
	@echo "  test-extensions .............. Run tests for extensions"

# top level target

getcomposer: #seems that there is no way to constantly get the newest version(install way outdates with new versions)
	php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
	php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '41e71d86b40f28e771d4bb662b997f79625196afcca95a5abf44391188c695c6c1456e16154c75a211d238cc3bc5cb47') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); }"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
	php composer.phar self-update

install: deploy

deploy: directories clean composer-install

vagrant: directories clean #add composer install
	rm -rf libraries/Zend # vagrant has own zend
	rm -f Vagrantfile
	!(ls $(PWD)/application/scripts/Vagrantfile > /dev/null 2> /dev/null) || ln -s $(PWD)/application/scripts/Vagrantfile $(PWD)/Vagrantfile
	(ls $(PWD)/Vagrantfile > /dev/null 2> /dev/null) || ln -s $(PWD)/application/scripts/Vagrantfile-dist $(PWD)/Vagrantfile
	(ls $(HOME)/.vagrant.d/boxes/owdev > /dev/null 2> /dev/null) || vagrant box add owdev http://files.ontowiki.net/owdev.box
	@echo ""
	@echo '=> Now type "vagrant up"'

vagrant-clean:
	rm -f Vagrantfile
	vagrant box remove owdev

clean:
	rm -rf cache/* logs/* libraries/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

composer-install: #add difference for user and dev (with phpunit etc and without)
	php composer.phar install
# test stuff

test-directories:
	rm -rf application/tests/cache application/tests/unit/cache application/tests/integration/cache
	mkdir -p application/tests/cache application/tests/unit/cache application/tests/integration/cache

test-unit: test-directories
	$(PHPUNIT) --testsuite "OntoWiki Unit Tests"

test-integration-virtuoso: test-directories
	EF_STORE_ADAPTER=virtuoso $(PHPUNIT) --testsuite "OntoWiki Virtuoso Integration Tests"

test-integration-mysql: test-directories
	EF_STORE_ADAPTER=zenddb $(PHPUNIT) --testsuite "OntoWiki Virtuoso Integration Tests"

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


# coding standard
STANDARD =application/tests/CodeSniffer/Standards/Ontowiki/ruleset.xml
# #### config ####

# ignore pattern
IGNOREPATTERN =libraries,extensions/exconf/pclzip.lib.php,extensions/exconf/Archive.php,application/scripts,extensions/markdown/parser/markdown.php,vendor

REQUESTSTR =-p --standard=$(STANDARD) --ignore=$(IGNOREPATTERN) --extensions=php */

codesniffer:
	$(PHPCS) $(REQUESTSTR)

codebeautifier:
	$(PHPCBF) $(REQUESTSTR)

# other stuff

list-events:
	@grep -R "new Erfurt_Event" * 2> /dev/null | sed "s/.*new Erfurt_Event('//;s/');.*//" | sort -u
