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
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
 
require_once dirname (__FILE__) .'/../TestHelper.php';

/**
 * This test class comtains tests for the OntoWiki index controller.
 * 
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        
        $this->getFrontController()->setParam('bootstrap', $this->bootstrap->getBootstrap());
        
        parent::setUp();
    }
    
    public function tearDown()
    {
        // OntoWiki_Navigation::reset();
        // OntoWiki::reset ();
        parent::tearDown();
    }
    
    public function testNoControllerAndActionDefaultToNewsAction()
    {
        $this->dispatch('/');
        
        $this->assertController('index');
        $this->assertAction('news');
    }
    
    
    public function testInvalidActionNoDefaultActionDefaultsToNewsAction()
    {
        $config = OntoWiki::getInstance()->config;
        unset($config->index->default->controller);
        unset($config->index->default->action);
        
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
    
    /*
    public function testInvalidActionWithMessagesDefaultsToMessagesAction()
    {
        /*
         * It will produces:
         * Undefined index: class_name</summary>
         * PHPUnit_Framework_Error_Notice</code></p>
         * /home/k00ni/Documents/ow_cubeviz/libraries/Erfurt/Erfurt/Event/Dispatcher.php@140</code>
         * <br/ ><code>#0: PHPUnit_Util_ErrorHandler::handleError@/home/k00ni/Documents/ow_cubeviz/libraries/Erfurt/Erfurt/Event/Dispatcher.php:140<br />#1: Erfurt_Event_Dispatcher->trigger@/home/k00ni/Documents/ow_cubeviz/libraries/Erfurt/Erfurt/Event.php:180<br />#2: Erfurt_Event->trigger@/home/k00ni/Documents/ow_cubeviz/application/classes/OntoWiki/Controller/Plugin/SetupHelper.php:102<br />#3: OntoWiki_Controller_Plugin_SetupHelper->routeShutdown@/home/k00ni/Documents/ow_cubeviz/libraries/Zend/Controller/Plugin/Broker.php:260<br />#4: Zend_Controller_Plugin_Broker->routeShutdown@/home/k00ni/Documents/ow_cubeviz/libraries/Zend/Controller/Front.php:923<br />#5: Zend_Controller_Front->dispatch@/home/k00ni/Documents/ow_cubeviz/libraries/Zend/Application/Bootstrap/Bootstrap.php:97<br />#6: Zend_Application_Bootstrap_Bootstrap->run@/home/k00ni/Documents/ow_cubeviz/libraries/Zend/Application.php:366<br />#7: Zend_Application->run@/home/k00ni/Documents/ow_cubeviz/libraries/Zend/Test/PHPUnit/ControllerTestCase.php:206<br />#8: Zend_Test_PHPUnit_ControllerTestCase->dispatch@/home/k00ni/Documents/ow_cubeviz/application/tests/controllers/ServiceControllerTest.php:224<br />#9: ServiceControllerTest->sparqlNoAuthWithInvalidQuery<br />#10: ReflectionMethod->invokeArgs@/usr/share/php/PHPUnit/Framework/TestCase.php:942<br />#11: PHPUnit_Framework_TestCase->runTest@/usr/share/php/PHPUnit/Framework/TestCase.php:804<br />#12: PHPUnit_Framework_TestCase->runBare@/usr/share/php/PHPUnit/Framework/TestResult.php:649<br />#13: PHPUnit_Framework_TestResult->run@/usr/share/php/PHPUnit/Framework/TestCase.php:751<br />#14: PHPUnit_Framework_TestCase->run@/usr/share/php/PHPUnit/Framework/TestSuite.php:772<br />#15: PHPUnit_Framework_TestSuite->runTest@/usr/share/php/PHPUnit/Framework/TestSuite.php:745<br />#16: PHPUnit_Framework_TestSuite->run@/usr/share/php/PHPUnit/Framework/TestSuite.php:705<br />#17: PHPUnit_Framework_TestSuite->run@/usr/share/php/PHPUnit/TextUI/TestRunner.php:325<br />#18: PHPUnit_TextUI_TestRunner->doRun@/usr/share/php/PHPUnit/TextUI/Command.php:192<br />#19: PHPUnit_TextUI_Command->run@/usr/share/php/PHPUnit/TextUI/Command.php:130<br />#20: PHPUnit_TextUI_Command::main@/usr/bin/phpunit:46<br /> 
         */
        /*
        $owApp = OntoWiki::getInstance();
    
        $owApp->setBootstrap ( $this->bootstrap->getBootstrap() );
        $this->assertNotEmpty(null == $owApp->appendMessage(new OntoWiki_Message('Test Message')));
        
        $this->dispatch('/index/actionXYZNotExisting');
        
        $this->assertController('index');
        $this->assertAction('messages');
    }*/
    
    public function testEmptyAction()
    {        
        $this->dispatch('/index/');
        
        // var_dump ($this->response->getBody ());
        
        $this->assertController('index');
        $this->assertAction('news');
        // $this->assertNotQuery('div.section-mainwindows');
        // $this->assertQuery('div.section-sidewindows');
    }
    
    public function testMessagesActionNoMessages()
    {
        $this->dispatch('/index/messages');
        
        $this->assertController('index');
        $this->assertAction('messages');
        // $this->assertQueryContentContains('p.messagebox', 'No messages');
    }
    
    public function testMessagesActionSingleMessage()
    {
        $owApp = OntoWiki::getInstance();
        
        /**
         * Next line prevents you from getting this two error messages:
         * - PHP Fatal error:  Call to a member function getResource() on a non-object in /home/k00ni/Documents/ow_cubeviz/application/classes/OntoWiki.php on line 172
         * - Fatal error: Call to a member function getResource() on a non-object in /home/k00ni/Documents/ow_cubeviz/application/classes/OntoWiki.php on line 172
         */
        $owApp->setBootstrap ( $this->bootstrap->getBootstrap() );
        
        $owApp->appendMessage(new OntoWiki_Message('Test Message 123', OntoWiki_Message::INFO));
        
        $this->dispatch('/index/messages');
        
        $this->assertController('index');
        $this->assertAction('messages');
        $this->assertQueryContentContains('p.messagebox.info', 'Test Message 123');
    }
    
    public function testMessagesActionMultipleMessages()
    {
        $owApp = OntoWiki::getInstance();
        $owApp->setBootstrap ( $this->bootstrap->getBootstrap() );
        
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
            file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'_files'.DIRECTORY_SEPARATOR.'aksw.rss')
        ));
        
        $this->dispatch('/index/news');
        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQueryContentContains('h1.title', 'News');
        $this->assertQueryCount('div.messagebox.feed', 5);
    }
   
     
    public function testNewsActionFail()
    {        
        $this->dispatch('/');
        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQueryContentContains('h1.title', 'News');
        // $this->assertQuery('p.messagebox.warning');

    }
    
    public function testNewsshortActionSuccess()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(new Zend_Http_Response(
            200, 
            array(),
            file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'_files'.DIRECTORY_SEPARATOR.'aksw.rss')
        ));
        
        $this->dispatch('/index/newsshort');
        $this->assertController('index');
        $this->assertAction('newsshort');
        // $this->assertQueryContentContains('h1.title', 'News');
        $this->assertQueryCount('div.messagebox.feed', 3);
    }
    
    public function testNewsshortActionFail()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(new Zend_Http_Response(404, array(), ''));
        
        $this->dispatch('/index/newsshort');
        
        //echo ($this->_response->getBody());exit;
        
        $this->assertController('index');
        $this->assertAction('newsshort');
        // $this->assertQueryContentContains('h1.title', 'News');
        // $this->assertQuery('p.messagebox.warning');
    }
}
