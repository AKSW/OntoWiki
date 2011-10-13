ZENDVERSION=1.11.5

default:
	@echo "please use:"
	@echo "     'make install' (-> make directories, zend and libraries)"
	@echo "     'make install-dev' (-> same as install but writeable)"
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

install: directories libraries

install-dev: directories libraries-dev

clean:
	rm -rf cache/* logs/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

libraries: zend rdfauthor erfurt 

libraries-dev: zend rdfauthor-dev erfurt-dev submodules

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
	git clone git://github.com/AKSW/RDFauthor.git libraries/RDFauthor

rdfauthor-dev:
	rm -rf libraries/RDFauthor
	@echo 'Cloning RDFauthor into libraries/RDFauthor ...'
	git clone git@github.com:AKSW/RDFauthor.git libraries/RDFauthor

erfurt:
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	git clone git://github.com/AKSW/Erfurt.git libraries/Erfurt

erfurt-dev:
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
# if severity classes were chanced aou must run 'cs-install' again
# standard severity class they must be fulfilled to be able to commit
SEVERITY = 7
# intensive severity class they must not be fulfilled to be able to commit,
# but you are able to check your code with additional coding standards
SEVERITY_INTENSIVE = 5
# checkt filetypes
FILETYPES = php
# path to the Ontowiki Coding Standard
CSPATH = application/tests/CodeSniffer/Standards/Ontowiki

cs-install: cs-enable
	pear install PHP_CodeSniffer

cs-uninstall: cs-disable

cs-enable:
	ln -s "../../application/tests/CodeSniffer/pre-commit" .git/hooks/pre-commit

cs-disable:
	rm .git/hooks/pre-commit

cs-check-commit:
	application/tests/CodeSniffer/pre-commit
cs-check-commit-emacs:
	application/tests/CodeSniffer/pre-commit -remacs
cs-check-commit-intensive:
	application/tests/CodeSniffer/pre-commit -s5

cs-check-path:
	phpcs --report=summary --extensions=$(FILETYPES) --severity=$(SEVERITY) -s -p --standard=$(CSPATH) $(FPATH)
cs-check-path-emacs:
	phpcs --report=emacs --extensions=$(FILETYPES) --severity=$(SEVERITY) -s -p --standard=$(CSPATH) $(FPATH)
cs-check-path-full:
	phpcs --report=full --extensions=$(FILETYPES) --severity=$(SEVERITY) -s -p --standard=$(CSPATH) $(FPATH)

cs-check-all:
	phpcs --report=summary --extensions=$(FILETYPES) --severity=$(SEVERITY) -s -p --standard=$(CSPATH) *
cs-check-all-intensive:
	phpcs --report=summary --extensions=$(FILETYPES) --severity=$(SEVERITY_INTENSIVE) -s -p --standard=$(CSPATH) *

cs-check-blame:
	phpcs --report=gitblame -v --extensions=$(FILETYPES) --severity=$(SEVERITY_INTENSIVE) -s -p --standard=$(CSPATH) *

cs-check-blame-full:
	phpcs --report=gitblame -s --extensions=$(FILETYPES) --severity=$(SEVERITY_INTENSIVE) -s -p --standard=$(CSPATH) *
