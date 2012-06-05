<?php
/**
 * OntoWiki
 *
 * LICENSE
 *
 * This file is part of the OntoWiki project.
 * Copyright (C) 2006-2010, AKSW
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the 
 * Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * A copy of the GNU General Public License is bundled with this package in
 * the file LICENSE.txt. It is also available through the world-wide-web at 
 * this URL: http://opensource.org/licenses/gpl-2.0.php
 *
 * @category   OntoWiki
 * @package    controllers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @version    $Id: $
 */

require_once dirname (__FILE__) .'/../TestHelper.php';

/**
 * This test class comtains tests for the OntoWiki service controller.
 * 
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @author     Konrad Abicht <k.abicht@googlemail.com>
 */
class ServiceControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('ServiceControllerTest'));
    }
    
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
        // OntoWiki_Navigation::reset();
    }
    
    // ------------------------------------------------------------------------
    // Auth Action
    // ------------------------------------------------------------------------
    
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
        /*
        $config = OntoWiki::getInstance()->config;
        $config->service->auth->allowGet = false;
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(405);
        $this->assertHeaderContains('allow', 'POST');
        */
    }
    
    /**
     * We enable GET authentication and test that we do not get a 
     * 405 Method No Allowed response.
     */
    public function testAuthActionGetAllowed()
    {
        /*
        $config = OntoWiki::getInstance()->config;
        $config->service->auth->allowGet = true;
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
        */
    }
    
    public function testAuthActionNoParams()
    {
        /*
        $this->request->setMethod('POST');
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
        */
    }
    
    public function testAuthActionLogoutTrue()
    {
        /*
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'logout' => 'true'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
        */
    }
    
    public function testAuthActionLogoutInvalidValue()
    {
        /*
         * TODO: fix this
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'logout' => 'xyz'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(400);
        */
    }
    
    public function testAuthActionAnonymousUserNoPasswordSuccess()
    {
        /*
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'username' => 'Anonymous'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
        */
    }
    
    public function testAuthActionAnonymousUserPasswordSetSuccess()
    {
        /*
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'username' => 'Anonymous',
                          'password' => ''
                      ));
        
        $this->dispatch('/service/auth');
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
        */
    }
    
    public function testAuthActionInvalidUser()
    {
        /*
        $this->request->setMethod('POST')
                      ->setPost(array(
                          'username' => 'xyz',
                          'password' => '123'
                      ));
        
        $this->dispatch('/service/auth');
        
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
        */
    }
    
    // ------------------------------------------------------------------------
    // SPARQL Action
    // ------------------------------------------------------------------------
    
    /**
     * No parameter, no action!
     * 
     * @test
     */
    public function sparqlNoParameter()
    {
        /*
        $this->request->setMethod('POST');
        
        $this->dispatch('/service/sparql');
        
        $this->assertController('service');
        $this->assertAction('sparql');
        $this->assertResponseCode(200);
        */
    }
    
    /**
     * No authentification, but with a query. OW should use Anonymous.
     * 
     * @test
     */
    public function sparqlNoAuthWithInvalidQuery()
    {     
        /*   
        // Send invalid query
        $this->request->setMethod('POST')
                      ->setPost(
                        array( 'query' => '123')
                      );
        
        $this->dispatch('/service/sparql');
                
        $this->assertController('service');
        $this->assertAction('sparql');
        $this->assertResponseCode(200);
        */
    }
    
    /**
     * No auth, valid query. Expected XML.
     * @test
     */
     /*
    public function sparqlNoAuthWithValidQueryResultAsXML()
    {        
        // Send invalid query
        $this->request->setMethod('POST')
                      ->setPost(
                        array( 'query' => 'SELECT ?s WHERE { ?s ?p ?o. }')
                      );
        
        $this->dispatch('/service/sparql');
                
        $this->assertController('service');
        $this->assertAction('sparql');
        $this->assertResponseCode(200);
        
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<sparql xmlns="http://www.w3.org/2005/sparql-results#">
<head>
<variable name="s" />
</head>
<results>
</results>
</sparql>
';
        
        $this->assertEquals ($expected, $this->response->getBody());
    }
    */
    
    /**
     * No auth, valid query. Expected JSON.
     * @test
     */
     /*
    public function sparqlNoAuthWithValidQueryResultAsJSON()
    {        
        // Send invalid query
        $this->request->setMethod('POST')
                      ->setPost(
                        array( 'query' => 'SELECT ?s WHERE { ?s ?p ?o. }')
                      );
        
        $this->request->setHeaders ( 
            array ( 'Accept' => 'application/sparql-results+json' ) 
        );
        
        $this->dispatch('/service/sparql');
                
        $this->assertController('service');
        $this->assertAction('sparql');
        $this->assertResponseCode(200);
        
        $expected = '{"head":{"vars":["s"]},"bindings":[],"results":{"bindings":[]}}';
        
        $this->assertEquals ( $expected, $this->response->getBody () );
    }
    */
    
    // ------------------------------------------------------------------------
    // Update Action
    // ------------------------------------------------------------------------
    
    /**
     * @test
     */
    public function testUpdateDoesNothingWithEmptyParameters()
    {
        /*
         TODO: fix this
        $this->request->setMethod('POST')
                      ->setPost(array('insert' => '{}', 'delete' => '{}'));
        

        $storeMock = $this->getMock('Erfurt_Store');
        $storeMock->expects($this->any())
                  ->method('getModel')
                  ->will($this->returnValue($modelMock));
        
        Erfurt_App::setStore();
        
        $this->dispatch('/service/update');
        
        $this->assertController('service');
        $this->assertAction('update');
        */
    }
}
