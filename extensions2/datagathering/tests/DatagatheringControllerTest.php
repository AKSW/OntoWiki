<?php

require_once 'TestHelper.php';

class LinkeddataWrapperTest extends Zend_Test_PHPUnit_ControllerTestCase
{   
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    public function testImportActionRequestTypeNotGetBadRequest()
    {
        $this->request->setMethod('POST');
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(400);
    }
    
    public function testImportActionNoParamsBadRequest()
    {
        $this->dispatch('/datagathering/import');
        
        var_dump($this->response->getBody());exit;
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(400);
    }
    
    public function testImportActionInvalidWrapperParamBadRequest()
    {
        $this->request->setQuery(array(
            'wrapper' => 'anInvalidWrapperName123456789ThisShouldNeverExist'
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(400);
    }
    
    /*
    public function testImportActionNoWritePermissionsForbidden()
    {
        $this->request->setQuery(array(
            'uri' => 'http://example.org/testResource1',
            'm' => 'http://localhost/'
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(403);
    }
    */
}
