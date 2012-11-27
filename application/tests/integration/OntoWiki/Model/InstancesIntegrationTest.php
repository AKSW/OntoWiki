<?php
/**
 * It tests the behavior of Ontowiki_Model_Instances
 * while using the database backend
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class OntoWiki_Model_InstancesIntegrationTest extends Erfurt_TestCase {
    /**
     *
     * @var OntoWiki_Model_Instances 
     */
    protected $_instances = null;

    protected $_modelUri = 'http://example.org/test/';

    /**
     *
     * @var Erfurt_Store 
     */
    protected $_store = null;

    public function setUp()
    {
        $this->markTestNeedsDatabase();
        $this->_store = $this->getStore();
        $this->authenticateDbUser();

        //create model
        $model = $this->_store->getNewModel($this->_modelUri, '', Erfurt_Store::MODEL_TYPE_OWL, false);

        $this->_instances = new OntoWiki_Model_Instances(
            $this->_store,
            new Erfurt_Rdf_Model($this->_modelUri, null, $this->_store)
        );

        $this->addTestData();

        parent::setUp();
    }

    private $_class = 'http://model.org/model#className1';

    public function addTestData()
    {
        $this->authenticateDbUser();
        $turtleString = '<http://model.org/model#i1> a
                            <'.$this->_class.'> ;
                            <http://www.w3.org/2000/01/rdf-schema#label> "instance1";
                            <http://model.org/prop> "val1", "val2" .
                        <http://model.org/model#i2> a
                            <'.$this->_class.'> ;
                            <http://www.w3.org/2000/01/rdf-schema#label> "instance2" ;
                            <http://model.org/prop> "val3" .';

        $this->_store->importRdf($this->_modelUri, $turtleString, 'turtle', Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING, false);
    }

    public function getStore()
    {
        return Erfurt_App::getInstance()->getStore();
    }

    /**
     * @medium
     */
    public function testResources()
    {
        //two instances and a triple for the graph
        $this->assertCount(3, $this->_instances->getResources());
    }

    /**
     * @medium
     */
    public function testTypeFilter()
    {
        $id = $this->_instances->addTypeFilter($this->_class);
        $this->assertCount(2, $this->_instances->getResources());
        $this->_instances->removeFilter($id);
        $this->_instances->addTypeFilter('http://other');
        $this->assertEmpty($this->_instances->getResources());
    }

    /**
     * @medium
     */
    public function testGetValues()
    {
        $v = $this->_instances->getValues();
        $this->assertCount(3, $v);
        $this->assertArrayHasKey('http://model.org/model#i1', $v);
        $this->assertArrayHasKey('http://model.org/model#i2', $v);

        // the __TYPE and resourceUri
        $this->assertCount(2, $v['http://model.org/model#i1']);
        $this->assertArrayHasKey('__TYPE', $v['http://model.org/model#i1']);
        $this->assertArrayHasKey('resourceUri', $v['http://model.org/model#i1']);
        $this->assertContains($this->_class, self::_onlyValues($v['http://model.org/model#i1']['__TYPE']));
        $this->assertContains('http://model.org/model#i1', self::_onlyValues($v['http://model.org/model#i1']['resourceUri']));
    }

    /**
     * @medium
     */
    public function testAddShownProperties()
    {
        //add properties
        $this->_instances->addShownProperty('http://model.org/prop');
        $v = $this->_instances->getValues();
        //count values
        $this->assertCount(3, $v['http://model.org/model#i1']);
        //test variable creation
        $this->assertArrayHasKey('prop', $v['http://model.org/model#i1']);

        //test values
        $this->assertCount(2, $v['http://model.org/model#i1']['prop']);
        $this->assertContains("val1", self::_onlyValues($v['http://model.org/model#i1']['prop']));
        $this->assertContains("val2", self::_onlyValues($v['http://model.org/model#i1']['prop']));

        //another one
        $this->_instances->addShownProperty('http://www.w3.org/2000/01/rdf-schema#label');
        $v = $this->_instances->getValues();
        $this->assertCount(4, $v['http://model.org/model#i1']);
        $this->assertArrayHasKey('label', $v['http://model.org/model#i1']);
    }

    /**
     * @medium
     */
    public function testGetProperties()
    {
        $p = $this->_instances->getAllProperties();
        $this->assertCount(3, $p);
        $ovp = self::_onlyValues($p);
        $this->assertContains("http://www.w3.org/1999/02/22-rdf-syntax-ns#type", $ovp);
        $this->assertContains("http://model.org/prop", $ovp);
        $this->assertContains("http://www.w3.org/2000/01/rdf-schema#label", $ovp);
    }

    /**
     * @medium
     */
    public function testGetPossibleValues()
    {
        $v = $this->_instances->getPossibleValues("http://model.org/prop");
        $this->assertCount(3, $v);
        $ovv = self::_onlyValues($v);
        $this->assertContains("val1", $ovv);
        $this->assertContains("val2", $ovv);
        $this->assertContains("val3", $ovv);
    }

    /**
     * @medium
     */
    public function testAddFilter()
    {
        $id = $this->_instances->addFilter("http://model.org/prop", false, 'prop', 'equals', 'val1', null, 'literal');
        $this->assertCount(1, $this->_instances->getResources());
        $this->_instances->removeFilter($id);
        $this->assertCount(3, $this->_instances->getResources());
    }

    /**
     * @medium
     */
    public function testLimitOffset()
    {
        $this->_instances->addTypeFilter($this->_class);
        $this->assertCount(2, $this->_instances->getResources());
        $this->_instances->setLimit(1);
        $this->assertCount(1, $this->_instances->getResources());
        $this->_instances->setLimit(0);
        $this->assertCount(2, $this->_instances->getResources());
        $this->_instances->setOffset(1);
        $this->assertCount(1, $this->_instances->getResources());
    }

    /**
     * @medium
     */
    public function testOrderProperty()
    {
        $this->_instances->addTypeFilter($this->_class);
        $this->assertCount(2, $this->_instances->getResources());
        $this->_instances->setOrderProperty("http://model.org/prop", true);
        $r = $this->_instances->getResources();
        $this->assertEquals($r[0]['uri'], 'http://model.org/model#i1');
        $this->_instances->setOrderProperty("http://model.org/prop", false);
        $r = $this->_instances->getResources();
        $this->assertEquals($r[0]['uri'], 'http://model.org/model#i2');
    }

    /**
     * @medium
     */
    public function testOrderURI()
    {
        $this->_instances->addTypeFilter($this->_class);
        $this->assertCount(2, $this->_instances->getResources());
        $this->_instances->orderByUri(true);
        $r = $this->_instances->getResources();
        //var_dump($r);
        $this->assertEquals($r[0]['uri'], 'http://model.org/model#i1');
        $this->_instances->orderByUri(false);
        $r = $this->_instances->getResources();
        $this->assertEquals($r[0]['uri'], 'http://model.org/model#i2');
    }

    protected static function _onlyValues($arr)
    {
        $r = array();
        foreach($arr as $a){
            if(isset($a['origvalue'])){
                $r[] = $a['origvalue'];
            } else if(isset($a['uri'])){
                $r[] = $a['uri'];
            } else {
                $r[] = $a['value'];
            }
        }
        return $r;
    }
}
?>
