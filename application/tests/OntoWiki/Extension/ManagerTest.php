<?php
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))). DIRECTORY_SEPARATOR."libraries/Erfurt/library/Erfurt/include/vocabulary.php";
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
    }
}
?>
