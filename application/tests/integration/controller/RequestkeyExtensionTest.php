<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class contains tests for the requestkey extension.
 *
 * @category   OntoWiki
 * @package    Extensions_Requestkey
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
class RequestkeyExtensionTest extends OntoWiki_Test_ControllerTestCase
{
    private $_modelUri = 'http://example.org/RequestkeyExtensionTest/';
    private $_store;
    private $_model;

    public function setUp()
    {
        $this->setUpIntegrationTest();

        $this->_store = Erfurt_App::getInstance()->getStore();

        $this->_model = $this->_store->isModelAvailable($this->_modelUri);
        if (!$this->_model) {
            $this->_model = $this->_store->getNewModel($this->_modelUri);
        }
    }

    public function tearDown()
    {
        if ($this->_store->isModelAvailable($this->_modelUri)) {
            $this->_store->deleteModel($this->_modelUri);
        }

        unset($this->_store);
        unset($this->_model);

        parent::tearDown();
    }

    public function testBaseControllerRequestWithValidKey()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('model' => $this->_modelUri));

        $this->dispatch('/model/delete');

        @$this->assertNotResponseCode(500, 'HTTP status code should not indicate CSRF exception');
        $this->assertFalse($this->_store->isModelAvailable($this->_modelUri),
                           'Model should be deleted');
    }

    public function testServiceControllerRequestWithValidKey()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('named-graph-uri' => $this->_modelUri,
                                      'insert' => '{}',
                                      'delete' => '{}'));

        $this->dispatch('/service/update');

        @$this->assertNotHeader('WWW-Authenticate');
        @$this->assertNotResponseCode(500, 'HTTP status code should not indicate CSRF exception');
    }

    public function testBaseControllerRequestWithInvalidKey()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('model' => $this->_modelUri));

        $this->request->setPost(array(OntoWiki_Controller::REQUESTKEY_FIELD_NAME => 'invalid'));

        $this->dispatch('/model/delete');

        @$this->assertResponseCode(500, 'HTTP status code should indicate CSRF exception');
        $this->assertTrue($this->_store->isModelAvailable($this->_modelUri),
                          'Model should not be deleted');
    }

    public function testServiceControllerRequestWithInvalidKey()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('named-graph-uri' => $this->_modelUri,
                                      'insert' => '{}',
                                      'delete' => '{}'));

        $this->request->setPost(array(OntoWiki_Controller::REQUESTKEY_FIELD_NAME => 'invalid'));

        $this->dispatch('/service/update');

        @$this->assertResponseCode(500, 'HTTP status code should indicate CSRF exception');
    }

    public function testBaseControllerRequestWithNoKey()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('model' => $this->_modelUri));

        unset($_POST[OntoWiki_Controller::REQUESTKEY_FIELD_NAME]);

        $this->dispatch('/model/delete');

        @$this->assertResponseCode(500, 'HTTP status code should indicate CSRF exception');
        $this->assertTrue($this->_store->isModelAvailable($this->_modelUri),
                          'Model should not be deleted');
    }

    public function testServiceControllerRequestWithNoKey()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('named-graph-uri' => $this->_modelUri,
                                      'insert' => '{}',
                                      'delete' => '{}'));

        unset($_POST[OntoWiki_Controller::REQUESTKEY_FIELD_NAME]);

        $this->dispatch('/service/update');

        @$this->assertResponseCode(500, 'HTTP status code should indicate CSRF exception');
    }
}
