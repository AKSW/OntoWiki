<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 *
 * @author Philipp Frischmuth <pfrischmuth@gmail.com>
 */
class OntoWiki_Model_TitleHelperIntegrationTest extends Erfurt_TestCase
{
    /** @var Erfurt_Store_Adapter_test */
    private $_storeAdapter = null;

    /** @var Erfurt_Store */
    private $_store = null;

    private $_erfurtApp = null;

    public function setUp()
    {
        $this->markTestNeedsTestConfig();
        $this->markTestNeedsDatabase();
        $this->_erfurtApp = Erfurt_App::getInstance();
        $this->_store = $this->_erfurtApp->getStore();
        $this->authenticateDbUser();
        $this->_modelUri = 'http://example.org/graph123/';
        $this->_addTestData();
        parent::setUp();
    }

    private function _addTestData()
    {
        $model = $this->_store->getNewModel($this->_modelUri, '', Erfurt_Store::MODEL_TYPE_OWL, false);
        $this->authenticateDbUser();
        $turtleString
            = '<http://purl.org/dc/terms/title> <http://www.w3.org/2000/01/rdf-schema#label> "testABC_en"@en .'
            . '<http://example.org/resourceXYZ> <http://www.w3.org/2004/02/skos/core#prefLabel> "testABC_noLang" ;'
            . '                                 <http://www.w3.org/2000/01/rdf-schema#label> "testABC_de"@de .'
            . '<http://example.org/graph123/resourceABC> <http://www.w3.org/2000/01/rdf-schema#label> "testABC" ;'
            . '                                          <http://ns.ontowiki.net/SysOnt/Site/menuLabel> "testMenuLabel" .';

        $this->_store->importRdf(
            $this->_modelUri,
            $turtleString,
            'turtle',
            Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING, false
        );
    }

    public function testMultipleAddResourceGetTitleCallsGithubIssue65()
    {
        $graph      = new Erfurt_Rdf_Model($this->_modelUri);
        $properties = array(
            'testABC_en@en'                                   => 'http://purl.org/dc/terms/title',
            'testABC_de@de'                                   => 'http://example.org/resourceXYZ',
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
            $resource      = $property;
            $lang          = null;
            $expectedTitle = $expected;
            if (strpos($expected, '@') !== false) {
                $parts         = explode('@', $expected);
                $expectedTitle = $parts[0];
                $lang          = $parts[1];
            }

            $titleHelper->addResource($resource);
            $title = $titleHelper->getTitle($property, $lang);
            $this->assertEquals($expectedTitle, $title);
        }
    }

    public function testPrependTitlePropertyDifferentInstances()
    {
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

        $graph    = new Erfurt_Rdf_Model($this->_modelUri);
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

        $graph    = new Erfurt_Rdf_Model('http://example.org/graph123/');
        $resource = 'http://example.org/graph123/resourceABC';

        $titleHelper = new OntoWiki_Model_TitleHelper($graph, $this->_store, $config);
        $title       = $titleHelper->getTitle($resource);
        $this->assertEquals('testABC', $title);

        // now prepend a property
        $titleHelper->prependTitleProperty('http://ns.ontowiki.net/SysOnt/Site/menuLabel');
        $title = $titleHelper->getTitle($resource);
        $this->assertEquals('testMenuLabel', $title);
    }
}
