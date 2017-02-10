- `install-services.sh` to handle the install of additional services

## SPARQL services

This file and scripts are taken form the SemanticMediaWiki repo and only selected files where copied for running tests with virtuoso.
It is taken as of commit 721a63d3a73400300f73e0a088196a6ed0fe5afd from https://github.com/SemanticMediaWiki/SemanticMediaWiki.
The unsupported services are marked with *Enabled* "No".

<table>
	<tr>
		<th>Enabled</th>
		<th>Service</th>
		<th>Connector</th>
		<th>QueryEndPoint</th>
		<th>UpdateEndPoint</th>
		<th>DataEndpoint</th>
		<th>DefaultGraph</th>
		<th>Comments</th>
	</tr>
	<tr>
		<th>No</th>
		<th>Fuseki (mem)<sup>1</sup></th>
		<td>Fuseki</td>
		<td>http://localhost:3030/db/query</td>
		<td>http://localhost:3030/db/update</td>
		<td>''</td>
		<td>''</td>
		<td>fuseki-server --update --port=3030 --mem /db</td>
	</tr>
	<tr>
		<th>No</th>
		<th>Fuseki (memTDB)</th>
		<td>Fuseki</td>
		<td>http://localhost:3030/db/query</td>
		<td>http://localhost:3030/db/update</td>
		<td>''</td>
		<td>http://example.org/myFusekiGraph</td>
		<td>fuseki-server --update --port=3030 --memTDB --set tdb:unionDefaultGraph=true /db</td>
	</tr>
	<tr>
		<th>Yes</th>
		<th>Virtuoso opensource</th>
		<td>Virtuoso</td>
		<td>http://localhost:8890/sparql</td>
		<td>http://localhost:8890/sparql</td>
		<td>''</td>
		<td>http://example.org/myVirtuosoGraph</td>
		<td>sudo apt-get install virtuoso-opensource</td>
	</tr>
	<tr>
		<th>No</th>
		<th>4store<sup>2</sup></th>
		<td>4store</td>
		<td>http://localhost:8088/sparql/</td>
		<td>http://localhost:8088/update/</td>
		<td>''</td>
		<td>http://example.org/myFourGraph</td>
		<td>apt-get install 4store</td>
	</tr>
	<tr>
		<th>No</th>
		<th>Sesame</th>
		<td>Custom</td>
		<td>http://localhost:8080/openrdf-sesame/repositories/test-smw</td>
		<td>http://localhost:8080/openrdf-sesame/repositories/test-smw/statements</td>
		<td>''</td>
		<td>`test-smw` is specifed as native in-memory store</td>
		<td></td>
	</tr>

</table>

<sup>1</sup> When running integration tests with [Jena Fuseki][fuseki] it is suggested that the `in-memory` option is used to avoid potential loss of production data during test execution.

<sup>2</sup> Currently, Travis-CI doesn't support `4Store` (1.1.4-2) as service but the following configuration has been sucessfully tested with the available test suite. ([issue #110](https://github.com/garlik/4store/issues/110) )

[fuseki]: https://jena.apache.org/
[virtuoso]: https://github.com/openlink/virtuoso-opensource
[4store]: https://github.com/garlik/4store
