<?php
    /**
 * It tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class ManagerIntegrationTest extends Erfurt_TestCase {

    protected $_resourcesDirectory = null;
    protected $_bootstrap = null;

    protected function setUp()
    {
        $this->_resourcesDirectory = realpath(dirname(__FILE__)) . '/_files/';

        $this->markTestNeedsDatabase();

        $this->_bootstrap = new Zend_Application(
            'integration_testing',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );

        // bootstrap
        try {
            $this->_bootstrap->bootstrap();
        } catch (Exception $e) {
            echo 'Error on bootstrapping application: ';
            echo $e->getMessage();
            return;
        }

        $this->authenticateDbUser();
    }

    /**
     * @medium
     */
    public function testScan()
    {
        $em = new OntoWiki_Extension_Manager($this->_resourcesDirectory, CACHE_PATH . 'extensions_test.json');
        $ex = $em->getExtensions();
        $this->assertCount(2, $ex);
        $this->assertArrayHasKey('test1', $ex);
        $this->assertArrayHasKey('test2', $ex);
        //test local ini
        $this->assertFalse((bool)$ex['test2']->private->sub->b);
    }
}
