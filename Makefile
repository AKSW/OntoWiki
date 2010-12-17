default:
	@echo "please use:"
	@echo "     'make pull' ('hg pull' for all subrepos)"
	@echo "     'make update' ('hg pull -u' for all subrepos)"
	@echo "     'make branch-check' ('hg branch' for all subrepos)"
	@echo "     'make zend' (download and install Zend under libraries)"
	@echo "     'make test' (execute 'phpunit TestSuite')"
	@echo "     'make test-erfurt' (execute Erfurts TestSuite)"

pull:
	@hg --repository . pull
	@hg --repository libraries/Erfurt pull
	@hg --repository libraries/RDFauthor pull

update: pull
	@echo "\nOntoWiki"
	hg --repository . update
	@echo "\nErfurt"
	hg --repository libraries/Erfurt update
	@echo "\nRDFauthor"
	hg --repository libraries/RDFauthor update

force-update: pull
	@echo "I force the update of the subrepos ..."
	@echo "\nOntoWiki"
	hg --repository . update
	@echo "\nErfurt"
	hg --repository libraries/Erfurt update -c
	@echo "\nRDFauthor"
	hg --repository libraries/RDFauthor update -c

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
	curl -O http://framework.zend.com/releases/ZendFramework-1.11.1/ZendFramework-1.11.1-minimal.tar.gz || wget  http://framework.zend.com/releases/ZendFramework-1.11.1/ZendFramework-1.11.1-minimal.tar.gz
	tar xzf ZendFramework-1.11.1-minimal.tar.gz
	mv ZendFramework-1.11.1-minimal/library/Zend libraries
	rm -rf ZendFramework-1.11.1-minimal.tar.gz ZendFramework-1.11.1-minimal

libraries:
	rm -rf libraries/Erfurt
	@echo 'Cloning Erfurt into libraries/Erfurt ...'
	hg clone https://erfurt.ontowiki.googlecode.com/hg/ libraries/Erfurt
	rm -rf libraries/RDFauthor
	@echo 'Cloning RDFauthor into libraries/RDFauthor ...'
	hg clone https://rdfauthor.googlecode.com/hg/ libraries/RDFauthor

test:
	phpunit --verbose application/tests/TestSuite 

test-erfurt:
	cd libraries/Erfurt/tests && phpunit Erfurt_TestSuite && cd ../../..
