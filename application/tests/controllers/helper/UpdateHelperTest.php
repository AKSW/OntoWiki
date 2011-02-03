<?php 

// test environment
require_once dirname(__FILE__) . '/../../' . 'TestHelper.php';

// test fixture
require_once APPLICATION_PATH . 'controllers/helper/UpdateHelper.php';

class UpdateHelperTest extends PHPUnit_Framework_TestCase
{
    protected $_fixture = null;
    
    public function setUp()
    {
        $this->_fixture = new UpdateHelper;
    }
    
    public function testTest()
    {
        $hashedResponse = array(
            'http://example.com/resource1' => array(
                'http://example.com/property1' => array(
                    'e14a77747a618565585c1a269e8e3a3e'  // md5('"Test literal one."')
                ), 
                'http://example.com/property2' => array(
                    'c2d871c3bdcb22ba4bebe586f8159e10', // md5('"Test literal two."@en')
                    '763c31e308b9c3dd20aa1983985722b2'  // md5('"Test literal two bee."^^<http://www.w3.org/2001/XMLSchema#string>')
                ), 
            ), 
            'http://example.com/resource2' => array(
                'http://example.com/property3' => array(
                    'ffe8f4662c4065ffbe62d6449d625bb7', // md5('"1234"^^<http://www.w3.org/2001/XMLSchema#int>'), 
                    '757d204b68e8e1c419288694ab908f55'  // md5("123")
                )
            )
        );
        
        $queryResult = array('results' => array('bindings' => array(
            array('o' => array('type' => 'literal', 'value' => 'Test literal one.')), 
            array('o' => array('type' => 'literal', 'value' => 'Test literal two.', 'lang' => 'en')), 
            array('o' => array('type' => 'typed-literal', 'value' => 'Test literal two bee.', 'datatype' => 'http://www.w3.org/2001/XMLSchema#string')), 
            array('o' => array('type' => 'literal', 'value' => '1234', 'datatype' => 'http://www.w3.org/2001/XMLSchema#int')), 
            array('o' => array('type' => 'literal', 'value' => '123')), 
        )));
        
        $expected = array(
            'http://example.com/resource1' => array(
                'http://example.com/property1' => array(
                    array('value' => 'Test literal one.', 'type' => 'literal'), 
                ), 
                'http://example.com/property2' => array(
                    array('value' => 'Test literal two.', 'type' => 'literal', 'lang' => 'en'), 
                    array('value' => 'Test literal two bee.', 'type' => 'literal', 'datatype' => 'http://www.w3.org/2001/XMLSchema#string')
                ), 
            ), 
            'http://example.com/resource2' => array(
                'http://example.com/property3' => array(
                    array('value' => '1234', 'type' => 'literal', 'datatype' => 'http://www.w3.org/2001/XMLSchema#int'), 
                    array('value' => '123', 'type' => 'literal'), 
                )
            )
        );
        
        $mockModel = $this->getMockBuilder('Erfurt_Rdf_Model')
                          ->disableOriginalConstructor()
                          ->getMock();
        
        $mockModel->expects($this->any())
                  ->method('sparqlQuery')
                  ->will($this->returnValue($queryResult));
        $mockModel->expects($this->any())
                  ->method('__toString')
                  ->will($this->returnValue('http://example.com/graph1'));
        
        $actual = $this->_fixture->findStatementsForObjectsWithHashes($mockModel, $hashedResponse);
        
        $this->assertSame($expected, $actual);
    }
}

