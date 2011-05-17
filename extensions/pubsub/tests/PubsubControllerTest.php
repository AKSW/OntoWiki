<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

// This constant will not be defined iff this file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'PubsubControllerTest::main');
}

class PubsubControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('PubsubControllerTest'));
    }
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }
    
    public function testCallWithoutAction()
    {
        $this->dispatch('/pubsub');
        
        // We expect the error controller with error action here, since ther is
        // no default index action for this controller.
        $this->assertController('error');
        $this->assertAction('error');
    }
    
    public function testTestAction()
    {
        $this->dispatch('/pubsub/test');
        
        $this->assertController('pubsub');
        $this->assertAction('test');
        $this->assertResponseCode(200);
        
        $this->assertQueryContentContains('body', 'PubsubController is enabled');
    }
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'PubsubControllerTest::main') {
    PubsubControllerTest::main();
}
