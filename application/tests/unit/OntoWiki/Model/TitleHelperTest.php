<?php
    /**
     *
     * @author Philipp Frischmuth <pfrischmuth@gmail.com>
     */
class OntoWiki_Model_TitleHelperTest extends Erfurt_TestCase
{
    /** @var Erfurt_Store_Adapter_test */
    private $_storeAdapter = null;

    /** @var Erfurt_Store */
    private  $_store = null;

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
    }

    public function testMultipleAddResourceGetTitleCallsGithubIssue65()
    {
        $queryResult1 = array(
            'head' => array(
                'vars' => array(
                    'property',
                    'value'
                )
            ),
            'results' => array(
                'bindings' => array(
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2000/01/rdf-schema#label',
                        ),
                        'value' => array(
                            'value'    => 'testABC_en',
                            'xml:lang' => 'en'
                        )
                    )
                )
            )
        );
        $this->_storeAdapter->addQueryResult($queryResult1);

        $queryResult2 = array(
            'head' => array(
                'vars' => array(
                    'property',
                    'value'
                )
            ),
            'results' => array(
                'bindings' => array(
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2004/02/skos/core#prefLabel',
                        ),
                        'value' => array(
                            'value'    => 'testABC_noLang'
                        )
                    ),
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2000/01/rdf-schema#label',
                        ),
                        'value' => array(
                            'value'    => 'testABC_de',
                            'xml:lang' => 'de'
                        )
                    )
                )
            )
        );
        $this->_storeAdapter->addQueryResult($queryResult2);

        $graph = new Erfurt_Rdf_Model('http://example.org/graph123/');
        $properties = array(
            'testABC_en@en' => 'http://purl.org/dc/terms/title',
            'testABC_de@de' => 'http://example.org/resourceXYZ',
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
        );

        $config = array(
            'titleHelper' => array(
                'properties' => array(
                    'skosPlabel' => 'http://www.w3.org/2004/02/skos/core#prefLabel',
                    'dcTitle'    => 'http://purl.org/dc/elements/1.1/title',
                    'dcTitle2'   => 'http://purl.org/dc/terms/title',
                    'swrcTitle'  => 'http://swrc.ontoware.org/ontology#title',
                    'foafName'   => 'http://xmlns.com/foaf/0.1/name',
                    'doapName'   => 'http://usefulinc.com/ns/doap#name',
                    'siocName'   => 'http://rdfs.org/sioc/ns#name',
                    'tagName'    => 'http://www.holygoat.co.uk/owl/redwood/0.1/tags/name',
                    'lgeodName'  => 'http://linkedgeodata.org/vocabulary#name',
                    'geoName'    => 'http://www.geonames.org/ontology#name',
                    'goName'     => 'http://www.geneontology.org/dtds/go.dtd#name',
                    'rdfsLabel'  => 'http://www.w3.org/2000/01/rdf-schema#label'
                ),
                'searchMode' => 'language'
            )
        );

        $titleHelper = new OntoWiki_Model_TitleHelper($graph, $this->_store, $config);
        foreach ($properties as $expected => $property) {
            $resource = $property;
            $lang = null;
            $expectedTitle = $expected;
            if (strpos($expected, '@') !== false) {
                $parts = explode('@', $expected);
                $expectedTitle = $parts[0];
                $lang = $parts[1];
            }

            $titleHelper->addResource($resource);
            $title = $titleHelper->getTitle($property, $lang);
            $this->assertEquals($expectedTitle, $title);
        }
    }

    public function testPrependTitlePropertyDifferentInstances()
    {
        $queryResult1 = array(
            'head' => array(
                'vars' => array(
                    'property',
                    'value'
                )
            ),
            'results' => array(
                'bindings' => array(
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2000/01/rdf-schema#label'
                        ),
                        'value' => array(
                            'value'    => 'testABC'
                        )
                    )
                )
            )
        );
        $this->_storeAdapter->addQueryResult($queryResult1);

        $queryResult2 = array(
            'head' => array(
                'vars' => array(
                    'property',
                    'value'
                )
            ),
            'results' => array(
                'bindings' => array(
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2000/01/rdf-schema#label'
                        ),
                        'value' => array(
                            'value'    => 'test123'
                        )
                    ),
                    array(
                        'property' => array(
                            'value' => 'http://ns.ontowiki.net/SysOnt/Site/menuLabel'
                        ),
                        'value' => array(
                            'value'    => 'testMenuLabel'
                        )
                    )

                )
            )
        );
        $this->_storeAdapter->addQueryResult($queryResult2);

        $config = array(
            'titleHelper' => array(
                'properties' => array(
                    'skosPlabel' => 'http://www.w3.org/2004/02/skos/core#prefLabel',
                    'dcTitle'    => 'http://purl.org/dc/elements/1.1/title',
                    'dcTitle2'   => 'http://purl.org/dc/terms/title',
                    'swrcTitle'  => 'http://swrc.ontoware.org/ontology#title',
                    'foafName'   => 'http://xmlns.com/foaf/0.1/name',
                    'doapName'   => 'http://usefulinc.com/ns/doap#name',
                    'siocName'   => 'http://rdfs.org/sioc/ns#name',
                    'tagName'    => 'http://www.holygoat.co.uk/owl/redwood/0.1/tags/name',
                    'lgeodName'  => 'http://linkedgeodata.org/vocabulary#name',
                    'geoName'    => 'http://www.geonames.org/ontology#name',
                    'goName'     => 'http://www.geneontology.org/dtds/go.dtd#name',
                    'rdfsLabel'  => 'http://www.w3.org/2000/01/rdf-schema#label'
                ),
                'searchMode' => 'language'
            )
        );

        $graph = new Erfurt_Rdf_Model('http://example.org/graph123/');
        $resource = 'http://example.org/graph123/resourceABC';

        $titleHelper = new OntoWiki_Model_TitleHelper($graph, $this->_store, $config);
        $titleHelper->addResource($resource);
        $title = $titleHelper->getTitle($resource);
        $this->assertEquals('testABC', $title);

        // now prepend a property
        $titleHelper = new OntoWiki_Model_TitleHelper($graph, $this->_store, $config);
        $titleHelper->prependTitleProperty('http://ns.ontowiki.net/SysOnt/Site/menuLabel');
        $titleHelper->addResource($resource);
        $title = $titleHelper->getTitle($resource);
        $this->assertEquals('testMenuLabel', $title);
    }

    public function testPrependTitlePropertySameInstances()
    {
        $queryResult1 = array(
            'head' => array(
                'vars' => array(
                    'property',
                    'value'
                )
            ),
            'results' => array(
                'bindings' => array(
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2000/01/rdf-schema#label'
                        ),
                        'value' => array(
                            'value'    => 'testABC'
                        )
                    )
                )
            )
        );
        $this->_storeAdapter->addQueryResult($queryResult1);

        $queryResult2 = array(
            'head' => array(
                'vars' => array(
                    'property',
                    'value'
                )
            ),
            'results' => array(
                'bindings' => array(
                    array(
                        'property' => array(
                            'value' => 'http://www.w3.org/2000/01/rdf-schema#label'
                        ),
                        'value' => array(
                            'value'    => 'test123'
                        )
                    ),
                    array(
                        'property' => array(
                            'value' => 'http://ns.ontowiki.net/SysOnt/Site/menuLabel'
                        ),
                        'value' => array(
                            'value'    => 'testMenuLabel'
                        )
                    )

                )
            )
        );
        $this->_storeAdapter->addQueryResult($queryResult2);

        $config = array(
            'titleHelper' => array(
                'properties' => array(
                    'skosPlabel' => 'http://www.w3.org/2004/02/skos/core#prefLabel',
                    'dcTitle'    => 'http://purl.org/dc/elements/1.1/title',
                    'dcTitle2'   => 'http://purl.org/dc/terms/title',
                    'swrcTitle'  => 'http://swrc.ontoware.org/ontology#title',
                    'foafName'   => 'http://xmlns.com/foaf/0.1/name',
                    'doapName'   => 'http://usefulinc.com/ns/doap#name',
                    'siocName'   => 'http://rdfs.org/sioc/ns#name',
                    'tagName'    => 'http://www.holygoat.co.uk/owl/redwood/0.1/tags/name',
                    'lgeodName'  => 'http://linkedgeodata.org/vocabulary#name',
                    'geoName'    => 'http://www.geonames.org/ontology#name',
                    'goName'     => 'http://www.geneontology.org/dtds/go.dtd#name',
                    'rdfsLabel'  => 'http://www.w3.org/2000/01/rdf-schema#label'
                ),
                'searchMode' => 'language'
            )
        );

        $graph = new Erfurt_Rdf_Model('http://example.org/graph123/');
        $resource = 'http://example.org/graph123/resourceABC';

        $titleHelper = new OntoWiki_Model_TitleHelper($graph, $this->_store, $config);
        $title = $titleHelper->getTitle($resource);
        $this->assertEquals('testABC', $title);

        // now prepend a property
        $titleHelper->prependTitleProperty('http://ns.ontowiki.net/SysOnt/Site/menuLabel');
        $title = $titleHelper->getTitle($resource);
        $this->assertEquals('testMenuLabel', $title);
    }
}
