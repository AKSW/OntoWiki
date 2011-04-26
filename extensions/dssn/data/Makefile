install: install-webid-seebi install-activities

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
