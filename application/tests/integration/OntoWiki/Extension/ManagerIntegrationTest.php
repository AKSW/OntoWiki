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
        OntoWiki_Extension_Manager::clearCache();
        $this->_resourcesDirectory = realpath(dirname(__FILE__)) . '/_files/';

        $this->markTestNeedsTestConfig();

        $this->_bootstrap = new Zend_Application(
            'testing',
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
        $em = new OntoWiki_Extension_Manager($this->_resourcesDirectory);
        $this->assertCount(2, $em->getExtensions());
    }
}