<?php

class DatagatheringControllerTest extends OntoWiki_Test_ControllerTestCase
{
    public function setUp()
    {
        $this->_extensionName = 'datagathering';

        $this->setUpExtensionUnitTest();
    }

    public function testImportActionRequestTypeNotGetBadRequest()
    {
        //$this->request->setMethod('POST');
        $this->dispatch('/datagathering/import');

        $this->assertController('error');
        $this->assertAction('error');
        @$this->assertResponseCode(400);
    }

    public function testImportActionNoParamsBadRequest()
    {
        $this->dispatch('/datagathering/import');

        $this->assertController('error');
        $this->assertAction('error');
        @$this->assertResponseCode(400);
    }

    public function testImportActionInvalidWrapperParamBadRequest()
    {
        $this->request->setQuery(array(
            'wrapper' => 'anInvalidWrapperName123456789ThisShouldNeverExist'
        ));

        $this->dispatch('/datagathering/import');

        $this->assertController('error');
        $this->assertAction('error');
        @$this->assertResponseCode(400);
    }

    public function testImportActionModelNotEditableForbidden()
    {
        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'deny');

        $this->request->setQuery(array(
            'uri' => 'http://example.org/testResource1',
            'm'   => 'http://example.org/testModel1'
        ));

        $this->dispatch('/datagathering/import');

        $this->assertController('error');
        $this->assertAction('error');
        @$this->assertResponseCode(403);
    }

    public function testImportActionWrapperResultFalse()
    {
        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');

        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test'
        ));

        $this->dispatch('/datagathering/import');

        $this->assertController('datagathering');
        $this->assertAction('import');
        @$this->assertResponseCode(200);

        $result = json_decode($this->_response->getBody(), true);

        $this->assertArrayHasKey('code', $result);
        $this->assertFalse($result['code']);
        $this->assertArrayHasKey('message', $result);
        $this->assertNotEmpty($result['message']);
    }

    public function testImportActionWrapperResultEmptyArray()
    {
        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');

        Erfurt_Wrapper_Test::$runResult = array();

        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test'
        ));

        $this->dispatch('/datagathering/import');

        $this->assertController('datagathering');
        $this->assertAction('import');
        @$this->assertResponseCode(200);

        $result = json_decode($this->_response->getBody(), true);

        $this->assertArrayHasKey('code', $result);
        $this->assertFalse($result['code']);
        $this->assertArrayHasKey('message', $result);
        $this->assertNotEmpty($result['message']);
    }

    public function testImportActionWrapperResultArrayNoAdd()
    {
        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');

        Erfurt_Wrapper_Test::$runResult = array('status_codes' => array());

        $this->request->setQuery(array(
            'uri'     => 'http://example.org/testResource1',
            'm'       => 'http://example.org/testModel1',
            'wrapper' => 'Erfurt_Wrapper_Test'
        ));

        $this->dispatch('/datagathering/import');

        $this->assertController('datagathering');
        $this->assertAction('import');
        @$this->assertResponseCode(200);

        $result = json_decode($this->_response->getBody(), true);

        $this->assertArrayHasKey('code', $result);
        $this->assertFalse($result['code']);
        $this->assertArrayHasKey('message', $result);
        $this->assertNotEmpty($result['message']);
    }

    public function testImportActionWrapperResultArrayWithAddButNothingAdded()
    {
        Erfurt_App::getInstance()->getVersioning()->enableVersioning(false);

        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');

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
        @$this->assertResponseCode(200);

        $result = json_decode($this->_response->getBody(), true);

        $this->assertArrayHasKey('code', $result);
        $this->assertFalse($result['code']);
        $this->assertArrayHasKey('message', $result);
        $this->assertNotEmpty($result['message']);
    }

    public function testImportActionWrapperResultArrayWithAdd()
    {
        Erfurt_App::getInstance()->getVersioning()->enableVersioning(false);

        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');

        $this->_storeAdapter->addCountResult(0);
        $this->_storeAdapter->addCountResult(2);

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
        @$this->assertResponseCode(200);

        $result = json_decode($this->_response->getBody(), true);

        $this->assertArrayHasKey('code', $result);
        $this->assertTrue($result['code']);
        $this->assertArrayHasKey('message', $result);
        $this->assertNotEmpty($result['message']);

        $this->assertEquals($add, $this->_storeAdapter->getStatementsForGraph('http://example.org/testModel1'));
    }

    public function testImportActionWrapperResultArrayWithAddMatchingPreset()
    {
        Erfurt_App::getInstance()->getVersioning()->enableVersioning(false);

        $this->_storeAdapter->createModel('http://example.org/testModel1');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'view', 'grant');
        $this->_ac->setUserModelRight('http://example.org/testModel1', 'edit', 'grant');

        $this->_storeAdapter->addCountResult(0);
        $this->_storeAdapter->addCountResult(2);

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

        $this->assertController('datagathering');
        $this->assertAction('import');
        @$this->assertResponseCode(200);

        $result = json_decode($this->_response->getBody(), true);

        $this->assertArrayHasKey('code', $result);
        $this->assertTrue($result['code']);
        $this->assertArrayHasKey('message', $result);
        $this->assertNotEmpty($result['message']);

        $this->assertEquals(array(), $this->_storeAdapter->getStatementsForGraph('http://example.org/testModel1'));
    }
}
