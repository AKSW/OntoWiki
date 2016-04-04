<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2015, {@link http://aksw.org AKSW}
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

/*
 * method to get evironment variables which are prefixed with "REDIRECT_"
 * in some configurations Apache prefixes the environment variables on each rewrite walkthrough
 * e.g. under centos
 */
function getEnvVar ($key)
{
    $prefix = "REDIRECT_";
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    foreach ($_SERVER as $k => $v) {
        if (substr($k, 0, strlen($prefix)) == $prefix) {
            if (substr($k, -(strlen($key))) == $key) {
                return $v;
            }
        }
    }
    return null;
}

/**
 * Bootstrap constants
 * @since 0.9.5
 */
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
} // fix for PHP < 5.3.0
define('BOOTSTRAP_FILE', basename(__FILE__));
define('ONTOWIKI_ROOT', rtrim(__DIR__, '/\\') . DIRECTORY_SEPARATOR);
define('APPLICATION_PATH', ONTOWIKI_ROOT . 'application'.DIRECTORY_SEPARATOR);
define('CACHE_PATH', ONTOWIKI_ROOT . 'cache'.DIRECTORY_SEPARATOR);

/**
 * Old constants for < 0.9.5 backward compatibility
 * @deprecated 0.9.5
 */
define('_OWBOOT', BOOTSTRAP_FILE);
define('_OWROOT', ONTOWIKI_ROOT);
define('OW_SHOW_MAX', 5);


// PHP environment settings
if ((int)ini_get('max_execution_time') < 240) {
    ini_set('max_execution_time', 240);
}

if ((int)substr(ini_get('memory_limit'), 0, -1) < 256) {
    ini_set('memory_limit', '256M');
}

// append local Erfurt include path
require_once('vendor/autoload.php');

// use default timezone from php.ini or let PHP guess it
date_default_timezone_set(@date_default_timezone_get());

// determine wheter rewrite engine works
// and redirect to a URL that doesn't need rewriting
// TODO: check for AllowOverride All
$rewriteEngineOn = false;

if (getEnvVar('ONTOWIKI_APACHE_MOD_REWRITE_ENABLED')) {
    // used in .htaccess or in debian package config
    // in some configurations Apache prefixes the env var with 'REDIRECT_'
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
        if (!$rewriteEngineOn && ! strpos($_SERVER['REQUEST_URI'], BOOTSTRAP_FILE)) {
            header('Location: ' . rtrim($_SERVER['REQUEST_URI'], '/\\') . '/' . BOOTSTRAP_FILE, true, 302);
            return;
        }
    }
}

define('ONTOWIKI_REWRITE', $rewriteEngineOn);

// create application
$application = new Zend_Application(
    'default',
    ONTOWIKI_ROOT . 'application/config/application.ini'
);

// restore old error handler
restore_error_handler();

// bootstrap
try {
    $application->bootstrap();
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error on bootstrapping application: ';
    echo $e->getMessage();
    return;
}

$event = new Erfurt_Event('onPostBootstrap');
$event->bootstrap = $application->getBootstrap();
$event->trigger();

$application->run();
