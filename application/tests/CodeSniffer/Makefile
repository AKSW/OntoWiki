default: help

help:
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

# coding standard

# #### config ####
# cs-script path
MSOURCE = $(CURDIR)/Makefile
MSOURCE2 = $(CURDIR)/$(shell readlink $(MSOURCE))
CSSPATH = $(shell dirname $(MSOURCE2))/

cs-default:
	chmod ugo+x "$(CSSPATH)cs-scripts.sh"
	
cs-enable: cs-default
	$(CSSPATH)cs-scripts.sh -f $(shell dirname $(shell readlink $(MSOURCE))) -e

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
	$(CSSPATH)cs-scripts.sh -c "--report=summary *"
cs-check-all-intensive:
	$(CSSPATH)cs-scripts.sh -s -c "--report=summary *"

cs-check-blame:
	$(CSSPATH)cs-scripts.sh -s -c "--report=gitblame -v *"

cs-check-blame-full:
	$(CSSPATH)cs-scripts.sh -s -c "--report=gitblame *"
