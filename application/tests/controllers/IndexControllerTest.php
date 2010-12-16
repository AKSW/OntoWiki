<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';

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
