ZENDVERSION=1.11.5

default:
	@echo "please use:"
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
	@echo "     'make cs-enable' (enable CodeSniffer to check code before every commit)"
	@echo "     'make cs-disable' (disable CodeSniffer code checking)"
	@echo "     'make cs-check-commit' (run pre ciommit code checking manually)"
	@echo "     'make cs-check-commit-intensive' (run pre ciommit code checking"
	@echo "             manually with stricter coding standard)"
	@echo "     'make cs-check-all' (run complete code checking)"
	@echo "     'make cs-check-commit-intensive' (run complete code checking with"
	@echo "             stricter coding standard)"


# top level target

install: directories libraries

clean:
	rm -rf cache/* logs/*

directories: clean
	mkdir -p logs cache
	chmod 777 logs cache extensions

libraries: zend erfurt rdfauthor


# developer targets

pull:
	git pull
	cd libraries/RDFauthor && git pull
	cd libraries/Erfurt && git pull

update: pull

force-update: pull

status:
	git status
	cd libraries/RDFauthor && git status
	cd libraries/Erfurt && git status

branch-check:
	git rev-parse --abbrev-ref HEAD
	git --work-tree=libraries/Erfurt rev-parse --abbrev-ref HEAD
	git --work-tree=libraries/RDFauthor rev-parse --abbrev-ref HEAD

# libraries

zend:
	rm -rf libraries/Zend
	curl -O http://framework.zend.com/releases/ZendFramework-${ZENDVERSION}/ZendFramework-${ZENDVERSION}-minimal.tar.gz || wget http://framework.zend.com/releases/ZendFramework-${ZENDVERSION}/ZendFramework-${ZENDVERSION}-minimal.tar.gz
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
severity = 7
# intensive severity class they must not be fulfilled to be able to commit,
# but you are able to check your code with additional coding standards
severity_intensive = 5
# checkt filetypes
filetypes = php
# path to the Ontowiki Coding Standard
cspath = application/tests/CodeSniffer/Standards/Ontowiki

cs-install: cs-enable
	pear install PHP_CodeSniffer

cs-uninstall: cs-disable

cs-enable:
	./application/tests/CodeSniffer/add-hook.sh

cs-disable:
	./application/tests/CodeSniffer/remove-hook.sh

cs-check-commit:
	hg status -nam | grep '\.$(filetypes)$\' | xargs --no-run-if-empty phpcs --report=full --severity=$(severity) -p -s --standard=$(cspath)
cs-check-commit-intensive:
	hg status -nam | grep '\.$(filetypes)$\' | xargs --no-run-if-empty phpcs --report=full --severity=$(severity_intensive) -p -s --standard=$(cspath)

cs-check-all:
	phpcs --report=summary --extensions=$(filetypes) --severity=$(severity) -s -p --standard=$(cspath) *
cs-check-all-intensive:
	phpcs --report=summary --extensions=$(filetypes) --severity=$(severity_intensive) -s -p --standard=$(cspath) *
