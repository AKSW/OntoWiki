<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

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
class IndexControllerTest extends OntoWiki_Test_ControllerTestCase
{
    public function setUp()
    {
        $this->setUpUnitTest();
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
        $config                             = OntoWiki::getInstance()->config;
        $config->index->default->controller = 'index';
        $config->index->default->action     = 'empty';

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

        $this->assertQueryContentContains('p.messagebox', 'Test Message');
    }

    public function testEmptyAction()
    {
        $this->dispatch('/index/');

        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQuery('div.section-mainwindows');
        $this->assertQuery('div.section-sidewindows');
    }

    public function testMessagesActionNoMessages()
    {
        $this->dispatch('/index/messages');

        $this->assertController('index');
        $this->assertAction('messages');
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
        $adapter->setResponse(
            new Zend_Http_Response(
                200,
                array(),
                file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'aksw.rss')
            )
        );

        $this->dispatch('/index/news');
        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQueryContentContains('h1.title', 'News');
        $this->assertQueryCount('div.messagebox.feed', 5);
    }


    public function testNewsActionFail()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        $adapter->setNextRequestWillFail(true);
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));

        $this->dispatch('/');

        $this->assertController('index');
        $this->assertAction('news');
        $this->assertQueryContentContains('h1.title', 'News');
        $this->assertQuery('p.messagebox.warning');

    }

    public function testNewsshortActionSuccess()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));
        $adapter->setResponse(
            new Zend_Http_Response(
                200,
                array(),
                file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'aksw.rss')
            )
        );

        $this->dispatch('/index/newsshort');
        $this->assertController('index');
        $this->assertAction('newsshort');
        $this->assertQueryCount('div.messagebox.feed', 3);
    }

    public function testNewsshortActionFail()
    {
        $adapter = new Zend_Http_Client_Adapter_Test();
        $adapter->setNextRequestWillFail(true);
        Zend_Feed::setHttpClient(new Zend_Http_Client(null, array('adapter' => $adapter)));

        $this->dispatch('/index/newsshort');

        $this->assertController('index');
        $this->assertAction('newsshort');
        $this->assertQuery('p.messagebox.warning');
    }

}
