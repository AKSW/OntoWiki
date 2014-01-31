#!/usr/bin/env php
<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki bootstrap file.
 *
 * @category OntoWiki
 * @author Norman Heino <norman.heino@gmail.com>
 */

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

function initApp(){
    /* Profiling */
    define('REQUEST_START', microtime(true));

    set_error_handler('errorHandler');
       /**
     * Bootstrap constants
     * @since 0.9.5
     */
    if (!defined('__DIR__')) {
        define('__DIR__', dirname(__FILE__));
    } // fix for PHP < 5.3.0
    define('BOOTSTRAP_FILE', basename(__FILE__));
    define('ONTOWIKI_ROOT', rtrim(dirname(__DIR__), '/\\') . DIRECTORY_SEPARATOR);
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
    ini_set('max_execution_time', 240);

    if ((int)substr(ini_get('memory_limit'), 0, -1) < 256) {
        ini_set('memory_limit', '256M');
    }

    /*
     * include path preparation
     */
    // init with local path in order to prefer these over system paths
    $includePath = ONTOWIKI_ROOT . 'libraries/' . PATH_SEPARATOR;
    // append local Erfurt include path
    if (file_exists(ONTOWIKI_ROOT . 'libraries/Erfurt/Erfurt/App.php')) {
        $includePath .= ONTOWIKI_ROOT . 'libraries/Erfurt/' . PATH_SEPARATOR;
    } else if (file_exists(ONTOWIKI_ROOT . 'libraries/Erfurt/library/Erfurt/App.php')) {
        $includePath .= ONTOWIKI_ROOT . 'libraries/Erfurt/library' . PATH_SEPARATOR;
    }
    // append system include paths
    $includePath .= get_include_path() . PATH_SEPARATOR;
    // set the include path
    set_include_path($includePath);

    // use default timezone from php.ini or let PHP guess it
    date_default_timezone_set(@date_default_timezone_get());

    // determine wheter rewrite engine works
    // and redirect to a URL that doesn't need rewriting
    // TODO: check for AllowOverride All
    $rewriteEngineOn = false;
    define('ONTOWIKI_REWRITE', $rewriteEngineOn);

    /** check/include Zend_Application */
    try {
        // use include, so we can catch it with the error handler
        require_once 'Zend/Application.php';
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Fatal Error: Could not load Zend library.<br />' . PHP_EOL
             . 'Maybe you need to install it with apt-get or with "make zend"?' . PHP_EOL;
        exit;
    }

    // create application
    $application = new Zend_Application(
        'default',
        ONTOWIKI_ROOT . 'application/config/application.ini'
    );

    /** check/include OntoWiki */
    try {
        // use include, so we can catch it with the error handler
        require_once 'OntoWiki.php';
    } catch (Exception $e) {
        print('Fatal Error: Could not load the OntoWiki Application Framework classes.' . PHP_EOL);
        print('Your installation directory seems to be screwed.' . PHP_EOL);
        exit;
    }

    /* check/include Erfurt_App */
    try {
        // use include, so we can catch it with the error handler
        require_once 'Erfurt/App.php';
    } catch (Exception $e) {
        print('Fatal Error: Could not load the Erfurt Framework classes.' . PHP_EOL);
        print('Maybe you should install it with apt-get or with "make deploy"?' . PHP_EOL);
        exit;
    }

    // restore old error handler
    restore_error_handler();

    // bootstrap
    try {
        $application->bootstrap();
    } catch (Exception $e) {
        print('Error on bootstrapping application: ' . $e->getMessage() . PHP_EOL);
        exit;
    }
    return $application;
}

$application    = initApp();

$bootstrap      = $application->getBootstrap();
$ontoWiki       = OntoWiki::getInstance();
$extManager     = $ontoWiki->extensionManager;
$extensions     = $extManager->getExtensions();

print($ontoWiki->config->version->label . ' ' . $ontoWiki->config->version->number . PHP_EOL);

// create a worker registry
$workerRegistry = Erfurt_Worker_Registry::getInstance();

// trigger event to let extensions add their jobs
$event = new Erfurt_Event('onAnnounceWorker');
$event->bootstrap   = $bootstrap;
$event->registry    = $workerRegistry;
$event->trigger();
if (!count($workerRegistry->getJobs())) {
    print('No jobs registered - nothing to do or wait for.' . PHP_EOL );
    exit;
}

// register  jobs manually
// key name, class file, class name, config

// test job
$workerRegistry->registerJob(
    'test',
    'libraries/Erfurt/library/Erfurt/Worker/TestJob.php',
    'Erfurt_Worker_TestJob',
    array()
);

// cron job
$workerRegistry->registerJob(
    'cron',
    'application/classes/OntoWiki/Jobs/Cron.php',
    'OntoWiki_Jobs_Cron',
    array()
);


//  create a new worker backend with filled worker registry
$worker = new Erfurt_Worker_Backend($workerRegistry);
//  set worker and Gearman worker to listen mode and wait
$worker->listen();

