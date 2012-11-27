<?php
/**
 * It tests the behavior of Ontowiki_Model_Instances
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class ManagerTest extends PHPUnit_Framework_TestCase {

    public function testTriple2ConfigArray()
    {
        $triples = array (
            'file:///home/jonas/programming/php-workspace/ow/extensions/account/' =>
            array (
                'http://xmlns.com/foaf/0.1/primaryTopic' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/account/raw/master/doap.n3#account',
                    ),
                ),
            ),
            'https://github.com/AKSW/account/raw/master/doap.n3#account' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://usefulinc.com/ns/doap#Project',
                    ),
                ),
                'http://usefulinc.com/ns/doap#name' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'account',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/privateNamespace' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/account/raw/master/doap.n3#',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/enabled' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Login Module',
                    ),
                ),
                'http://usefulinc.com/ns/doap#description' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'provides a login module and a recover action.',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/authorLabel' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'AKSW',
                    ),
                ),
                'http://usefulinc.com/ns/doap#maintainer' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://aksw.org',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/templates' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'templates',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/languages' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'languages',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/hasModule' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/account/raw/master/doap.n3#Default',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node1',
                    ),
                ),
                'http://usefulinc.com/ns/doap#release' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/account/raw/master/doap.n3#v1-0',
                    ),
                ),
            ),
            'https://github.com/AKSW/account/raw/master/doap.n3#Default' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Module',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Default',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/caching' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/priority' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '40',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/context' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'main.sidewindows',
                    ),
                ),
            ),
            '_:node1' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'allow',
                    ),
                ),
                'https://github.com/AKSW/account/raw/master/doap.n3#local' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/account/raw/master/doap.n3#webid' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/account/raw/master/doap.n3#openid' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            'https://github.com/AKSW/account/raw/master/doap.n3#v1-0' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://usefulinc.com/ns/doap#Version',
                    ),
                ),
                'http://usefulinc.com/ns/doap#revision' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '1.0',
                    ),
                ),
            ),
        );
        $base = 'file:///home/jonas/programming/php-workspace/ow/extensions/account/';
        $conf = OntoWiki_Extension_Manager::triples2configArray($triples , "test", $base, "file:///tmp/test");

        $this->assertEquals('account', $conf['name']);
        $this->assertEquals('Login Module', $conf['title']);
        $this->assertTrue($conf['private']['allow']['openid']);
        $this->assertFalse($conf['private']['allow']['webid']);
    }


    public function testTriple2ConfigArray2()
    {
        $triples = array (
            'file:///home/jonas/programming/php-workspace/ow/extensions/navigation/' =>
            array (
                'http://xmlns.com/foaf/0.1/primaryTopic' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/navigation/raw/master/doap.n3#navigation',
                    ),
                ),
            ),
            'https://github.com/AKSW/navigation/raw/master/doap.n3#navigation' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://usefulinc.com/ns/doap#Project',
                    ),
                ),
                'http://usefulinc.com/ns/doap#name' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'navigation',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/privateNamespace' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/navigation/raw/master/doap.n3#',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/enabled' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Navigation Module',
                    ),
                ),
                'http://usefulinc.com/ns/doap#description' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'an extensible and highly customizable module to navigate in knowledge bases via tree-based information (e.g. classes)',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/authorLabel' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'AKSW',
                    ),
                ),
                'http://usefulinc.com/ns/doap#maintainer' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://aksw.org',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/templates' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'templates',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/languages' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'languages',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/hasModule' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/navigation/raw/master/doap.n3#Default',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node1',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node2',
                    ),
                    2 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node4',
                    ),
                ),
                'http://usefulinc.com/ns/doap#release' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'https://github.com/AKSW/navigation/raw/master/doap.n3#v1-0',
                    ),
                ),
            ),
            'https://github.com/AKSW/navigation/raw/master/doap.n3#Default' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Module',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Default',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/context' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'main.sidewindows',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/priority' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '30',
                    ),
                ),
            ),
            '_:node1' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'defaults',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#config' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'classes',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#limit' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '10',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showMenu' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node2' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'sorting',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node3',
                    ),
                ),
            ),
            '_:node3' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'label',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'By Label',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    ),
                ),
            ),
            '_:node4' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node5',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node9',
                    ),
                    2 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node13',
                    ),
                    3 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node16',
                    ),
                    4 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node19',
                    ),
                    5 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node22',
                    ),
                    6 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node25',
                    ),
                    7 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node28',
                    ),
                ),
            ),
            '_:node5' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'classes',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Classes',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#cache' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkVisibility' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2002/07/owl#Class',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2000/01/rdf-schema#Class',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node6',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node7',
                    ),
                    2 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node8',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hiddenNS' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2000/01/rdf-schema#',
                    ),
                    2 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2002/07/owl#',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hiddenRelation' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/hidden',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showImplicitElements' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showEmptyElements' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showCounts' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkSub' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hideDefaultHierarchy' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node6' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2000/01/rdf-schema#subClassOf',
                    ),
                ),
            ),
            '_:node7' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'instanceRelation',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    ),
                ),
            ),
            '_:node8' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'list',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#config' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '{|filter|:[{|rdfsclass|:|%resource%|,|mode|:|rdfsclass|}]}',
                    ),
                ),
            ),
            '_:node9' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'properties',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Properties',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2002/07/owl#DatatypeProperty',
                    ),
                    2 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2002/07/owl#ObjectProperty',
                    ),
                    3 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2002/07/owl#AnnotationProperty',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node10',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node11',
                    ),
                    2 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node12',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showImplicitElements' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showEmptyElements' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showCounts' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hideDefaultHierarchy' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkSub' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node10' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf',
                    ),
                ),
            ),
            '_:node11' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'instanceRelation',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf',
                    ),
                ),
            ),
            '_:node12' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'list',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#config' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '{|shownProperties|:[{|uri|:|%resource%|,|label|:|Label 1|,|action|:|add|,|inverse|:false}],|filter|:[{|property|:|%resource%|,|filter|:|bound|}]}',
                    ),
                ),
            ),
            '_:node13' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'spatial',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Spatial',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/SpatialArea',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/Planet',
                    ),
                    2 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/Continent',
                    ),
                    3 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/Country',
                    ),
                    4 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/Province',
                    ),
                    5 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/District',
                    ),
                    6 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/City',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node14',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node15',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
            ),
            '_:node14' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/spatialHierarchy/isLocatedIn',
                    ),
                ),
            ),
            '_:node15' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'instanceRelation',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/addressFeatures/physical/country',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.aksw.org/addressFeatures/physical/city',
                    ),
                ),
            ),
            '_:node16' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'faun',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Faunistics',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#Family',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#Genus',
                    ),
                    2 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#Species',
                    ),
                    3 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#Order',
                    ),
                    4 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#SubOrder',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node17',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node18',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkSub' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node17' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#subTaxonOf',
                    ),
                ),
            ),
            '_:node18' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'instanceRelation',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://purl.org/net/faunistics#identifiesAs',
                    ),
                ),
            ),
            '_:node19' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'skos',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'SKOS',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2004/02/skos/core#Concept',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node20',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node21',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showCounts' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node20' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2004/02/skos/core#broader',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2004/02/skos/core#narrower',
                    ),
                ),
            ),
            '_:node21' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'instanceRelation',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2004/02/skos/core#narrower',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.w3.org/2004/02/skos/core#broader',
                    ),
                ),
            ),
            '_:node22' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'org',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Groups',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://xmlns.com/foaf/0.1/Group',
                    ),
                    1 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://xmlns.com/foaf/0.1/Organization',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node23',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node24',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showCounts' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node23' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/subGroup',
                    ),
                ),
            ),
            '_:node24' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'instanceRelation',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://xmlns.com/foaf/0.1/member',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#out' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://xmlns.com/foaf/0.1/member_of',
                    ),
                ),
            ),
            '_:node25' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'go',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Gene Ontology',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.geneontology.org/dtds/go.dtd#term',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node26',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node27',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#showCounts' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkSub' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hideDefaultHierarchy' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'false',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
            ),
            '_:node26' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.geneontology.org/dtds/go.dtd#is_a',
                    ),
                ),
            ),
            '_:node27' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'list',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#query' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'SELECT DISTINCT ?resourceUri WHERE { ?resourceUri <http://www.geneontology.org/GO.format.gaf-2_0.shtml#go_id> <%resource%> }',
                    ),
                ),
            ),
            '_:node28' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'checklist',
                    ),
                ),
                'http://www.w3.org/2000/01/rdf-schema#label' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Checklist',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#titleMode' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'titleHelper',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#hierarchyTypes' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.mindswap.org/2003/owl/geo/geoFeatures.owl#Country',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/config' =>
                array (
                    0 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node29',
                    ),
                    1 =>
                    array (
                        'type' => 'bnode',
                        'value' => '_:node30',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#checkSub' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'true',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#rootName' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'Caucasus Spiders',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#rootURI' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://db.caucasus-spiders.info/Area/152',
                    ),
                ),
            ),
            '_:node29' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'hierarchyRelations',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#in' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://www.mindswap.org/2003/owl/geo/geoFeatures.owl#within',
                    ),
                ),
            ),
            '_:node30' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/Config',
                    ),
                ),
                'http://ns.ontowiki.net/SysOnt/ExtensionConfig/id' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'list',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#shownProperties' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '{|uri|:|http://purl.org/net/faunistics#citationSuffix|,|label|:|citation suffix|,|action|:|add|,|inverse|:false}',
                    ),
                ),
                'https://github.com/AKSW/navigation/raw/master/doap.n3#query' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => 'SELECT DISTINCT ?resourceUri ?famUri WHERE {     ?recUri <http://purl.org/net/faunistics#recordedAtLocation> ?resourceLocation     OPTIONAL{         ?resourceLocation <http://www.mindswap.org/2003/owl/geo/geoFeatures.owl#within> ?l1.         OPTIONAL{             ?l1 <http://www.mindswap.org/2003/owl/geo/geoFeatures.owl#within> ?l2.             OPTIONAL{                 ?l2 <http://www.mindswap.org/2003/owl/geo/geoFeatures.owl#within> ?l3.             }         }     }     ?recUri <http://purl.org/net/faunistics#identifiesAs> ?resourceUri .     ?resourceUri <http://purl.org/net/faunistics#subTaxonOf> ?genUri .     ?genUri <http://purl.org/net/faunistics#subTaxonOf> ?famUri .     FILTER ( sameTerm(?resourceLocation, <%resource%>) ||     sameTerm(?l1, <%resource%>) ||     sameTerm(?l2, <%resource%>) ||     sameTerm(?l3, <%resource%>)) }',
                    ),
                ),
            ),
            'https://github.com/AKSW/navigation/raw/master/doap.n3#v1-0' =>
            array (
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' =>
                array (
                    0 =>
                    array (
                        'type' => 'uri',
                        'value' => 'http://usefulinc.com/ns/doap#Version',
                    ),
                ),
                'http://usefulinc.com/ns/doap#revision' =>
                array (
                    0 =>
                    array (
                        'type' => 'literal',
                        'value' => '1.0',
                    ),
                ),
            ),
        );
        $base = 'file:///home/jonas/programming/php-workspace/ow/extensions/navigation/';
        $conf = OntoWiki_Extension_Manager::triples2configArray($triples , "navigation", $base, "file:///tmp/test");
        //var_dump($conf);
        $this->assertArrayHasKey('private', $conf);
        //TODO more assertions
    }
}
?>
