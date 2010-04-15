<?php

/**
 * OntoWiki test base file
 *
 * Sets the same include paths as OntoWik uses and must be included
 * by all tests.
 *
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: test_base.php 2327 2008-05-26 15:47:55Z norman.heino $
 */

?>
<?php

// path to tests
define('_TESTROOT', rtrim(dirname(__FILE__), '/') . '/');

// path to OntoWiki
define('_OWROOT', rtrim(realpath(_TESTROOT . '../src'), '/') . '/');

// add libraries to include path
$includePath  = get_include_path()               . PATH_SEPARATOR;
$includePath .= _TESTROOT                        . PATH_SEPARATOR;
$includePath .= _OWROOT . 'application/classes/' . PATH_SEPARATOR;
$includePath .= _OWROOT . 'libraries/'           . PATH_SEPARATOR;
$includePath .= _OWROOT . 'libraries/Erfurt/libraries/'           . PATH_SEPARATOR;
set_include_path($includePath);

// start dummy session before any PHPUnit output
require_once 'Zend/Session/Namespace.php';
$session = new Zend_Session_Namespace('OntoWiki_Test');

// Zend_Loader for class autoloading
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('OntoWiki_');
$loader->registerNamespace('Erfurt_');



?>
