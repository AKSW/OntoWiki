<?php

/**
 * OntoWiki Selenium Test Runner
 *
 * Reads the config.ini in the current directory, and runs all tests activated tests
 * in this file
 *
 * @author     Julian Jöris <julianjoeris@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

?>
<?php

// path to tests
define('_TESTROOT', rtrim(dirname(__FILE__), '/') . '/');

// read config data
$configuration = parse_ini_file  ( "config.ini" ,TRUE);

// get DISPLAY

$DISPLAY = `ls /tmp/.X11-unix/`;
$DISPLAY = ":".substr(trim($DISPLAY),1).".0";

// if  no absolute Path is given
$std_prefix = _TESTROOT."TESTSUITES/";

// command template
$command = "export DISPLAY=%s\njava -jar "._TESTROOT."selenium-server.jar -htmlSuite \"%s\" \"%s\" \"%s%s\" \"%s\"";
$winCommand = "export DISPLAY=%s\nrdesktop -u %s -p %s -s \"%s %s %s %s %s %s\" %s";


$resultNumber = 0;

foreach ($configuration as $group => $parameters) {
	if(substr($group,0,17)== "TestConfiguration") {
		foreach ($configuration[$parameters["TestGroup"]] as $currentTest) {
			unlink($parameters["TestResult"]);
			// Check, if absolute path to special test, or internal package Test
			if(substr($currentTest,0,1) == "/") $prefix = ""; else $prefix = $std_prefix;
			// test, if testing on a windows remote machine
                        if(!empty($parameters["WindowsCMD"])) {
				$currentTest =  str_replace($parameters["WindowsSuit"],"",$currentTest);
				$tok = explode("/",$parameters["WindowsSuit"]);
				if(empty($prefix))
					$currentTest = array_pop($tok).$currentTest;
				else
					 $currentTest = array_pop($tok)."\\".$currentTest;
				$cmd = sprintf($winCommand,$DISPLAY,$parameters["WindowsUser"],
								    $parameters["WindowsPass"],
							   	    $parameters["WindowsCMD"],
							   	    $parameters["WindowsSuit"],
							   	    $parameters["Browser"],
							   	    $parameters["OntoWikiURL"],
								    $currentTest,
							   	    $parameters["TestResult"].$resultNumber,
							   	    $parameters["WindowsServ"]);
				print $cmd;
				passthru($cmd);	
			} else {
				$cmd = sprintf($command,$DISPLAY,$parameters["Browser"],
				   				 $parameters["OntoWikiURL"],
								 $prefix,$currentTest,
								 $parameters["TestResult"].$resultNumber);
				passthru($cmd);
			}
			$resultNumber++;
		}
	MergeAndaddMetadata($parameters["svnRelease"],$parameters["TestResult"],$resultNumber);
		
	}
		
}

function MergeAndaddMetadata ($svnURL, $resultFile,$resultNumber) {
	$string = "<p><b>Zeitpunkt des Testes ".date("d.m.Y H:i")."</b><p/>\n";
	$string.= "<pre>";
	$string.= `svn info $svnURL`;
	$string.= "</pre>";
	$file = file($resultFile."0");
	$fp = fopen($resultFile,"w");
	foreach ($file as $row) {
		fputs($fp,$row);
		if(substr($row,0,4) == "<h1>") {
			fputs($fp,$string);
		}
	}
	fclose($fp);
	unlink($resultFile."0");
	for($i=1;$i<$resultNumber;$i++) {
		passthru("cat $resultFile$i >> $resultFile");
		print "cat $resultFile.$i >> $resultFile";
		unlink($resultFile.$i);
	}	
}

?>
