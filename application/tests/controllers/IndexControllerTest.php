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
    
    public function testMessagesActionNoMessages()
    {
        $this->dispatch('/index/messages');
        
        $this->assertController('index');
        $this->assertAction('messages');
        $this->assertQueryContentContains('p.messagebox', 'No messages');
    }
    
    public function testMessagesActionSingleMessage()
    {
        $owApp = OntoWiki::getInstance();
        $owApp->appendMessage(new OntoWiki_Message('Test Message 123', OntoWiki_Message::INFO));
        
        $this->dispatch('/index/messages');
        
        $this->assertController('index');
        $this->assertAction('messages');
        $this->assertQueryContentContains('p.messagebox.info', 'Test Message 123');
    }
    
    public function testMessagesActionMultipleMessages()
    {
        $owApp = OntoWiki::getInstance();
        $owApp->appendMessage(new OntoWiki_Message('Test Message 123', OntoWiki_Message::INFO));
        $owApp->appendMessage(new OntoWiki_Message('Error Message 456', OntoWiki_Message::ERROR));
        
        $this->dispatch('/index/messages');
        $this->assertController('index');
        $this->assertAction('messages');
        $this->assertQueryContentContains('p.messagebox.info', 'Test Message 123');
        $this->assertQueryContentContains('p.messagebox.error', 'Error Message 456');
    }
    
    public function testNewsActionSuccess()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(new Zend_Http_Response(
            200, 
            array(), 
            '<?xml version="1.0" encoding="UTF-8"?>
             <!-- generator="wordpress/2.1" -->
             <rss version="2.0"
                xmlns:content="http://purl.org/rss/1.0/modules/content/"
                xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                xmlns:dc="http://purl.org/dc/elements/1.1/">
             <channel>
             <title>blog.aksw.org</title>
                <link>http://blog.aksw.org</link>
                <description>The shared AKSW blog about our projects and the Semantic Web.</description>
                 <pubDate>Tue, 14 Dec 2010 16:02:49 +0000</pubDate>
                <generator>http://wordpress.org/?v=2.1</generator>
                <language>en</language>
             </channel>
             </rss>'
        ));
        
        $this->dispatch('/index/news');
        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQueryContentContains('h1.title', 'News');
    }
    
    public function testNewsActionFail()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(new Zend_Http_Response(404, array(), ''));
        
        $this->dispatch('/index/news');
        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQueryContentContains('h1.title', 'News');
        $this->assertQuery('p.messagebox.warning');
    }
    
    public function testNewsshortActionSuccess()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(new Zend_Http_Response(
            200, 
            array(), 
            '<?xml version="1.0" encoding="UTF-8"?>
             <!-- generator="wordpress/2.1" -->
             <rss version="2.0"
                xmlns:content="http://purl.org/rss/1.0/modules/content/"
                xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                xmlns:dc="http://purl.org/dc/elements/1.1/">
             <channel>
             <title>blog.aksw.org</title>
                <link>http://blog.aksw.org</link>
                <description>The shared AKSW blog about our projects and the Semantic Web.</description>
                 <pubDate>Tue, 14 Dec 2010 16:02:49 +0000</pubDate>
                <generator>http://wordpress.org/?v=2.1</generator>
                <language>en</language>
             </channel>
             </rss>'
        ));
        
        $this->dispatch('/index/newsshort');
        $this->assertController('index');
        $this->assertAction('newsshort');
        $this->assertQueryContentContains('h1.title', 'News');
    }
    
    public function testNewsshortActionFail()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(new Zend_Http_Response(404, array(), ''));
        
        $this->dispatch('/index/newsshort');
        $this->assertController('index');
        $this->assertAction('newsshort');
        $this->assertQueryContentContains('h1.title', 'News');
        $this->assertQuery('p.messagebox.warning');
    }   
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'IndexControllerTest::main') {
    IndexControllerTest::main();
}
