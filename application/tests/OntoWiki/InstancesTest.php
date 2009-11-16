<?php

require_once '../test_base.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';
require_once '../../../erfurt/tests/Erfurt/Versioning/StoreStub.php';

/**
 * InstancesTest tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class InstancesTest extends PHPUnit_Framework_TestCase{

    protected $instances;
    protected $_storeStub;

    public function setUp(){
        $this->_storeStub = new Erfurt_Versioning_StoreStub();
        $this->instances = new OntoWiki_Model_Instances($this->_storeStub, "http://graph.com"); // no config given
    }

    public function testQuery(){
        echo $this->instances->getResourceQuery();
    }
}
?>
