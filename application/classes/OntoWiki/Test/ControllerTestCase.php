<?php
/**
 * Created by JetBrains PhpStorm.
 * User: philipp
 * Date: 08.09.12
 * Time: 14:59
 * To change this template use File | Settings | File Templates.
 */
class OntoWiki_Test_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    /** @var Erfurt_Ac_Test|Erfurt_Ac_Default */
    protected $_ac = null;

    /** @var Erfurt_Store_Adapter_Interface */
    protected $_storeAdapter = null;

    /** @var string|null */
    protected $_extensionName = null;

    public function setUpIntegrationTest()
    {
        $this->bootstrap = new Zend_Application(
            'integration_testing',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );

        try {
            parent::setUp();
        } catch (Exception $e) {
            // if we can't connect to the database, we skip the test
            $this->markTestSkipped($e->getMessage());
        }

        // additional checks for database....
        $this->_markTestNeedsDatabase();
    }

    public function setUpUnitTest()
    {
        $this->bootstrap = new Zend_Application(
            'unit_testing',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );

        parent::setUp();
    }

    public function setUpExtensionUnitTest()
    {
        $this->bootstrap = new Zend_Application(
            'extension_unit_testing',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();

        if (null !== $this->_extensionName) {
            $extensionManager = OntoWiki::getInstance()->extensionManager;

            if (!$extensionManager->isExtensionActive($this->_extensionName)) {
                Erfurt_Event_Dispatcher::reset();
                $this->markTestSkipped('extension is not active');
            }
        }

        $this->_ac = Erfurt_App::getInstance(false)->getAc();
        $this->_storeAdapter = Erfurt_App::getInstance(false)->getStore()->getBackendAdapter();
    }

    private function _markTestNeedsDatabase()
    {
        $config =  Erfurt_App::getInstance(false)->getConfig();

        $dbName = null;
        if ($config->store->backend === 'virtuoso') {
            if (isset($config->store->virtuoso->dsn)) {
                $dbName = $config->store->virtuoso->dsn;
            }
        } else if ($config->store->backend === 'zenddb') {
            if (isset($config->store->zenddb->dbname)) {
                $dbName = $config->store->zenddb->dbname;
            }
        }

        if ((null === $dbName) || (substr($dbName, -5) !== '_TEST')) {
            $this->markTestSkipped('Invalid test database for tests: ' . $dbName); // make sure a test db was selected!
        }

        try {
            $store = Erfurt_App::getInstance(false)->getStore();
            $store->checkSetup();
            $this->_dbWasUsed = true;
        } catch (Erfurt_Store_Exception $e) {
            if ($e->getCode() === 20) {
                // Setup successful
                $this->_dbWasUsed = true;
            } else {
                $this->markTestSkipped();
            }
        } catch (Erfurt_Exception $e2) {
            $this->markTestSkipped();
        }

        $this->assertTrue(Erfurt_App::getInstance()->getStore()->isModelAvailable($config->sysont->modelUri, false));
        $this->assertTrue(Erfurt_App::getInstance()->getStore()->isModelAvailable($config->sysont->schemaUri, false));
    }
}
