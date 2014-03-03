<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * It tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class ManagerIntegrationTest extends Erfurt_TestCase
{
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
        Erfurt_App::getInstance(false)->getCache()->clean();

        // clear cache, since otherwise the extension manager may have the real extensions loaded
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache('user');
        }

        $em = new OntoWiki_Extension_Manager($this->_resourcesDirectory, CACHE_PATH . 'extensions_test.json');
        $ex = $em->getExtensions();

        $this->assertCount(2, $ex);
        $this->assertArrayHasKey('test1', $ex);
        $this->assertArrayHasKey('test2', $ex);
        //test local ini
        $this->assertFalse((bool)$ex['test2']->private->sub->b);
    }
}
