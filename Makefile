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
	@echo "     'make cs-check-path FPATH=<path>' (run code checking on specific path)"
	@echo "     'make cs-check-path-emacs FPATH=<path>' (same as cs-check-path"
	@echo "             with emacs output)"
	@echo "     'make cs-check-path-full FPATH=<path>' (run intensive code checking on"
	@echo "             specific path)"
	@echo "     'make cs-check-all' (run complete code checking)"
	@echo "     'make cs-check-commit-intensive' (run complete code checking with"
	@echo "             stricter coding standard)"
	@echo "     'make cs-check-blame' (get blame list)"


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

cs-default:
	chmod ugo+x "$(CSSPATH)cs-scripts.sh"
	
cs-install: cs-default
	$(CSSPATH)cs-scripts.sh -i
	
cs-install-submodule: cs-default
	$(CSSPATH)cs-scripts.sh -f $(CSSPATH) -m $(MPATH)

cs-uninstall-submodule: cs-default
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
	$(CSSPATH)cs-scripts.sh -p "-s5"

cs-check-path:
	$(CSSPATH)cs-scripts.sh -c "--report=summary $(FPATH)"
cs-check-path-emacs:
	$(CSSPATH)cs-scripts.sh -c "--report=emacs $(FPATH)"
cs-check-path-full:
	$(CSSPATH)cs-scripts.sh -c "--report=full $(FPATH)"

cs-check-all:
	$(CSSPATH)cs-scripts.sh -c "--ignore=$(IGNOREPATTERN) --report=summary *"
cs-check-all-intensive:
	$(CSSPATH)cs-scripts.sh -s -c "--ignore=$(IGNOREPATTERN) --report=summary *"

cs-check-blame:
	$(CSSPATH)cs-scripts.sh -s -c "--ignore=$(IGNOREPATTERN) --report=gitblame -v *"

cs-check-blame-full:
	$(CSSPATH)cs-scripts.sh -s -c "--ignore=$(IGNOREPATTERN) --report=gitblame *"
