default: help

help:
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

# coding standard

# #### config ####
# cs-script path
MSOURCE = $(CURDIR)/Makefile
MSOURCE2 = $(CURDIR)/$(shell readlink $(MSOURCE))
CSSPATH = $(shell dirname $(MSOURCE2))/

# Parameter check
ifndef FPATH
	FPATH = "*"
endif
ifdef SNIFFS
	SNIFFSTR = "--sniffs="$(SNIFFS)
else
	SNIFFSTR =
endif

REQUESTSTR = $(OPTIONS) $(SNIFFSTR)  $(FPATH)

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

cs-check:
	$(CSSPATH)cs-scripts.sh -c "--report=summary $(REQUESTSTR)"
cs-check-intensive:
	$(CSSPATH)cs-scripts.sh -s -c "--report=summary $(REQUESTSTR)"
cs-check-full:
	$(CSSPATH)cs-scripts.sh -c "-v --report=full $(REQUESTSTR)"
cs-check-emacs:
	$(CSSPATH)cs-scripts.sh -c "--report=emacs $(REQUESTSTR)"
cs-check-blame:
	$(CSSPATH)cs-scripts.sh -s -c "--report=gitblame $(REQUESTSTR)"
