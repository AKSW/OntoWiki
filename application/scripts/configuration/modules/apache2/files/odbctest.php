<?php
$conn   = odbc_connect('VOS', 'dba', 'dba');
echo odbc_errormsg();
$query  = 'SELECT DISTINCT ?g WHERE {GRAPH ?g {?s ?p ?o.}}';
$result = odbc_exec($conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');
?>
<ul>
<?php while (odbc_fetch_row($result)): ?>
    <li><?php echo odbc_result($result, 1) ?></li>
<?php endwhile; ?>
</ul>
