<?php

/**
 *
 * @author jonas
 */
class InstancesIntegrationTest extends Erfurt_TestCase {
    /**
     *
     * @var OntoWiki_Model_Instances 
     */
    protected $_instances = null;
    
    /**
     *
     * @var Erfurt_Store 
     */
    protected $_store = null;

    public function setUp()
    {        
        $this->_store = $this->getStore();
        
        $this->_instances = new OntoWiki_Model_Instances(
            $this->_store, 
            new Erfurt_Rdf_Model('http://graph.com/123/', null, $this->_store)
        );
        
        $this->addTestData();
    }
    
    private $_class = 'http://model.org/model#className1';
    
    public function addTestData()
    {
        $this->markTestNeedsDatabase();
        $this->authenticateDbUser();
    
        $store = $this->getStore();
        $modelUri = 'http://example.org/test/';
        $turtleString = '<http://model.org/model#i1> a 
                            <'.$this->_class.'> ;
                            <http://www.w3.org/2000/01/rdf-schema#label> "intance1";
                            <http://model.org/prop> "val1", "val2" .
                        <http://model.org/model#i2> a 
                            <'.$this->_class.'> ;
                            <http://www.w3.org/2000/01/rdf-schema#label> "intance2" ;
                            <http://model.org/prop> "val3" .';
        
        $store->importRdf($modelUri, $turtleString, 'turtle', Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING, false);
    }
    
    public function getStore(){
        return Erfurt_App::getInstance()->getStore();
    }
        
    public function testResources(){
        echo "yay";
        $this->assertCount(2, $this->_instances->getResources());
    }        
    
    public function testTypeFilter(){
        $id = $this->_instances->addTypeFilter($this->_class);
        $this->assertCount(2, $this->_instances->getResources());
        $this->_instances->removeFilter($id);
        $this->_instances->addTypeFilter('http://other');
        $this->assertEmpty($this->_instances->getResources());
    }
}
?>
