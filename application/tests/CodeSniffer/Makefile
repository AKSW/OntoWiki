default: help

help:
	@echo "     cs-enable ............................. Enable CodeSniffer to check code before every commit"
	@echo "     cs-disable ............................ Disable CodeSniffer code checking"
	@echo "     cs-check-commit ....................... Run pre-commit code checking manually"
	@echo "     cs-check-commit-emacs ................. Same as cs-check-commit with emacs output)"
	@echo "     cs-check-commit-intensive ............. Run pre-commit code checking"
	@echo "                                             manually with stricter coding standard"
	@echo "     cs-check .............................. Run complete code checking"
	@echo "     cs-check-full ......................... Run complete code checking with detailed output"
	@echo "     cs-check-emacs ........................ Run complete code checking with with emacs output"
	@echo "     cs-check-blame ........................ Run complete code checking with blame list output"
	@echo "     cs-check-intensive .................... Run complete code checking with"
	@echo "                                             stricter coding standard"
	@echo "     cs-check-intensive-full ............... Run complete code checking with"
	@echo "                                             stricter coding standard and detailed output"
	@echo "     possible Parameter:"
	@echo "     > FPATH=<path> ................. Run code checking on specific relative path"
	@echo "     > SNIFFS=<sniff 1>,<sniff 2> ... Run code checking on specific sniffs"
	@echo "     > OPTIONS=<option> ............. Run code checking with specific CodeSniffer options"

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
	$(CSSPATH)cs-scripts.sh -c "-s --report=summary $(REQUESTSTR)"
cs-check-intensive:
	$(CSSPATH)cs-scripts.sh -s -c "-s --report=summary $(REQUESTSTR)"
cs-check-intensive-full:
	$(CSSPATH)cs-scripts.sh -s -c "-s --report=full $(REQUESTSTR)"
cs-check-full:
	$(CSSPATH)cs-scripts.sh -c "-s --report=full $(REQUESTSTR)"
cs-check-emacs:
	$(CSSPATH)cs-scripts.sh -c "--report=emacs $(REQUESTSTR)"
cs-check-blame:
	$(CSSPATH)cs-scripts.sh -s -c "--report=gitblame $(REQUESTSTR)"
