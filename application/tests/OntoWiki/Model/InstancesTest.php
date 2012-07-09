<?php
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

//require_once dirname (__FILE__). '/../../../../libraries/Erfurt/tests/Erfurt/TestCase.php';

/**
 * It tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class OntoWiki_Model_InstancesTest extends PHPUnit_Framework_TestCase 
{
    protected $_instances = null;

    protected $_storeAdapter = null;
    protected $_store = null;

    public function setUp()
    {
        $this->_storeAdapter = new Erfurt_Store_Adapter_Test();
        
        $this->_store = new Erfurt_Store(array(
            'adapterInstance' => $this->_storeAdapter
        ), 'Test');
        
        $this->_instances = new OntoWiki_Model_Instances(
            $this->_store, 
            new Erfurt_Rdf_Model('http://graph.com/123/')
        ); // no config given
    }

    public function testGetResourceQuery()
    {
        $this->assertInstanceOf('Erfurt_Sparql_Query2', $this->_instances->getResourceQuery());
    }
    
    public function testSerialization()
    {
        ob_start();
        $this->_instances = unserialize(serialize($this->_instances));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(empty($o)); //no warnings
        
        $v = $this->_instances->getValues();
        $this->assertInternalType('array', $v);
    }
}
