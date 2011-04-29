install: install-webid-seebi install-aair-mini

install-activities:
	@echo "Install activities to the dssn wiki default model!"
	owcli -w dssn -e model:add -i activity-examples.n3 --inputOptions="-i guess"

clean:
	@echo "clean dssn wiki default model!"
	owcli -w dssn -e model:drop

install-webid-seebi:
	@echo "Install seebis webid to the default model in dssn wiki"
	owcli -w dssn -e model:create
	rdf get http://sebastian.tramp.name | owcli -w dssn -e model:add -i -

install-aair-mini:
	@echo "Install mini-aair to the dssn wiki default model!"
	owcli -w dssn -e model:add -i aair-mini.n3 --inputOptions="-i guess"
