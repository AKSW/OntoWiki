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
    
    public function tearDown()
    {
        // Always logout the user, since we have multiple auth tests
        Erfurt_Auth::getInstance()->clearIdentity();
    }
    
    public function testCallWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/service');
        
        // We expect the error controller with error action here, since ther is
        // no default index action for this controller.
        $this->assertController('error');
        $this->assertAction('error');
    }
    
    
    public function testAuthActionGetNotAllowed()
    {
        $config = OntoWiki::getInstance()->config;
        $config->service->auth->allowGet = false;
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(405);
        $this->assertHeaderContains('allow', 'POST');
    }
    
    /**
     * We enable GET authentication and test that we do not get a 
     * 405 Method No Allowed response.
     */
    public function testAuthActionGetAllowed()
    {
        $config = OntoWiki::getInstance()->config;
        $config->service->auth->allowGet = true;
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(400);
    }
    
    public function testAuthActionNoParams()
    {
        $this->request->setMethod('POST');
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(400);
    }
    
    public function testAuthActionLogoutTrue()
    {
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'logout' => 'true'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
    }
    
    public function testAuthActionLogoutInvalidValue()
    {
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'logout' => 'xyz'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(400);
    }
    
    
    public function testAuthActionAnonymousUserNoPasswordSuccess()
    {
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'username' => 'Anonymous'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
    }
    
    
    public function testAuthActionAnonymousUserPasswordSetSuccess()
    {
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'username' => 'Anonymous',
                          'password' => ''
                      ));
        
        $this->dispatch('/service/auth');
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
    }
    
    public function testAuthActionInvalidUser()
    {
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'username' => 'xyz',
                          'password' => '123'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(401);
    }
}
