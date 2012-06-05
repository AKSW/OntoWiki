<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once '../test_base.php';

// PHPUnit
#require_once 'PHPUnit/Framework.php';
require_once '../../../libraries/Erfurt/tests/Erfurt/TestCase.php';

/**
 * InstancesTest tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class OntoWiki_Model_InstancesTest extends Erfurt_TestCase
{
    protected $_instances;

    protected $_store;

    public function setUp()
    {
        $this->markTestNeedsDatabase();
        $this->_store = Erfurt_App::getInstance()->getStore();
        $this->_instances = new OntoWiki_Model_Instances($this->_store, new Erfurt_Rdf_Model('http://graph.com'));
    }

    public function testQuery()
    {
        echo $this->_instances->getResourceQuery();
    }

    public function testSerialization()
    {
        ob_start();
        $this->_instances = unserialize(serialize($this->_instances));
        $v = $this->_instances->getValues();
        $o = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(empty($o)); //no warnings
    }
}
