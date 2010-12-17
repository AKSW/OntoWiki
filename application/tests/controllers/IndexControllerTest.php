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
 * @package    controllers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @version    $Id: $
 */
 
/*
 * Helper file, that adjusts the include_path and initializes the test environment.
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';

// This constant will not be defined iff this file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'IndexControllerTest::main');
}

/**
 * This test class comtains tests for the OntoWiki index controller.
 * 
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('IndexControllerTest'));
    }
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    public function testNoControllerAndActionDefaultToNewsAction()
    {
        $this->dispatch('/');
        
        $this->assertController('index');
        $this->assertAction('news');
    }
    
    public function testInvalidActionNoDefaultActionDefaultsToNewsAction()
    {
        $this->dispatch('/index/actionXYZNotExisting');
        
        $this->assertController('index');
        $this->assertAction('news');
    }
    
    public function testInvalidActionDefaultsToConfiguredDefaultAction()
    {
        $config = OntoWiki::getInstance()->config;
        $config->index->default->controller = 'index';
        $config->index->default->action = 'empty';
        
        $this->dispatch('/index/actionXYZNotExisting');
        
        $this->assertController('index');
        $this->assertAction('empty');
    }
    
    public function testInvalidActionWithMessagesDefaultsToMessagesAction()
    {
        $owApp = OntoWiki::getInstance();
        $owApp->appendMessage(new OntoWiki_Message('Test Message'));
        
        $this->dispatch('/index/actionXYZNotExisting');
        
        $this->assertController('index');
        $this->assertAction('messages');
    }
    
    public function testEmptyAction()
    {
        $this->dispatch('/index/empty');
        
        $this->assertController('index');
        $this->assertAction('empty');
        $this->assertNotQuery('div.section-mainwindows');
        $this->assertQuery('div.section-sidewindows');
    }
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'IndexControllerTest::main') {
    IndexControllerTest::main();
}
