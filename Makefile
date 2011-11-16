ZENDVERSION=1.11.5

default:
	@echo "Typical targets your could want to reach:"
	@echo ""
	@echo "--> 'make deploy' : install OntoWiki <-- in doubt, use this"
	@echo "                    (use this for server installations)"
	@echo "    'make install': install OntoWiki for developer"
	@echo "                    (you will need github access and ssh for this)"
	@echo "    'make help'   : show more (developer related) make targets"
	@echo "                    (this includes all code sniffing targets)"

help:
	@echo "please use:"
	@echo "     'make deploy' (-> runs everything which is needed for a deployment)"
	@echo "     'make install' (-> make directories, zend and libraries)"
	@echo "     'make directories' (create cache/log dir and chmod environment)"
	@echo "     'make zend' (download and install Zend under libraries)"
	@echo "     'make libraries' ('git clone' all subrepos - in case submodules do not work)"
	@echo "     'make erfurt' (clone under libraries)"
	@echo "     'make rdfauthor' (clone under libraries)"
	@echo "     'make pull' ('git pull' for all repos)"
	@echo "     'make status' ('git status' for all repos)"
	@echo "     'make branch-check' ('git rev-parse' for all repos)"
	@echo "     'make clean' (deletes all log and cache files)"
	@echo "     'make cs-install' (install CodeSniffer)"
	@echo "     'make cs-uninstall' (uninstall CodeSniffer)"
	@echo "     'make cs-install-submodule MPATH=<path>' (install CodeSniffer on a submodule,"
	@echo "             <path> must by the relativ path to the submodule)"
	@echo "     'make cs-uninstall-submodule MPATH=<path>' (uninstall CodeSniffer on a submodule,"
	@echo "             <path> must by the relativ path to the submodule)"
	@echo "     'make cs-enable' (enable CodeSniffer to check code before every commit)"
	@echo "     'make cs-disable' (disable CodeSniffer code checking)"
	@echo "     'make cs-check-commit' (run pre-commit code checking manually)"
	@echo "     'make cs-check-commit-emacs' (same as cs-check-commit with emacs output)"
	@echo "     'make cs-check-commit-intensive' (run pre-commit code checking"
	@echo "             manually with stricter coding standard)"
	@echo "     'make cs-check (run complete code checking)"
	@echo "     'make cs-check-intensive' (run complete code checking with"
	@echo "             stricter coding standard)"
	@echo "     'make cs-check-full' (run complete code checking with detailed output)"
	@echo "     'make cs-check-emacs' (run complete code checking with with emacs output)"
	@echo "     'make cs-check-blame' (run complete code checking with blame list output)"
	@echo "     'make cs-check' (run complete code checking)"
	@echo "     'possible Parameter:"
	@echo "     'FPATH=<path>' (run code checking on specific relative path)"
	@echo "     'SNIFFS=<sniff 1>,<sniff 2>' (run code checking on specific sniffs)"
	@echo "     'OPTIONS=<option>' (run code checking with specific CodeSniffer options)"
	


# top level target

deploy: directories clean zend
	rm -rf libraries/RDFauthor
	@echo 'Cloning RDFauthor into libraries/RDFauthor ...'
	git clone git://github.com/AKSW/RDFauthor.git libraries/RDFauthor
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	git clone git://github.com/AKSW/Erfurt.git libraries/Erfurt


install: directories libraries

clean:
	rm -rf cache/* logs/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

libraries: zend submodules

submodules:
	git submodule init
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

erfurt:
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	git clone git@github.com:AKSW/Erfurt.git libraries/Erfurt

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
IGNOREPATTERN = */libraries/*

# Parameter check
ifndef FPATH
	FPATH = "*"
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
cs-check-full:
	$(CSSPATH)cs-scripts.sh -c "-s -v --report=full $(REQUESTSTR)"
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
