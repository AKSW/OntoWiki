<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

define('SCRIPT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
}
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../libraries',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
    'virtload'  => 'Import files in data directory into Virtuoso',
    'virtclear' => 'Clear a specified graph',
    'help|h'   => 'Help -- usage message',
));
try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();
    return false;
}

// If help requested, report usage message
if ($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();
    return true;
} else if ($getopt->getOption('virtload')) {
    return _importData();
} else if ($getopt->getOption('virtclear')) {
    return _clearGraph();
} else {
    echo $getopt->getUsageMessage();
    return true;
}


return true;

/* Functions */

function _clearGraph()
{
    $graph = _chooseGraph();
    if (!$graph) {
        return false;
    }
    $command = "isql VERBOSE=OFF \"EXEC=sparql DELETE FROM <$graph> {?s ?p ?o} WHERE {?s ?p ?o}\" 2>&1 1> /dev/null";
    $output = shell_exec($command);
    if (null !== $output) {
        return false;
    }
    
    echo "DONE!" . PHP_EOL;
    return true;
}

function _importData()
{
    // Check for files
    $importFiles = array();
    $files = scandir(SCRIPT_DIR . 'data');
    foreach ($files as $file) {
        if ($file[0] === '.') {
            continue;
        }
        $importFiles[$file] = SCRIPT_DIR . 'data' . DIRECTORY_SEPARATOR . $file;
    }
    
    $failed = false;
    $failReason = '';
    foreach ($importFiles as $file=>$fullPath) {
        $ending = substr($file, -4);
        switch ($ending) {
            case '.ttl':
                break;
            case '.owl':
            case '.rdf':
                $newPath = SCRIPT_DIR . 'tmp' . DIRECTORY_SEPARATOR . 'sourceNtriples.ttl';
                $cmd = "rapper -gqo ntriples '$fullPath' 2>&1 1> /dev/null > '$newPath'";
                $output = shell_exec($cmd);
                if (null !== $output) {
                    echo 'Error while converting source file to TTL.';
                    return;
                }
                $fullPath = $newPath;
                break;
            default: 
                continue;
        }
        
        
        
        echo "Preparing import of file $file now." . PHP_EOL;
        $graph = _chooseGraph();
        if (!$graph) {
            $failed = true;
            $failReason = 'Invalid Graph!';
            break;
        }
        echo "Will import data into graph <$graph>." . PHP_EOL;
        
        $splitFiles = _splitFiles($fullPath);
        $count = count($splitFiles);
        foreach ($splitFiles as $i=>$splitFile) {
            $progress = round(($i/$count)*100.0);
            echo "\rImporting $file now: $progress%";
            if (!$failed) {
                $result = _importFile($splitFile, $graph);
            }
            unlink($splitFile);
            if (!$result) {
                $failed = true;
                $failReason = 'Import failed';
            }
        }
        
        // Move file to done dir!
        if (!$failed) {
            copy($fullPath, SCRIPT_DIR.'done'.DIRECTORY_SEPARATOR.$file);
            unlink($fullPath);
        }
        
        echo PHP_EOL;
    }
    
    if ($failed) {
        echo $failReason . PHP_EOL;
    } else {
        echo 'DONE!' . PHP_EOL;
    }
    return true;
}

function _splitFiles($fullPath)
{
    $splitFiles = array();
    
    $fHandle = fopen($fullPath, 'r');
    $fileCount = 0;
    $i = 0;
    $currentContent = '';
    if ($fHandle) {
        while ($line = fgets($fHandle)) {
            $currentContent .= $line;
            
            $i++;
            if ($i === 10000) {
                $tmp = SCRIPT_DIR . 'tmp' . DIRECTORY_SEPARATOR . $fileCount++;
                file_put_contents($tmp, $currentContent);
                $splitFiles[] = $tmp;
                $i = 0;
                $currentContent = '';
            }
        }
        if ($currentContent !== '') {
            $tmp = SCRIPT_DIR . 'tmp' . DIRECTORY_SEPARATOR . $fileCount++;
            file_put_contents($tmp, $currentContent);
            $splitFiles[] = $tmp;
        }
    }
    fclose($fHandle);
    
    return $splitFiles;
}

function _chooseGraph()
{
    $command = 'isql VERBOSE=OFF "EXEC=SELECT ID_TO_IRI(REC_GRAPH_IID) AS GRAPH FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH"';
    
    $output = shell_exec($command);
    $outputLines = explode("\n", $output);
    
    $startReached = false;
    $graphs = array();
    foreach ($outputLines as $line) {
        $trimmedLine = trim($line);
        if ($trimmedLine === '') {
            continue;
        }
        if (strpos($trimmedLine, '______________________________') !== false) {
            $startReached = true;
            continue;
        }
        if ($startReached) {
            $graphs[] = $line;
        }
    }
    
    echo "Choose a graph:\n";
    foreach ($graphs as $i=>$g) {
        echo "    ($i) $g" . PHP_EOL;
    }
    echo "Just type in the number: ";
    $input = intval(trim(fgets(STDIN)));
    
    if (isset($graphs[$input])) {
        return $graphs[$input];
    }
    
    return false;
}

function _importFile($file, $graph)
{
    $command = "isql 1111 dba dba \"EXEC=TTLP(file_to_string_output('$file'), '', '$graph', 255)\" 2>&1 1> /dev/null";
    $output = shell_exec($command);
    if (null !== $output) {
        var_dump($output);return;
        return false;
    }
    
    return true;
}
