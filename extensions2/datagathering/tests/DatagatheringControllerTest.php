<?php

require_once 'TestHelper.php';

class LinkeddataWrapperTest extends Zend_Test_PHPUnit_ControllerTestCase
{   
    protected $_testAc = null;
    protected $_testAdapter = null;
    
    public function setUp()
    {
        $this->_testAc = new Erfurt_Ac_Test();
        Erfurt_App::getInstance()->setAc($this->_testAc);
        
        $this->_testAdapter = new Erfurt_Store_Adapter_Test();
        Erfurt_App::getInstance()->setStore(new Erfurt_Store(
            array('adapterInstance' => $this->_testAdapter), 
            'Test'
        ));
        
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
        
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }
    
    public function testImportActionNoParamsBadRequest()
    {
        $this->dispatch('/datagathering/import');
        
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }
    
    public function testImportActionInvalidWrapperParamBadRequest()
    {
        $this->request->setQuery(array(
            'wrapper' => 'anInvalidWrapperName123456789ThisShouldNeverExist'
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }
    
    public function testImportActionInvalidModelUnauthorized()
    {
        $this->request->setQuery(array(
            'uri' => 'http://example.org/testResource1',
            'm'   => 'http://example.org/testModel1'
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(401);
    }
}
