default:
	@echo "please use:"
	@echo "     'make pull' ('hg pull' for all repos)"
	@echo "     'make update' ('hg pull' and 'hg update' for all repos)"
	@echo "     'make force-update' ('hg pull' and 'hg update -c' for all repos)"
	@echo "     'make status' ('hg status' for all repos)"
	@echo "     'make branch-check' ('hg branch' for all repos)"
	@echo "     'make libraries' ('hg clone' all subrepos - in case of an old mercurial)"
	@echo "     'make zend' (download and install Zend under libraries)"
	@echo "     'make directories' (create cache/log dir and chmod environment)"
	@echo "     'make install' (-> make directories, zend and libraries)"
	@echo "     'make clean' (deletes all log and cache files)"

pull:
	@hg --repository . pull
	@hg --config web.cacerts= --repository libraries/Erfurt pull
	@hg --config web.cacerts= --repository libraries/RDFauthor pull

update: pull
	@echo "\nOntoWiki"
	hg --repository . update
	@echo "\nErfurt"
	hg --config web.cacerts= --repository libraries/Erfurt update
	@echo "\nRDFauthor"
	hg --config web.cacerts= --repository libraries/RDFauthor update

force-update: pull
	@echo "I force the update of the subrepos ..."
	@echo "\nOntoWiki"
	hg --repository . update -c
	@echo "\nErfurt"
	hg --config web.cacerts= --repository libraries/Erfurt update -c
	@echo "\nRDFauthor"
	hg --config web.cacerts= --repository libraries/RDFauthor update -c

status:
	hg --repository . status
	hg --repository libraries/Erfurt status
	hg --repository libraries/RDFauthor status

branch-check:
	hg --repository . branch
	hg --repository libraries/Erfurt branch
	hg --repository libraries/RDFauthor branch

zend:
	rm -rf libraries/Zend
	curl -O http://framework.zend.com/releases/ZendFramework-1.10.8/ZendFramework-1.10.8-minimal.tar.gz || wget http://framework.zend.com/releases/ZendFramework-1.10.8/ZendFramework-1.10.8-minimal.tar.gz
	tar xzf ZendFramework-1.10.8-minimal.tar.gz
	mv ZendFramework-1.10.8-minimal/library/Zend libraries
	rm -rf ZendFramework-1.10.8-minimal.tar.gz ZendFramework-1.10.8-minimal

libraries:
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	hg clone https://erfurt.ontowiki.googlecode.com/hg/ libraries/Erfurt
	rm -rf libraries/RDFauthor
	@echo 'Cloning RDFauthor into libraries/RDFauthor ...'
	hg clone https://rdfauthor.googlecode.com/hg/ libraries/RDFauthor

directories:
	mkdir -p logs cache
	chmod 777 logs cache extensions

install: directories zend libraries

debianize:
	rm extensions/markdown/parser/License.text
	rm extensions/markdown/parser/PHP_Markdown_Readme.txt
	rm extensions/markdown/parser/markdown.php
	rm extensions/queries/resources/codemirror/LICENSE
	rm extensions/themes/silverblue/scripts/libraries/jquery.js
	rm libraries/RDFauthor/libraries/jquery.js
	rm Makefile
	@echo "now do: cp -R application/scripts/debian debian"

clean:
	rm -rf cache/* logs/*
