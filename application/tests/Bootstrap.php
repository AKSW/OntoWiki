<?php
/**
 * OntoWiki test base file
 *
 * Sets the same include paths as OntoWiki uses and must be included
 * by all tests.
 *
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: test_base.php 2327 2008-05-26 15:47:55Z norman.heino $
 */
 
/*
 * Set error reporting to the level to which Erfurt code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/*
 * Default timezone in order to prevent warnings
 */ 
date_default_timezone_set('Europe/Berlin');

/*
 * Check for minimum supported PHPUnit version
 */
$phpUnitVersion = PHPUnit_Runner_Version::id();
if ('@package_version@' !== $phpUnitVersion && version_compare($phpUnitVersion, '3.5.0', '<')) {
    echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported in OntoWiki unit tests.' . PHP_EOL;
    exit(1);
}
unset($phpUnitVersion);

define('BOOTSTRAP_FILE', basename(__FILE__));
define('ONTOWIKI_ROOT', realpath(dirname(__FILE__) . '/../..') . '/');
define('APPLICATION_PATH', ONTOWIKI_ROOT . 'application/');
define('APPLICATION_ENV', 'unittesting');
define('ONTOWIKI_REWRITE', false);
define('CACHE_PATH', ONTOWIKI_ROOT . 'cache'.DIRECTORY_SEPARATOR);

// path to tests
if (!defined('_TESTROOT')) {
    define('_TESTROOT', rtrim(dirname(__FILE__), '/') . '/');
}

// path to OntoWiki
define('_OWROOT', ONTOWIKI_ROOT);

// add libraries to include path
$includePath  = get_include_path()                              . PATH_SEPARATOR;
$includePath .= _TESTROOT                                       . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'application/classes/'          . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'libraries/'                    . PATH_SEPARATOR;

$includePath .= ONTOWIKI_ROOT . 'libraries/Erfurt/library'    . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'libraries/Erfurt/tests/unit' . PATH_SEPARATOR; // for test base class
set_include_path($includePath);

// start dummy session before any PHPUnit output
require_once 'Zend/Session/Namespace.php';
$session = new Zend_Session_Namespace('OntoWiki_Test');

// Zend_Loader for class autoloading
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('OntoWiki_');
$loader->registerNamespace('Erfurt_');
//$loader->registerNamespace('PHPUnit_');

// Access Erfurt app for constant loading etc.
Erfurt_App::getInstance(false);

/** OntoWiki */
require_once 'OntoWiki.php';
