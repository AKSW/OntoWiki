<?php

require_once '../test_base.php';

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    public function tearDown()
    {
        OntoWiki_Navigation::reset();
    }
    
    public function testTest()
    {
        $this->dispatch('/');
        
        $r = $this->getResponse();
        //var_dump($r->getBody());
        
        $this->assertController('index');
        $this->assertAction('news');
    }
    
    public function testAnotherTest()
    {
        $this->dispatch('/');
        
        $r = $this->getResponse();
        //var_dump($r->getBody());
        
        $this->assertController('index');
        $this->assertAction('news');
    }
}
