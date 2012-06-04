<?php 

require_once dirname (__FILE__) . '/../../../application/tests/TestHelper.php';

class SourceControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    public function testDispatching()
    {
        /*
        $this->dispatch('/source/edit');
        $this->request->setParam('m', 'http://localhost/OntoWiki/Config/');
        
        $r = $this->getResponse();
        // var_dump($r->getBody());
        
        $this->assertController('source');
        $this->assertAction('edit');
        */
    }
}
