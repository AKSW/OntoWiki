<?php

/**
 * OntoWiki bootstrap file.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: index.php 4253 2009-10-08 07:54:11Z pfrischmuth $
 * @category  ontowiki
 * @package   config
 * @author    Norman Heino <norman.heino@gmail.com>
 */

$start = microtime(true);

// constants for the boostrap file
define('_OWBOOT', basename(__FILE__));
define('_OWROOT', rtrim(dirname(__FILE__), '/') . '/');


// PHP environment settings
ini_set('max_execution_time', 240);
ini_set('memory_limit', '256M');


// let PHP guess the timezone
date_default_timezone_set(@date_default_timezone_get());


// determine wheter rewrite engine works
// and redirect to a URL that doesn't need rewriting
// TODO: check for AllowOverride All
// Use of apache functions is not compatible with Virtuoso VAD
$modRewriteAvailable = false;
$rewriteEngineOn     = false;
if(function_exists('apache_get_modules')){
	$loadedModules       = apache_get_modules();
	$modRewriteAvailable = in_array('mod_rewrite', $loadedModules);
}

if ($modRewriteAvailable) {
    $rewriteEngineOn = preg_match('/.*[^#][\t ]+RewriteEngine[\t ]+On/i', @file_get_contents(_OWROOT . '.htaccess'));
}

if (!$rewriteEngineOn and !strpos($_SERVER['REQUEST_URI'], _OWBOOT)) {
    header('Location: ' . rtrim($_SERVER['REQUEST_URI'], '/') . '/' . _OWBOOT, true, 302);
    exit;
}


// add libraries to include path
$includePath  = get_include_path()                     . PATH_SEPARATOR;
$includePath .= _OWROOT . 'application/classes/'       . PATH_SEPARATOR;
$includePath .= _OWROOT . 'libraries/Erfurt/libraries' . PATH_SEPARATOR;
$includePath .= _OWROOT . 'libraries/'                 . PATH_SEPARATOR;
set_include_path($includePath);

// set path variables
$rewriteBase = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], _OWBOOT));
$protocoll   = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') ? 'https' : 'http';
//$protocoll   = substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'));
$port        = $_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : '';
//$port = '';
$serverBase  = strtolower($protocoll) . '://' . $_SERVER['SERVER_NAME'] . $port; // HTTP_HOST also contains the port, but SERVER_NAME is only the name
$urlBase     = $serverBase . $rewriteBase;


// start application
require_once 'OntoWiki/Application.php';
$owApp = OntoWiki_Application::start($urlBase, $rewriteEngineOn);
$owApp->start = $start;


// set up front controller
require_once 'Zend/Controller/Front.php';
$frontController = Zend_Controller_Front::getInstance();

require_once 'OntoWiki/Dispatcher.php';
$dispatcher = new OntoWiki_Dispatcher();

require_once 'OntoWiki/Request.php';
$request = new OntoWiki_Request();

$frontController->setDispatcher($dispatcher)
                ->setRequest($request)
                ->setControllerDirectory(_OWROOT . 'application/controllers/')
                ->returnResponse(true);

// set up routes from config
$router = $frontController->getRouter();
$router->addConfig($owApp->config->routes);


// register plug-ins
require_once 'OntoWiki/Controller/Plugin/HttpAuth.php';
$frontController->registerPlugin(new OntoWiki_Controller_Plugin_HttpAuth(), 1); // Needs to be done first!
require_once 'OntoWiki/Controller/Plugin/SetupHelper.php';
$frontController->registerPlugin(new OntoWiki_Controller_Plugin_SetupHelper(), 2);

// throw exceptions in debug mode
if (defined('_OWDEBUG')) {
    $frontController->throwExceptions(true);
} else {
    $frontController->throwExceptions(false);
}

// start dispatching
$response = $frontController->dispatch();
$response->sendResponse();
$owApp->logger->info('Response sent after: ' . ((microtime(true) - $start) * 1000) . ' ms' . PHP_EOL);

$end = microtime(true);


