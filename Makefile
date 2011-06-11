ZENDVERSION=1.11.5

default:
	@echo "please use:"
	@echo "     'make pull' ('hg pull' for all repos)"
	@echo "     'make update' ('hg pull' and 'hg update' for all repos)"
	@echo "     'make force-update' ('hg pull' and 'hg update -c' for all repos)"
	@echo "     'make status' ('hg status' for all repos)"
	@echo "     'make branch-check' ('hg branch' for all repos)"
	@echo "     'make libraries' ('hg clone' all subrepos - in case of an old mercurial)"
	@echo "     'make zend' (download and install Zend under libraries)"
	@echo "     'make test' (execute 'phpunit TestSuite')"
	@echo "     'make test-erfurt' (execute Erfurts TestSuite)"
	@echo "     'make erfurt' (clone under libraries)"
	@echo "     'make rdfauthor' (clone under libraries)"
	@echo "     'make directories' (create cache/log dir and chmod environment)"
	@echo "     'make install' (-> make directories, zend and libraries)"
	@echo "     'make clean' (deletes all log and cache files)"


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
	@hg --repository . pull
	@hg --config web.cacerts= --repository libraries/RDFauthor pull
	cd libraries/Erfurt && git pull

update: pull
	@echo "\nOntoWiki"
	hg --repository . update
	@echo "\nRDFauthor"
	hg --config web.cacerts= --repository libraries/RDFauthor update

force-update: pull
	@echo "I force the update of the subrepos ..."
	@echo "\nOntoWiki"
	hg --repository . update -c
	@echo "\nRDFauthor"
	hg --config web.cacerts= --repository libraries/RDFauthor update -c

status:
	hg --repository . status
	hg --repository libraries/RDFauthor status
	cd libraries/Erfurt && git status

branch-check:
	hg --repository . branch
	hg --repository libraries/RDFauthor branch
	cd libraries/Erfurt && git branch


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
	hg clone https://rdfauthor.googlecode.com/hg/ libraries/RDFauthor

test:
	phpunit --verbose application/tests/TestSuite 

erfurt:
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	git clone git://github.com/AKSW/Erfurt.git libraries/Erfurt

test-erfurt:
	cd libraries/Erfurt/tests && phpunit Erfurt_TestSuite && cd ../../..


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

cs-install: cs-enable
	pear install PHP_CodeSniffer

cs-uninstall: cs-disable

cs-enable:
	./application/tests/CodeSniffer/add-hook.sh

cs-disable:
	./application/tests/CodeSniffer/remove-hook.sh

cs-check-commit:
	hg status -n | grep '\.php$\' | xargs phpcs --report=full --severity=7 -p -s --standard=application/tests/CodeSniffer/Standards/Ontowiki
cs-check-commit-intensive:
	hg status -n | grep '\.php$\' | xargs phpcs --report=full --severity=5 -p -s --standard=application/tests/CodeSniffer/Standards/Ontowiki

cs-check-all:
	phpcs --report=summary --extensions=php --severity=7 -s -p --standard=application/tests/CodeSniffer/Standards/Ontowiki *
cs-check-all-intensive:
	phpcs --report=summary --extensions=php --severity=5 -s -p --standard=application/tests/CodeSniffer/Standards/Ontowiki *

