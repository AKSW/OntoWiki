<?php

require_once '../test_base.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';


/**
 * InstancesTest tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class InstancesTest extends PHPUnit_Framework_TestCase{

    protected $instances;

    public function setUp(){
        $this->instances = new OntoWiki_Model_Instances(); //no config given
    }

    public function testQuery(){
        echo $this->instances->getResourceQuery();
    }
}
?>
