<?php

class SourceControllerTest extends OntoWiki_Test_ControllerTestCase
{
    public function setUp()
    {
        $this->_extensionName = 'source';

        $this->setUpExtensionUnitTest();
    }

    public function testDispatching()
    {
        $this->_storeAdapter->createModel('http://localhost/OntoWiki/Config/');
        $this->_ac->setUserModelRight('http://localhost/OntoWiki/Config/', 'view', 'grant');
        $this->_ac->setUserModelRight('http://localhost/OntoWiki/Config/', 'edit', 'grant');

        $this->request->setParam('m', 'http://localhost/OntoWiki/Config/');
        $this->dispatch('/source/edit');

        $this->assertController('source');
        $this->assertAction('edit');
        @$this->assertResponseCode(200);
    }
}
