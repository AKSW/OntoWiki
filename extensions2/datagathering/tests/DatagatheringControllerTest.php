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
    
    public function testImportActionModelNotEditableForbidden()
    {
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'deny');
        
        $this->request->setQuery(array(
            'uri' => 'http://example.org/testResource1',
            'm'   => 'http://example.org/testModel1'
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(403);
    }
    
    public function testImportActionWrapperResultNoArrayFalse()
    {
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');
        
        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test' 
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(200);
        
        $this->assertFalse(json_decode($this->_response->getBody()));
    }
    
    public function testImportActionWrapperResultEmptyArrayFalse()
    {
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');
        
        Erfurt_Wrapper_Test::$runResult = array();
        
        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test' 
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(200);
        
        $this->assertFalse(json_decode($this->_response->getBody()));
    }
    
    public function testImportActionWrapperResultArrayNoAddTrue()
    {
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');
        
        Erfurt_Wrapper_Test::$runResult = array('status_codes' => array());
        
        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test' 
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(200);
        
        $this->assertTrue(json_decode($this->_response->getBody()));
    }
    
    public function testImportActionWrapperResultArrayWithAddButNothingAddedTrue()
    {
        Erfurt_App::getInstance()->getVersioning()->enableVersioning(false);
        
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');
        
        Erfurt_Wrapper_Test::$runResult = array(
            'status_codes' => array(Erfurt_Wrapper::RESULT_HAS_ADD),
            'add' => array()
        );
        
        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test' 
        ));
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(200);
        
        $this->assertTrue(json_decode($this->_response->getBody()));
    }
    
    public function testImportActionWrapperResultArrayWithAddTrue()
    {
        Erfurt_App::getInstance()->getVersioning()->enableVersioning(false);
        
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');
        
        $add = array(
            'http://example.org/testResource1' => array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array(array(
                    'type' => 'uri', 
                    'value' => 'http://xmlns.com/foaf/0.1/Person'
                )),
                'http://xmlns.com/foaf/0.1/nick' => array(array(
                    'type' => 'literal', 
                    'value' => 'testResource1'
                ))
            )
        );
        
        Erfurt_Wrapper_Test::$runResult = array(
            'status_codes' => array(Erfurt_Wrapper::RESULT_HAS_ADD),
            'add' => $add
        );
        
        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test' 
        ));
        
        
        $this->dispatch('/datagathering/import');
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(200);
        
        $this->assertTrue(json_decode($this->_response->getBody()));
        
        $this->assertEquals($add, $this->_testAdapter->getStatementsForGraph('http://example.org/testModel1'));
    }
    
    public function testImportActionWrapperResultArrayWithAddMatchingPresetTrue()
    {
        Erfurt_App::getInstance()->getVersioning()->enableVersioning(false);
        
        $this->_testAdapter->createModel('http://example.org/testModel1');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_testAc->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');
        
        $add = array(
            'http://dbpedia.org/resource/Leipzig' => array(
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array(array(
                    'type' => 'uri', 
                    'value' => 'http://xmlns.com/foaf/0.1/Person'
                )),
                'http://xmlns.com/foaf/0.1/nick' => array(array(
                    'type' => 'literal', 
                    'value' => 'testResource1'
                ))
            )
        );
        
        Erfurt_Wrapper_Test::$runResult = array(
            'status_codes' => array(Erfurt_Wrapper::RESULT_HAS_ADD),
            'add' => $add
        );
        
        $this->request->setQuery(array(
            'uri'     => 'http://dbpedia.org/resource/Leipzig',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test' 
        ));
        
        
        $this->dispatch('/datagathering/import');
        
        //echo $this->_response->getBody();exit;
        
        $this->assertController('datagathering');
        $this->assertAction('import');
        $this->assertResponseCode(200);
        
        $this->assertTrue(json_decode($this->_response->getBody()));
        
        $this->assertEquals(array(), $this->_testAdapter->getStatementsForGraph('http://example.org/testModel1'));
    }
}
