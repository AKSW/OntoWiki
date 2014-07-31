ZENDVERSION=1.12.7
ZEND2VERSION=2.2.2

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
	@echo "     install .................... Make directories, zend and libraries"
	@echo "     help ....................... This help screen"
	@echo "     help-cs .................... Show help for code sniffing targets"
	@echo "     help-test .................. Show help for test related targets"
	@echo "     -------------------------------------------------------------------"
	@echo "     vagrant .................... Prepare environment to run with Vagrant (no zend)"
	@echo "     vagrant-clean .............. Removes owdev box in order to ensure you have the latest version"
	@echo "     directories ................ Create cache/log dir and chmod environment"
	@echo "     zend ....................... Download and install Zend under libraries"
	@echo "     libraries .................. Zend, Erfurt and RDFauthor submodules (read-write)"
	@echo "     erfurt ..................... Clone under libraries"
	@echo "     rdfauthor .................. Clone under libraries"
	@echo "     pull ....................... 'git pull' for all repos"
	@echo "     fetch ...................... 'git fetch' for all repos"
	@echo "     fetch-all .................. 'git fetch --all', i.e. fetch all repos on all remotes"
	@echo "     add-upstream ............... Adds standard AKSW git repo as remote/upstream"
	@echo "     status ..................... 'git status' for all repos"
	@echo "     branch-check ............... 'git rev-parse' for all repos"
	@echo "     clean ...................... Deletes all log and cache files"
	@echo "     install-test-environment ... Install neccessary software (PHPUnit,...))"
	@echo "     odbctest ................... Executes some tests to check the Virtuoso connection"

help-cs:
	@echo "Please use: (e.g. make cs-install)"
	@echo "     cs-install ............................ Install CodeSniffer"
	@echo "     cs-uninstall .......................... Uninstall CodeSniffer"
	@echo "     cs-install-submodule MPATH=<path> ..... Install CodeSniffer on a submodule,"
	@echo "                                             <path> must by the relative path to the submodule"
	@echo "     cs-uninstall-submodule MPATH=<path> ... Uninstall CodeSniffer on a submodule,"
	@echo "                                             <path> must by the relative path to the submodule"
	@echo "     cs-enable ............................. Enable CodeSniffer to check code before every commit"
	@echo "     cs-disable ............................ Disable CodeSniffer code checking"
	@echo "     cs-check-commit ....................... Run pre-commit code checking manually"
	@echo "     cs-check-commit-emacs ................. Same as cs-check-commit with emacs output"
	@echo "     cs-check-commit-intensive ............. Run pre-commit code checking"
	@echo "                                             manually with stricter coding standard"
	@echo "     cs-check .............................. Run complete code checking"
	@echo "     cs-check-full ......................... Run complete code checking with detailed output"
	@echo "     cs-check-emacs ........................ Run complete code checking with with emacs output"
	@echo "     cs-check-blame ........................ Run complete code checking with blame list output"
	@echo "     cs-check-intensive .................... Run complete code checking with"
	@echo "                                             stricter coding standard"
	@echo "     cs-check-intensive-full ............... Run complete code checking with"
	@echo "                                             stricter coding standard and detailed output"
	@echo "     possible Parameter:"
	@echo "     > CHECKPATH=<path> ................. Run code checking on specific relative path"
	@echo "     > SNIFFS=<sniff 1>,<sniff 2> ... Run code checking on specific sniffs"
	@echo "     > OPTIONS=<option> ............. Run code checking with specific CodeSniffer options"

help-test:
	@echo "  test ......................... Execute unit, integration and extension tests"
	@echo "  test-unit .................... Run OntoWiki unit tests"
	@echo "  test-unit-cc ................. Same as above plus code coverage report"
	@echo "  test-integration-virtuoso .... Run OntoWiki integration tests with virtuoso"
	@echo "  test-integration-virtuoso-cc . Same as above plus code coverage report"
	@echo "  test-integration-mysql ....... Run OntoWiki integration tests with mysql"
	@echo "  test-integration-mysql-cc .... Same as above plus code coverage report"
	@echo "  test-extensions .............. Run tests for extensions"

# top level target

deploy: directories clean zend submodules

install: directories libraries

vagrant: directories clean submodules-developer
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
	rm -rf cache/* logs/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

libraries: zend submodules-developer

submodules: # read-only
	git submodule init
	git config submodule.libraries/Erfurt.url "https://github.com/AKSW/Erfurt.git"
	git config submodule.libraries/RDFauthor.url "https://github.com/AKSW/RDFauthor.git"
	git submodule update

submodules-developer: # read-write
	git submodule init
	git config submodule.libraries/Erfurt.url "git@github.com:AKSW/Erfurt.git"
	git config submodule.libraries/RDFauthor.url "git@github.com:AKSW/RDFauthor.git"
	git submodule update

# developer targets

pull:
	git pull
	git submodule foreach git pull

fetch:
	git fetch
	git submodule foreach git fetch

fetch-all:
	# Remember to add the aprorpriate upstream sources frist
	# e.g. by using `make add-upstream`
	git fetch --all
	git submodule foreach git fetch --all

add-upstream:
	git remote add upstream git://github.com/AKSW/OntoWiki.git

info:
	@git --no-pager log -1 --oneline --decorate
	@git submodule foreach git --no-pager log -1 --oneline --decorate

status:
	git status -sb
	git submodule foreach git status -sb

branch-check:
	@git rev-parse --abbrev-ref HEAD
	@git submodule foreach git rev-parse --abbrev-ref HEAD

# libraries

ZENDFILEBASE="ZendFramework-${ZENDVERSION}-minimal"
ZENDURL="https://packages.zendframework.com/releases/ZendFramework-${ZENDVERSION}/${ZENDFILEBASE}.tar.gz"
zend:
	rm -rf libraries/Zend
	curl -L -# -O ${ZENDURL} || wget ${ZENDURL}
	tar xzf ${ZENDFILEBASE}.tar.gz
	mv ${ZENDFILEBASE}/library/Zend libraries
	rm -rf ${ZENDFILEBASE}.tar.gz ${ZENDFILEBASE}

ZEND2FILEBASE="ZendFramework-minimal-${ZEND2VERSION}"
ZEND2URL="https://packages.zendframework.com/releases/ZendFramework-${ZEND2VERSION}/${ZEND2FILEBASE}.tgz"
zend2:
	rm -rf libraries/Zend
	curl -L -# -O ${ZEND2URL} || wget ${ZEND2URL}
	tar xzf ${ZEND2FILEBASE}.tgz
	mv ${ZEND2FILEBASE}/library/Zend libraries
	rm -rf ${ZEND2FILEBASE}.tgz ${ZEND2FILEBASE}

rdfauthor:
	rm -rf libraries/RDFauthor
	@echo 'Cloning RDFauthor into libraries/RDFauthor ...'
	git clone git@github.com:AKSW/RDFauthor.git libraries/RDFauthor

# test stuff

test-directories:
	rm -rf application/tests/cache application/tests/unit/cache application/tests/integration/cache
	mkdir -p application/tests/cache application/tests/unit/cache application/tests/integration/cache

test-unit: test-directories
	@cd application/tests && phpunit --bootstrap Bootstrap.php unit/

test-unit-cc: test-directories
	@cd application/tests/unit && phpunit

test-integration-virtuoso: test-directories
	@cd application/tests && EF_STORE_ADAPTER=virtuoso phpunit --bootstrap Bootstrap.php integration/

test-integration-virtuoso-cc: test-directories
	@cd application/tests/integration && EF_STORE_ADAPTER=virtuoso phpunit

test-integration-mysql: test-directories
	@cd application/tests && EF_STORE_ADAPTER=zenddb phpunit --bootstrap Bootstrap.php integration/

test-integration-mysql-cc: test-directories
	@cd application/tests/integration && EF_STORE_ADAPTER=zenddb phpunit

test-extensions: directories
	@phpunit --bootstrap application/tests/Bootstrap.php extensions

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


install-test-environment:
	sudo apt-get install php-pear
	sudo pear config-set auto_discover 1
	sudo pear channel-update pear.php.net
	sudo pear upgrade pear
	sudo pear install -a pear.phpunit.de/PHPUnit
	sudo pear install phpunit/PHPUnit_Selenium
	sudo pear install phpunit/DbUnit

erfurt@:
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	git clone git@github.com:AKSW/Erfurt.git libraries/Erfurt

odbctest:
	@application/scripts/odbctest.php

# packaging

debianize:
	rm extensions/markdown/parser/License.text
	rm extensions/markdown/parser/PHP_Markdown_Readme.txt
	rm extensions/markdown/parser/markdown.php
	rm extensions/queries/resources/codemirror/LICENSE
	rm extensions/themes/silverblue/scripts/libraries/jquery.js
	rm libraries/RDFauthor/libraries/jquery.js
	rm Makefile
	@echo "now do: cp -R application/scripts/debian debian"


# coding standard

# #### config ####
# cs-script path
CSSPATH = application/tests/CodeSniffer/
# ignore pattern
IGNOREPATTERN = libraries,extensions/exconf/pclzip.lib.php,extensions/exconf/Archive.php,application/scripts,extensions/markdown/parser/markdown.php,extensions/queries/lib,extensions/queries/old

# Parameter check
ifndef CHECKPATH
	CHECKPATH = "./"
endif
ifdef SNIFFS
	SNIFFSTR = "--sniffs="$(SNIFFS)
else
	SNIFFSTR =
endif

REQUESTSTR = --ignore=$(IGNOREPATTERN) $(OPTIONS) $(SNIFFSTR)  $(CHECKPATH)

cs-default:
	chmod ugo+x "$(CSSPATH)cs-scripts.sh"

cs-install: cs-default
	$(CSSPATH)cs-scripts.sh -i

cs-install-submodule: cs-submodule-check cs-default
	$(CSSPATH)cs-scripts.sh -f $(CSSPATH) -m $(MPATH)

cs-uninstall-submodule: cs-submodule-check cs-default
	$(CSSPATH)cs-scripts.sh -n $(MPATH)

cs-uninstall: cs-default
	$(CSSPATH)cs-scripts.sh -u

cs-enable: cs-default
	$(CSSPATH)cs-scripts.sh -f $(CSSPATH) -e

cs-disable: cs-default
	$(CSSPATH)cs-scripts.sh -d

cs-check-commit:
	$(CSSPATH)cs-scripts.sh -p ""
cs-check-commit-emacs:
	$(CSSPATH)cs-scripts.sh -p "-remacs"
cs-check-commit-intensive:
	$(CSSPATH)cs-scripts.sh -p "-s"

cs-check:
	@$(CSSPATH)cs-scripts.sh -c "-s --report=summary $(REQUESTSTR)"
cs-check-intensive:
	@$(CSSPATH)cs-scripts.sh -s -c "-s --report=summary $(REQUESTSTR)"
cs-check-intensive-full:
	@$(CSSPATH)cs-scripts.sh -s -c "-s --report=full $(REQUESTSTR)"
cs-check-full:
	@$(CSSPATH)cs-scripts.sh -c "-s --report=full $(REQUESTSTR)"
cs-check-emacs:
	@$(CSSPATH)cs-scripts.sh -c "--report=emacs $(REQUESTSTR)"
cs-check-blame:
	@$(CSSPATH)cs-scripts.sh -c "--report=gitblame $(REQUESTSTR)"

cs-submodule-check:
ifndef MPATH
	@echo "You must Set a path to the submodule."
	@echo "Example: MPATH=path/to/the/submodule/"
	@exit 1
endif

# other stuff

list-events:
	@grep -R "new Erfurt_Event" * 2> /dev/null | sed "s/.*new Erfurt_Event('//;s/');.*//" | sort -u
