<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki bootstrap file.
 *
 * @category OntoWiki
 * @author Norman Heino <norman.heino@gmail.com>
 */

/* Profiling */
define('REQUEST_START', microtime(true));

/*
 * error handling for the very first includes etc.
 * http://stackoverflow.com/questions/1241728/
 */
function errorHandler ($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('errorHandler');

/**
 * Boostrap constants
 * @since 0.9.5
 */
define('BOOTSTRAP_FILE', basename(__FILE__));
define('ONTOWIKI_ROOT', rtrim(dirname(__FILE__), '/\\') . '/');
define('APPLICATION_PATH', ONTOWIKI_ROOT . 'application/');

/**
 * Old constants for < 0.9.5 backward compatibility
 * @deprecated 0.9.5
 */
define('_OWBOOT', BOOTSTRAP_FILE);
define('_OWROOT', ONTOWIKI_ROOT);
define('OW_SHOW_MAX', 5);


// PHP environment settings
ini_set('max_execution_time', 240);

if ((int) substr(ini_get('memory_limit'), 0, -1) < 256) {
    ini_set('memory_limit', '256M');
}

// add libraries to include path
$includePath = get_include_path() . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'libraries/' . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'libraries/Erfurt/' . PATH_SEPARATOR;
set_include_path($includePath);

// use default timezone from php.ini or let PHP guess it
date_default_timezone_set(@date_default_timezone_get());


// determine wheter rewrite engine works
// and redirect to a URL that doesn't need rewriting
// TODO: check for AllowOverride All
$rewriteEngineOn = false;

if (isset($_SERVER['ONTOWIKI_APACHE_MOD_REWRITE_ENABLED'])) {
    // used in .htaccess or in debian package config
    $rewriteEngineOn = true;
} else if (function_exists('__virt_internal_dsn')) {
    // compatible with Virtuoso VAD
    $rewriteEngineOn = true;
} else if (function_exists('apache_get_modules')) {
    // usually, we are not here
    if (in_array('mod_rewrite', apache_get_modules())) {
        // get .htaccess contents
        $htaccess = @file_get_contents(ONTOWIKI_ROOT . '.htaccess');

        // check if RewriteEngine is enabled
        $rewriteEngineOn = preg_match('/.*[^#][\t ]+RewriteEngine[\t ]+On/i', $htaccess);

        // explicitly request /index.php for non-rewritten requests
        if (!$rewriteEngineOn and ! strpos($_SERVER['REQUEST_URI'], BOOTSTRAP_FILE)) {
            header('Location: ' . rtrim($_SERVER['REQUEST_URI'], '/\\') . '/' . BOOTSTRAP_FILE, true, 302);
            return;
        }
    }
}

define('ONTOWIKI_REWRITE', $rewriteEngineOn);


/**
 * Ensure compatibility for PHP <= 5.3
 */
if (!function_exists('class_alias')) {
    function class_alias($original, $alias)
    {
        eval('abstract class ' . $alias . ' extends ' . $original . ' {}');
    }
}

/** check/include Zend_Application */
try {
    // use include, so we can catch it with the error handler
    include 'Zend/Application.php';
} catch (Exception $e) {
    echo 'Fatal Error: Could not load Zend library.<br />' . PHP_EOL
         . 'Maybe you need to install it with apt-get or with "make zend"?';
    return;
}

// create application
$application = new Zend_Application(
    'default',
    ONTOWIKI_ROOT . 'application/config/application.ini'
);

/** check/include OntoWiki */
try {
    // use include, so we can catch it with the error handler
    include 'OntoWiki.php';
} catch (Exception $e) {
    echo 'Fatal Error: Could not load the OntoWiki Application Framework classes.<br />' . PHP_EOL
         . 'Your installation directory seems to be screwed.';
    return;
}

/** check/include Erfurt_App */
try {
    // use include, so we can catch it with the error handler
    include 'Erfurt/App.php';
} catch (Exception $e) {
    echo 'Fatal Error: Could not load the Erfurt Framework classes.<br />' . PHP_EOL
    . 'Maybe you should install it with apt-get or with "make erfurt"?';
    return;
}

// restore old error handler
restore_error_handler();

// define alias for backward compatiblity
class_alias('OntoWiki', 'OntoWiki_Application');

// bootstrap
$application->bootstrap();

$event = new Erfurt_Event('onPostBootstrap');
$event->bootstrap = $application->getBootstrap();
$event->trigger();

$application->run();
