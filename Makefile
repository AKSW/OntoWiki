default:
	@echo "please use:"
	@echo "     'make pull' ('hg pull' for all subrepos)"
	@echo "     'make update' ('hg pull -u' for all subrepos)"

pull:
	hg --repository . pull
	hg --repository libraries/Erfurt pull

update:
	hg --repository . pull -u
	hg --repository libraries/Erfurt pull -u

status:
	hg --repository . status
	hg --repository libraries/Erfurt status
