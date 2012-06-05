#!/usr/bin/env php5
<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki mod-auth-external Authenticator
 *
 * This is an authenticator script for usage in a mod-auth-external pipe
 * configuration.
 *
 * @copyright  Copyright (c) 2009-2010 {@link http://aksw.org AKSW}
 * @license    http://www.gnu.org/licenses/gpl.txt  GNU GENERAL PUBLIC LICENSE v2
 * @link       http://code.google.com/p/mod-auth-external/
 */

// you can use this script as a copy outside the ontowiki tree but you need
// provide a valid ONTOWIKI_ROOT for this
//define('ONTOWIKI_ROOT', '/path/to/ontowiki/');
define('ONTOWIKI_ROOT', rtrim(dirname(__FILE__), '/\\') . '/../../../');
define('APPLICATION_PATH', ONTOWIKI_ROOT . 'application/');

// add libraries to include path
$includePath = get_include_path() . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'libraries/' . PATH_SEPARATOR;
$includePath .= ONTOWIKI_ROOT . 'libraries/Erfurt/' . PATH_SEPARATOR;
set_include_path($includePath);

// init the autoloader (so we do not need require_once anymore)
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Erfurt_');

// session is needed for authentication
$session = new Zend_Session_Namespace('OntoWiki_mod-auth-external_Authenticator');

// Parse the OntoWiki configuration
$config = new Zend_Config_Ini(ONTOWIKI_ROOT . 'config.ini', 'private');

// start the Erfurt engine (with a specific config)
$app = Erfurt_App::getInstance(false);
$app->start($config);

// grab the user/pass from php://stdin
$stdin = file_get_contents ( 'php://stdin');

// split the input by EOL
$userpass = explode( PHP_EOL, $stdin);

// set and check username
if ( (isset($userpass[0])) && ($userpass[0] != '') ) {
    $user = $userpass[0];
} else {
    echo "No user given" . PHP_EOL;
    exit (1);    
}

// set password
if ( isset($userpass[1]) ) {
    $pass = $userpass[1];
} else {
    $pass = '';
}

// Try to authenticate the user
// http://files.zend.com/help/Zend-Framework/zend.auth.html#zend.auth.introduction.results
$authResult = $app->authenticate($user, $pass);

// return 0 or 1 (message output on error)
if ($authResult->getCode() == Zend_Auth_Result::SUCCESS) {
    exit (0);
} else {
    foreach ($authResult->getMessages() as $message) {
        echo "(User: $user) $message" . PHP_EOL;
    }
    exit (1);
}
