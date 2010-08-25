default:
	@echo "If you wanna install these models via owcli, type 'make install'"

convert:
	cwm --n3 lod2-schema.n3 --rdf >lod2-schema.rdf
	cwm --n3 lod2-config.n3 --rdf >lod2-config.rdf
	cat lod2-data.n3 work/*.n3 partner/*.n3 >lod2-data-all.n3
	cwm --n3 lod2-data-all.n3 --rdf >lod2-data.rdf

install: convert install-externals install-config install-schema install-data clean

clean:
	rm lod2*.rdf

install-externals: convert
#	owcli -m "http://www.w3.org/1999/02/22-rdf-syntax-ns#" -e model:drop model:create model:add -i rdf.rdf
#	owcli -m "http://www.w3.org/2000/01/rdf-schema#" -e model:drop model:create model:add -i rdfs.rdf
#	owcli -m "http://www.w3.org/2002/07/owl#" -e model:drop model:create model:add -i owl.rdf
	owcli -m "http://www.w3.org/2004/02/skos/core#" -e model:drop model:create model:add -i skos.rdf
	owcli -m "http://xmlns.com/foaf/0.1/" -e model:drop model:create model:add -i foaf.rdf
	owcli -m "http://rdfs.org/sioc/ns#" -e model:drop model:create model:add -i sioc.rdf
	owcli -m "http://usefulinc.com/ns/doap#" -e model:drop model:create model:add -i doap.rdf

install-schema: convert
	owcli -m "http://lod2.eu/schema/" -e model:drop model:create model:add -i lod2-schema.rdf
	owcli -m "http://ns.ontowiki.net/SysOnt/" -e model:add -i lod2-config.rdf

install-config: convert
	owcli -m "http://ns.ontowiki.net/SysOnt/" -e model:add -i lod2-config.rdf

install-data: convert
	owcli -m "http://lod2.eu/" -e model:drop model:create model:add -i lod2-data.rdf

uninstall:
#	owcli -m "http://www.w3.org/1999/02/22-rdf-syntax-ns#" -e model:drop
#	owcli -m "http://www.w3.org/2000/01/rdf-schema#" -e model:drop
#	owcli -m "http://www.w3.org/2002/07/owl#" -e model:drop
	owcli -m "http://www.w3.org/2004/02/skos/core#" -e model:drop
	owcli -m "http://xmlns.com/foaf/0.1/" -e model:drop
	owcli -m "http://rdfs.org/sioc/ns#" -e model:drop
	owcli -m "http://usefulinc.com/ns/doap#" -e model:drop
	owcli -m "http://lod2.eu/schema/" -e model:drop
	owcli -m "http://lod2.eu/" -e model:drop

