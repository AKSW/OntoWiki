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

define('datagathering_BASE', dirname (__FILE__) . '/../');

$includePath  = get_include_path()                      . PATH_SEPARATOR;
$includePath .= datagathering_BASE                      . PATH_SEPARATOR;
set_include_path($includePath);

require_once datagathering_BASE .'../../application/tests/TestHelper.php';
