options=--inputOptions="-i turtle"
OWCLIDATA=owcli -w aksw.org -m http://aksw.org ${options}
OWCLISCHEMA=owcli -w aksw.org -m http://aksw.org ${options}

create:
	${OWCLIDATA} -e model:create

install: install-pages install-schema

install-pages:
	${OWCLIDATA} -e model:add -i pages.n3

install-schema:
	${OWCLISCHEMA} -e model:add -i schema.n3
