default:
	@echo "please use:"
	@echo "     'make pull' ('hg pull' for all subrepos)"
	@echo "     'make update' ('hg pull -u' for all subrepos)"
	@echo "     'make branch-check' ('hg branch' for all subrepos)"
	@echo "     'make zend' (download and install Zend under libraries)"

pull:
	hg --repository . pull
	hg --repository libraries/Erfurt pull
	hg --repository libraries/RDFauthor pull

update:
	hg --repository . pull -u
	hg --repository libraries/Erfurt pull -u
	hg --repository libraries/RDFauthor pull -u

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
	curl -O http://framework.zend.com/releases/ZendFramework-1.9.4/ZendFramework-1.9.4-minimal.tar.gz
	tar xzf ZendFramework-1.9.4-minimal.tar.gz
	mv ZendFramework-1.9.4-minimal/library/Zend libraries
	rm -rf ZendFramework-1.9.4-minimal.tar.gz ZendFramework-1.9.4-minimal

