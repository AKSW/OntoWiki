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
    
    public function test1()
    {
        
    }
    
    public function test2()
    {
        
    }
}
