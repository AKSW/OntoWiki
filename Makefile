ZENDVERSION=1.11.5

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

help:
	@echo "Please use: (e.g. make deploy)"
	@echo "     deploy ..................... Runs everything which is needed for a deployment"
	@echo "     install .................... Make directories, zend and libraries"
	@echo "     help ....................... This help screen"
	@echo "     help-cs .................... Show help for code sniffing targets"
	@echo "     -------------------------------------------------------------------"
	@echo "     vagrant .................... Prepare environment to run with Vagrant (no zend)"
	@echo "     directories ................ Create cache/log dir and chmod environment"
	@echo "     zend ....................... Download and install Zend under libraries"
	@echo "     libraries .................. Zend, Erfurt and RDFauthor submodules (read-write)"
	@echo "     erfurt ..................... Clone under libraries"
	@echo "     rdfauthor .................. Clone under libraries"
	@echo "     pull ....................... 'git pull' for all repos"
	@echo "     status ..................... 'git status' for all repos"
	@echo "     branch-check ............... 'git rev-parse' for all repos"
	@echo "     clean ...................... Deletes all log and cache files"
	@echo "     install-test-environment ... Install neccessary software (PHPUnit,...))"
	@echo "     test ....................... Executes OntoWiki's TestSuite"
	@echo "     test-erfurt ................ Executes Erfurts TestSuite"
	@echo "     test-extension ............. Executes TestSuites of each extension, if available"
	@echo "     test-all ................... Executes PHPUnit TestSuites (OW, Ext) and CodeSniffer"
	@echo "     odbctest ................... Executes some tests to check the Virtuoso connection"
	
help-cs:
	@echo "Please use: (e.g. make cs-install)"
	@echo "     cs-install ............................ Install CodeSniffer"
	@echo "     cs-uninstall .......................... Uninstall CodeSniffer)"
	@echo "     cs-install-submodule MPATH=<path> ..... Install CodeSniffer on a submodule,"
	@echo "                                             <path> must by the relativ path to the submodule"
	@echo "     cs-uninstall-submodule MPATH=<path> ... Uninstall CodeSniffer on a submodule,"
	@echo "                                             <path> must by the relativ path to the submodule"
	@echo "     cs-enable ............................. Enable CodeSniffer to check code before every commit"
	@echo "     cs-disable ............................ Disable CodeSniffer code checking"
	@echo "     cs-check-commit ....................... Run pre-commit code checking manually"
	@echo "     cs-check-commit-emacs ................. Same as cs-check-commit with emacs output)"
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
	@echo "     > FPATH=<path> ................. Run code checking on specific relative path"
	@echo "     > SNIFFS=<sniff 1>,<sniff 2> ... Run code checking on specific sniffs"
	@echo "     > OPTIONS=<option> ............. Run code checking with specific CodeSniffer options"


# top level target

deploy: directories clean zend submodules

install: directories libraries

vagrant: directories clean submodules-developer
	rm -rf libraries/Zend # vagrant has own zend
	@echo ""
	@echo '=> Now type "vagrant up"'

clean:
	rm -rf cache/* logs/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

libraries: zend submodules-developer

submodules: # read-only
	git submodule init
	git config submodule.libraries/Erfurt.url "git://github.com/AKSW/RDFauthor.git"
	git config submodule.libraries/RDFauthor.url "git://github.com/AKSW/Erfurt.git"
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

zend:
	rm -rf libraries/Zend
	curl -# -O http://framework.zend.com/releases/ZendFramework-${ZENDVERSION}/ZendFramework-${ZENDVERSION}-minimal.tar.gz || wget http://framework.zend.com/releases/ZendFramework-${ZENDVERSION}/ZendFramework-${ZENDVERSION}-minimal.tar.gz
	tar xzf ZendFramework-${ZENDVERSION}-minimal.tar.gz
	mv ZendFramework-${ZENDVERSION}-minimal/library/Zend libraries
	rm -rf ZendFramework-${ZENDVERSION}-minimal.tar.gz ZendFramework-${ZENDVERSION}-minimal

rdfauthor:
	rm -rf libraries/RDFauthor
	@echo 'Cloning RDFauthor into libraries/RDFauthor ...'
	git clone git@github.com:AKSW/RDFauthor.git libraries/RDFauthor

test:
	phpunit

test-extensions:
	phpunit --stderr extensions

test-all: 
	@make test
	@echo ""
	@echo "-----------------------------------"
	@echo ""
	@make test-extensions
	@echo ""
	@echo "-----------------------------------"
	@echo ""
	@make cs-check

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

test-erfurt:
	cd libraries/Erfurt && phpunit && cd ../../..

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
IGNOREPATTERN = */libraries/*,pclzip.lib

# Parameter check
ifndef FPATH
	FPATH = "./"
endif
ifdef SNIFFS
	SNIFFSTR = "--sniffs="$(SNIFFS)
else
	SNIFFSTR =
endif

REQUESTSTR = --ignore=$(IGNOREPATTERN) $(OPTIONS) $(SNIFFSTR)  $(FPATH)

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
	$(CSSPATH)cs-scripts.sh -c "-s --report=summary $(REQUESTSTR)"
cs-check-intensive:
	$(CSSPATH)cs-scripts.sh -s -c "-s --report=summary $(REQUESTSTR)"
cs-check-intensive-full:
	$(CSSPATH)cs-scripts.sh -s -c "-s --report=full $(REQUESTSTR)"
cs-check-full:
	$(CSSPATH)cs-scripts.sh -c "-s --report=full $(REQUESTSTR)"
cs-check-emacs:
	$(CSSPATH)cs-scripts.sh -c "--report=emacs $(REQUESTSTR)"
cs-check-blame:
	$(CSSPATH)cs-scripts.sh -s -c "--report=gitblame $(REQUESTSTR)"

cs-submodule-check:
ifndef MPATH
	@echo "You must Set a path to the submodule."
	@echo "Example: MPATH=path/to/the/submodule/"
	@exit 1 
endif
