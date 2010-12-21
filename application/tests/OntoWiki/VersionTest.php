<?php
/**
 * OntoWiki
 *
 * LICENSE
 *
 * This file is part of the OntoWiki project.
 * Copyright (C) 2006-2010, AKSW
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the 
 * Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * A copy of the GNU General Public License is bundled with this package in
 * the file LICENSE.txt. It is also available through the world-wide-web at 
 * this URL: http://opensource.org/licenses/gpl-2.0.php
 *
 * @category   OntoWiki
 * @package    Version
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
 
/*
 * Helper file, that adjusts the include_path and initializes the test environment.
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';

// This constant will not be defined iff this file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'OntoWiki_VersionTest::main');
}

/**
 * This test class comtains tests for the OntoWiki index controller.
 * 
 * @category   OntoWiki
 * @package    Version
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class OntoWiki_VersionTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('OntoWiki_VersionTest'));
    }
    
    public function testCompareVersionEqual()
    {
        $this->assertEquals(0, OntoWiki_Version::compareVersion(OntoWiki_Version::VERSION));
    }
    
    public function testCompareVersionGreater()
    {
        // This version should be greater for a long time ;-)
        $this->assertEquals(1, OntoWiki_Version::compareVersion('1000.1000.1000'));
    }
    
    public function testCompareVersionLess()
    {
        $this->assertEquals(-1, OntoWiki_Version::compareVersion('0.1.0'));
    }    
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'OntoWiki_VersionTest::main') {
    OntoWiki_VersionTest::main();
}
