<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class comtains tests for the OntoWiki model controller.
 *
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
class ModelControllerTest extends OntoWiki_Test_ControllerTestCase
{
    private $_modelUri = 'http://example.org/ModelControllerTest/';
    private $_store;

    public function setUp()
    {
        $this->setUpIntegrationTest();

        $this->_store = Erfurt_App::getInstance()->getStore();

        if (!$this->_store->isModelAvailable($this->_modelUri)) {
            $this->_store->getNewModel($this->_modelUri);
        }
    }

    public function tearDown()
    {
        if ($this->_store->isModelAvailable($this->_modelUri)) {
            $this->_store->deleteModel($this->_modelUri);
        }

        unset($this->_store);

        parent::tearDown();
    }

    // ------------------------------------------------------------------------
    // Delete Action
    // ------------------------------------------------------------------------

    public function testDeleteActionGET()
    {
        $this->request->setQuery(array('model' => $this->_modelUri));
        $this->dispatch('/model/delete');
        $this->assertTrue($this->_store->isModelAvailable($this->_modelUri),
                          'Model should not be deleted with GET method');
    }

    public function testDeleteActionPOST()
    {
        $this->request->setMethod('POST')
                      ->setPost(array('model' => $this->_modelUri));
        $this->dispatch('/model/delete');
        $this->assertFalse($this->_store->isModelAvailable($this->_modelUri),
                           'Model should be deleted');
    }
}
