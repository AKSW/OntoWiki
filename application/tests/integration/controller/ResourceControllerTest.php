<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class contains tests for the OntoWiki resource controller.
 *
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
class ResourceControllerTest extends OntoWiki_Test_ControllerTestCase
{
    private $_modelUri = 'http://example.org/ResourceControllerTest/';
    private $_resource = 'http://example.org/subject';
    private $_other = 'http://example.org/other';
    private $_store;
    private $_model;

    public function setUp()
    {
        $this->setUpIntegrationTest();

        $this->_store = Erfurt_App::getInstance()->getStore();

        if (!$this->_store->isModelAvailable($this->_modelUri)) {
            $this->_model = $this->_store->getNewModel($this->_modelUri);
            $this->_model->addStatement($this->_resource, EF_RDF_TYPE, array('type' => 'literal', 'value' => 'test'));
            $this->_model->addStatement($this->_other, EF_RDF_TYPE, array('type' => 'literal', 'value' => 'test'));
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

    // ------------------------------------------------------------------------
    // Delete Action
    // ------------------------------------------------------------------------

    public function testDeleteActionGET()
    {
        $this->request->setQuery(array('r' => $this->_resource));
        $this->dispatch('/resource/delete');
        $this->assertNotEquals(0, count($this->_model->getResource($this->_resource)->getDescription()[$this->_resource]),
                               'Resource should not be deleted with GET method');
        $this->assertNotEquals(0, count($this->_model->getResource($this->_other)->getDescription()[$this->_other]),
                               'Other resources should not be deleted');
    }

    public function testDeleteActionPOST()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('r' => $this->_resource));
        $this->dispatch('/resource/delete');
        $this->assertEquals(0, count($this->_model->getResource($this->_resource)->getDescription()[$this->_resource]),
                            'Resource should be deleted');
        $this->assertNotEquals(0, count($this->_model->getResource($this->_other)->getDescription()[$this->_other]),
                               'Other resources should not be deleted');
    }
}
