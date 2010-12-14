<?php

require_once '../test_base.php';

class ServiceControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    /* We need to refactor the OntoWiki bootstrap mechanism in order to support more than one test
       case, since extension loading leads to problems.
    public function testCallWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/service');
        
        // We expect the error controller with error action here, since ther is
        // no default index action for this controller.
        $this->assertController('error');
        $this->assertAction('error');
    }
    */
    
    public function testAuthAction()
    {
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
    }
    
    
    
    
    
    
    
}
