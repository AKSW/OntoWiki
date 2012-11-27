#! /usr/bin/env php
<?php
// Check for odbc extension
if (!extension_loaded('odbc')) {
    echo 'ODBC exenstion needs to be available! Try to install e.g. via ' .
         '"sudo apt-get install php5-odbc"' . PHP_EOL;
    exit;
}

// Check for config.ini
$confiDirPath =  dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;

$dsn      = null;
$username = null;
$password = null;

$customConfigPath  = $confiDirPath . 'config.ini';
$defaultConfigPath = $confiDirPath . 'config.ini.dist';
$config = null;
if (file_exists($customConfigPath)) {
    $config = parse_ini_file($customConfigPath);
} else if (file_exists($defaultConfigPath)) {
    // If not found try config.ini.dist (default settings)
    $config = parse_ini_file($defaultConfigPath);
}
if (is_array($config)) {
    if (isset($config['store.virtuoso.dsn'])) {
        $dsn = $config['store.virtuoso.dsn'];
    }
    if (isset($config['store.virtuoso.username'])) {
        $username = $config['store.virtuoso.username'];
    }
    if (isset($config['store.virtuoso.password'])) {
        $password = $config['store.virtuoso.password'];
    }
}

if (null === $dsn) {
    // ask
    echo 'DSN: ';
    $dsn = trim(fgets(STDIN));
}
if (null === $username) {
    // ask
    echo 'Username: ';
    $username = trim(fgets(STDIN));
}
if (null === $password) {
    // ask
    echo 'Password: ';
    $password = trim(fgets(STDIN));
}

$conn = @odbc_connect($dsn, $username, $password);
if (!$conn) {
    echo 'Connection failed - Something is wrong with your configuration.' . PHP_EOL;
    echo 'Used connection parameters:' . PHP_EOL;
    echo '    - DSN:      ' . $dsn . PHP_EOL;
    echo '    - Username: ' . $username . PHP_EOL;
    echo '    - Password: ' . $password . PHP_EOL . PHP_EOL;
    echo 'Error message: ' . odbc_errormsg() . PHP_EOL;
    exit;
}

$query  = 'SELECT DISTINCT ?g WHERE {GRAPH ?g { ?s ?p ?o . }}';
$result = @odbc_exec($conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');
if (!$result) {
    echo 'Query failed - Something is wrong with your configuration.' . PHP_EOL;
    echo 'Used connection parameters:' . PHP_EOL;
    echo '    - DSN:      ' . $dsn . PHP_EOL;
    echo '    - Username: ' . $username . PHP_EOL;
    echo '    - Password: ' . $password . PHP_EOL . PHP_EOL;
    echo 'Error message: ' . odbc_errormsg() . PHP_EOL;
    @odbc_close($conn);
    exit;
}

echo 'Your connection to Virtuoso seems to work fine:' . PHP_EOL;

while (@odbc_fetch_row($result)) {
     echo '    - ' . @odbc_result($result, 1) . PHP_EOL;
}

if ($conn) {
    @odbc_close($conn);
}

