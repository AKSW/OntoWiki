<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
    echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported in OntoWiki unit tests.' .
        PHP_EOL;
    exit(1);
}
unset($phpUnitVersion);

define('BOOTSTRAP_FILE', basename(__FILE__));
define('ONTOWIKI_ROOT', realpath(dirname(__FILE__) . '/../..') . '/');
define('APPLICATION_PATH', ONTOWIKI_ROOT . 'application/');
define('APPLICATION_ENV', 'unittesting');
define('ONTOWIKI_REWRITE', false);
define('CACHE_PATH', ONTOWIKI_ROOT . 'cache' . DIRECTORY_SEPARATOR);

// path to tests
if (!defined('_TESTROOT')) {
    define('_TESTROOT', rtrim(dirname(__FILE__), '/') . '/');
}

// path to OntoWiki
define('_OWROOT', ONTOWIKI_ROOT);

require_once(ONTOWIKI_ROOT . '/vendor/autoload.php');

// start dummy session before any PHPUnit output
$session = new Zend_Session_Namespace('OntoWiki_Test');

// Access Erfurt app for constant loading etc.
Erfurt_App::getInstance(false);

