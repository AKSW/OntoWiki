<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class comtains tests for the OntoWiki service controller.
 *
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @author     Konrad Abicht <k.abicht@googlemail.com>
 */
class ServiceControllerTest extends OntoWiki_Test_ControllerTestCase
{
    public function setUp()
    {
        $this->setUpIntegrationTest();
    }

    // ------------------------------------------------------------------------
    // Auth Action
    // ------------------------------------------------------------------------

    public function testCallWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/service');

        // We expect the error controller with error action here, since there is
        // no default index action for this controller.
        $this->assertController('error');
        $this->assertAction('error');
    }

    public function testAuthActionGetNotAllowed()
    {
        $config                          = OntoWiki::getInstance()->config;
        $config->service->auth->allowGet = false;

        $this->dispatch('/service/auth');

        $this->assertController('service');
        $this->assertAction('auth');

        // TODO: Remove the @ again, when the ZF issue is resolved
        // Currently there is a interface mismatch between PHPUnit >= 3.6 and ZF 1.x
        @$this->assertResponseCode(405);
        $this->assertHeaderContains('allow', 'POST');
    }

    /**
     * We enable GET authentication and test that we do not get a
     * 405 Method No Allowed response.
     */
    public function testAuthActionGetAllowed()
    {
        $config                        = OntoWiki::getInstance()->config;
        $config->service->allowGetAuth = true;

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
        $this->request->setMethod('POST')->setPost(
            array(
                 'logout' => 'true'
            )
        );

        $this->dispatch('/service/auth');

        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
    }

    public function testAuthActionLogoutInvalidValue()
    {
        $this->request->setMethod('POST')->setPost(
            array(
                 'logout' => 'xyz'
            )
        );

        $this->dispatch('/service/auth');

        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(400);
    }

    public function testAuthActionAnonymousUserNoPasswordSuccess()
    {
        $this->request->setMethod('POST')->setPost(
            array(
                 'u' => 'Anonymous'
            )
        );

        $this->dispatch('/service/auth');

        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
    }

    public function testAuthActionAnonymousUserPasswordSetSuccess()
    {
        $this->request->setMethod('POST')->setPost(
            array(
                 'u' => 'Anonymous',
                 'p' => ''
            )
        );

        $this->dispatch('/service/auth');
        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(200);
    }

    public function testAuthActionInvalidUser()
    {
        $this->request->setMethod('POST')->setPost(
            array(
                 'u' => 'xyz',
                 'p' => '123'
            )
        );

        $this->dispatch('/service/auth');

        $this->assertController('service');
        $this->assertAction('auth');
        $this->assertResponseCode(401);
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
        $this->request->setMethod('POST');

        $this->dispatch('/service/sparql');

        $this->assertController('service');
        $this->assertAction('sparql');
        $this->assertResponseCode(200);
    }

    /**
     * No authentification, but with a query. OW should use Anonymous.
     *
     * @test
     */
    public function sparqlNoAuthWithInvalidQuery()
    {
        // Send invalid query
        $this->request->setMethod('POST')->setPost(
            array('query' => '123')
        );

        $this->dispatch('/service/sparql');

        $code = $this->_response->getHttpResponseCode();

        $this->assertController('service');
        $this->assertAction('sparql');
        $this->assertResponseCode(400, "$code returned instead");
    }

    // ------------------------------------------------------------------------
    // Update Action
    // ------------------------------------------------------------------------

    public function testUpdateDoesNothingWithEmptyParameters()
    {
        $this->request->setMethod('POST')
            ->setPost(array('insert' => '{}', 'delete' => '{}'));

        $this->dispatch('/service/update');

        $this->assertController('service');
        $this->assertAction('update');
        $this->assertResponseCode(200);
    }
}
