<?php
/**
 * It tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class OntoWiki_Model_InstancesTest extends Erfurt_TestCase 
{
    /**
     *
     * @var OntoWiki_Model_Instances 
     */
    protected $_instances = null;

    /**
     *
     * @var Erfurt_Store_Adapter 
     */
    protected $_storeAdapter = null;
    
    /**
     *
     * @var Erfurt_Store 
     */
    protected $_store = null;

    public function setUp()
    {
        $this->markTestNeedsTestConfig();

        $this->_storeAdapter = new Erfurt_Store_Adapter_Test();

        $this->_store = new Erfurt_Store(
            array(
                'adapterInstance' => $this->_storeAdapter
            ), 
            'Test'
        );

        $this->_instances = new OntoWiki_Model_Instances(
            $this->_store, 
            new Erfurt_Rdf_Model('http://graph.com/123/', null, $this->_store)
        );

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
    }
    
    
    public function testSetStore()
    {
        $adapter = new Erfurt_Store_Adapter_Test();
        $store = new Erfurt_Store(array('adapterInstance'=>$adapter), 'Test');
        $this->assertNotSame($this->_instances->getStore(), $store);
        $this->_instances->setStore($store);
        $this->assertSame($this->_instances->getStore(), $store);
        
        try {
            $this->_instances->setStore(null);
            $this->fail("No Exception was thrown in setStore(null)");
        } catch (Exception $e) {
            $this->assertTrue(true); //increase assertion count :)
        }    
    }
    
    public function testAllTriple()
    {
        $this->assertTrue(in_array($this->_instances->getAllTriple(), $this->_instances->getResourceQuery()->getWhere()->getElements()));
        $this->_instances->removeAllTriple();
        $this->assertTrue(!in_array($this->_instances->getAllTriple(), $this->_instances->getResourceQuery()->getWhere()->getElements()));
        $this->_instances->addAllTriple();
        $this->assertTrue(in_array($this->_instances->getAllTriple(), $this->_instances->getResourceQuery()->getWhere()->getElements()));
    }
    
    public function testGetQuery()
    {
        $this->assertInstanceOf('Erfurt_Sparql_Query2', $this->_instances->getQuery());
    }
    
    public function testGetResourceVar()
    {
        $this->assertInstanceOf('Erfurt_Sparql_Query2_Var', $this->_instances->getResourceVar());
    }
    
    public function testOffsetLimit()
    {
        //default values
        $this->assertEquals(10, $this->_instances->getLimit());
        $this->assertEquals(0, $this->_instances->getOffset());
        //test setting
        $this->_instances->setOffset(1);
        $this->_instances->setLimit(1);
        $this->assertEquals(1, $this->_instances->getLimit());
        $this->assertEquals(1, $this->_instances->getOffset());
        //test repeated set
        $this->_instances->setOffset(1);
        $this->_instances->setLimit(1);
        //test negative set (minus interpreted as plus)
        $this->_instances->setOffset(-1);
        $this->_instances->setLimit(-1);
        $this->assertEquals(1, $this->_instances->getLimit());
        $this->assertEquals(1, $this->_instances->getOffset());
    }
        
    public function testNoCache()
    {
        $instances = new OntoWiki_Model_Instances(
            $this->_store, 
            new Erfurt_Rdf_Model('http://graph.com/123/', null, $this->_store),
            array(Erfurt_Store::USE_CACHE => false)
        );
        $this->assertInstanceOf('OntoWiki_Model_Instances', $instances);
    }
    
        
    public function testSerialize(){
        $vq1 = $this->_instances->getQuery()->getSparql();
        $rq1 = $this->_instances->getResourceQuery()->getSparql();
        $q = unserialize(serialize($this->_instances));
        $vq2 = $this->_instances->getQuery()->getSparql();
        $rq2 = $this->_instances->getResourceQuery()->getSparql();
        $this->assertEquals($vq1, $vq2);
        $this->assertEquals($rq1, $rq2);
        
        $c = clone $this->_instances;
        $vq2 = $c->getQuery()->getSparql();
        $rq2 = $c->getResourceQuery()->getSparql();
        $this->assertEquals($vq1, $vq2);
        $this->assertEquals($rq1, $rq2);
        $this->assertNotSame($this->_instances, $c);
    }
    
    public function testCallMagic(){
        //redirect from methods to both query objects
        $from = new Erfurt_Sparql_Query2_GraphClause(
            new Erfurt_Sparql_Query2_IriRef("http://abc")
        );
        $this->_instances->addFrom($from);
        $this->assertContains($from, $this->_instances->getFroms()); //redirected to resource query
        $this->assertContains($from, $this->_instances->getQuery()->getFroms());
        
        //check undefined exception
        try {
             $r = $this->_instances->undef();
             $this->fail("no exception when calling undefined method on instances object. see __call");
        } catch (Exception $exc) {
            $this->assertTrue(true); //increase assertion count :)
        }
    }
    
    /**
     * number of triples that are in an "empty" value query
     * @var int 
     */
    private $_valueQueryDefaultTriples = 3;
    
    public function testShownPropertiesEmpty()
    {
        //test default state
        $this->assertNoShownProperties($this->_instances);
    }
    
    public function testShownPropertiesAdd()
    {
        //add
        $r = $this->_instances->addShownProperty("http://abc");
        //test chaining
        $this->assertSame($this->_instances, $r);
        return $this->_instances;
    }
    
    /**
     * 
     * @depends testShownPropertiesAdd
     */
    public function testShownPropertiesAddPlain(OntoWiki_Model_Instances $i)
    {
        $psDef = $i->getShownPropertiesPlain();
        $this->assertCount(2, $psDef);
    }
    
    /**
     * 
     * @depends testShownPropertiesAdd
     */
    public function testShownPropertiesAddTriple(OntoWiki_Model_Instances $i)
    {
        //verify two triple in value query, alltriple (?resourceuri ?p ?o), filter(isuri) and the optional triple
        $triples = $i->getValueQuery()->getElements();
        $this->assertCount(1 + $this->_valueQueryDefaultTriples, $triples);
    }
    /**
     * 
     */
    public function testShownPropertiesCustomAddTriple()
    {
        $var = new Erfurt_Sparql_Query2_Var("sp");
        $triples = array(
            new Erfurt_Sparql_Query2_Triple(
                $this->_instances->getResourceVar(), 
                new Erfurt_Sparql_Query2_IriRef('http://ex.com/'),
                $var
            )
        );
        $r = $this->_instances->addShownPropertyCustom($triples, $var);
        //verify two triple in value query, alltriple (?resourceuri ?p ?o), filter(isuri) and the optional triple
        $triplesQuery = $this->_instances->getValueQuery()->getElements();

        $this->assertCount(1 + $this->_valueQueryDefaultTriples, $triplesQuery);
    }
    
    /**
     * 
     * @depends testShownPropertiesAdd
     */
    public function testShownPropertiesAddTitles(OntoWiki_Model_Instances $i)
    {
        //verify triple in value query
        $propertiesWithTitles = $i->getShownProperties();
        $this->assertCount(2, $propertiesWithTitles);
//        
//        $this->_shownProperties[$propertyUri.'-'.($inverse?'inverse':'direct')] = array(
//            'uri' => $propertyUri,
//            'name' => $propertyName,
//            'inverse' => $inverse,
//            'datatype' => $datatype,
//            'varName' => $ret['var']->getName(),
//            'var' => $ret['var'],
//            'optionalpart' => $ret['optional'],
//            'filter' => $ret['filter'],
//            'hidden' => $hidden
//        );
    }
    
    public function testtShownPropertiesRemove()
    {
        //add
        $p = "http://abc";
        $this->_instances->addShownProperty($p); 
        //result of add is checked in testShownPropertiesAdd1
        $r1 = $this->_instances->removeShownProperty($p, false);
        $this->assertNoShownProperties($this->_instances);
        $this->assertTrue($r1);
        //remove non existing
        $r2 = $this->_instances->removeShownProperty($p, false);
        $this->assertFalse($r2);
    }
    
    private function assertNoShownProperties(OntoWiki_Model_Instances $i){
        //only __TYPE
        $psDef = $i->getShownPropertiesPlain();
        $this->assertCount(1, $psDef);
        $triples = $i->getValueQuery()->getElements();
        $this->assertCount($this->_valueQueryDefaultTriples, $triples);
    }
    
    public function testFilterEmpty()
    {
        $fDef = $this->_instances->getFilter();
        $this->assertEmpty($fDef);
    }
    
    public function testFilterAddBound()
    {
        //test add
        $this->_instances->addFilter("http://abc", false, "abc", "bound");
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddContains()
    {
        $this->_instances->addFilter("http://abc", false, "abc", "contains", "xyz");
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddEquals()
    {
        $this->_instances->addFilter("http://abc", false, "abc", "equals", "xyz");
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddLarger()
    {
        $this->_instances->addFilter("http://abc", false, "abc", "larger", 4);
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddSmaller()
    {
        $this->_instances->addFilter("http://abc", false, "abc", "smaller", 4);
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddBetween()
    {
        $this->_instances->addFilter("http://abc", false, "abc", "between", 5, 6);
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddType()
    {
        $this->_instances->addTypeFilter("http://class.com");
        $this->assertCount(1, $this->_instances->getFilter());
    }
    public function testFilterAddSearch()
    {
        $this->_instances->addSearchFilter("term");
        $this->assertCount(1, $this->_instances->getFilter());
    }
    public function testFilterAddTriples()
    {
        $triples = array(
            new Erfurt_Sparql_Query2_Triple(
                $this->_instances->getResourceVar(), 
                new Erfurt_Sparql_Query2_IriRef('http://ex.com/'),
                new Erfurt_Sparql_Query2_Var("sp")
            )
        );
        $this->_instances->addTripleFilter($triples);
        //verify two triple in value query, alltriple (?resourceuri ?p ?o), filter(isuri) and the optional triple
        $triplesQuery = $this->_instances->getResourceQuery()->getElements();
        $this->assertContains($triples[0], $triplesQuery);
        $this->assertCount(1, $this->_instances->getFilter());
    }
    
    public function testFilterAddUndef()
    {
        //text unknown filter type
        try {
             $this->_instances->addFilter("http://abc", false, "abc", "undef");
             $this->fail("no exception when calling filter method with unsupported filter type.");
        } catch (Exception $exc) {
            $this->assertTrue(true); //increase assertion count :)
        }
        
        //verify triple in value query
    }
    
    public function testFilterRemove()
    {
        //add
        $id = $this->_instances->addFilter("http://abc", false, "abc", "bound");
        $fDef = $this->_instances->getFilter();
        $this->assertCount(1, $fDef);
        $this->_instances->removeFilter($id);
        $fDefAfter = $this->_instances->getFilter();
        $this->assertEmpty($fDefAfter);
    }
    
    public function testSetTitleHelper()
    {
        $newTH = new OntoWiki_Model_TitleHelper(null, $this->_store);
        $this->_instances->setTitleHelper($newTH);
        $this->assertSame($newTH, $this->_instances->getTitleHelper());
    }
    
    public function testGetProperties()
    {
        $p = $this->_instances->getAllProperties();
        $this->assertEmpty($p); //on a stub store, there should be no results
    }
    
    public function testGetPropertiesQuery()
    {
        $q = $this->_instances->getAllPropertiesQuery();
        $this->assertInstanceOf('Erfurt_Sparql_Query2', $q);
    }
    
    public function testGetValues()
    {
        $v = $this->_instances->getValues();
        $this->assertEmpty($v); //on a stub store, there should be no results
    }
    
    public function testResults()
    {
        $r = $this->_instances->getResults();
        $this->assertArrayHasKey('results', $r);
        $this->assertArrayHasKey('bindings', $r['results']);
        $this->assertEmpty($r['results']['bindings']); //on a stub store, there should be no results
    }
    
    public function testPossibleValues()
    {
        $v = $this->_instances->getPossibleValues("http://abc");
        $this->assertEmpty($v); //on a stub store, there should be no results
    }
    
    
}
 