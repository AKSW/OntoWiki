<?php
/**
 * DSSN Utils
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Utils
{

    /*
     * takes an ARC2 index / rdfphp array and adds a triple based on the result
     * of an extended SPARQL query
     */
    static public function indexAddTripleFromExtendedFormat(array $index, array $s, array $p, array $o) {
        $model = new DSSN_Model($index);


        $typeO = $o['type'];
        $object = array();
        $object['value'] = $o['value'];
        switch ($typeO) {
            case 'uri':
                $object['type'] = 'uri';
                break;
            case 'typed-literal':
                $object['type'] = 'literal';
                $object['datatype'] = $o['datatype'];
                break;
            case 'literal':
                $object['type'] = 'literal';
                if (isset($o['xml:lang'])) {
                    $object['lang'] = $o['xml:lang'];
                }
                break;
            default:
                /* be quiet here */
                break;
        }

        $statement = array();
        $s = $s['value']; // is always an IRI (or bnode)
        $p = $p['value']; // is always an IRI
        $pArray[$p] = $object;
        $statement[$s] = $pArray;
        $model->addStatements($statement);

        return $model->getStatements();
    }

    /*
     * set the needed constants for all DSSN classes
     */
    static public function setConstants() {
        if (defined('DSSN_AAIR_NS')) {
            return;
        } else {
            /*
             * DSSN namespace constants
             */
            define('DSSN_AAIR_NS' , 'http://xmlns.notu.be/aair#');
            define('DSSN_RDF_NS'  , 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            define('DSSN_RDFS_NS' , 'http://www.w3.org/2000/01/rdf-schema#');
            define('DSSN_FOAF_NS' , 'http://xmlns.com/foaf/0.1/');
            define('DSSN_ATOM_NS' , 'http://www.w3.org/2005/Atom/');
            define('DSSN_XSD_NS'  , 'http://www.w3.org/2001/XMLSchema#');

            /*
             * DSSN resource constants
             */
            define('DSSN_ATOM_published'      , DSSN_ATOM_NS . 'published');
            define('DSSN_AAIR_Activity'       , DSSN_AAIR_NS . 'Activity');
            define('DSSN_AAIR_activityActor'  , DSSN_AAIR_NS . 'activityActor');
            define('DSSN_AAIR_activityVerb'   , DSSN_AAIR_NS . 'activityVerb');
            define('DSSN_AAIR_activityObject' , DSSN_AAIR_NS . 'activityObject');
            define('DSSN_AAIR_avatar'         , DSSN_AAIR_NS . 'avatar');
            define('DSSN_AAIR_content'        , DSSN_AAIR_NS . 'content');
            define('DSSN_AAIR_thumbnail'      , DSSN_AAIR_NS . 'thumbnail');
            define('DSSN_RDF_type'            , DSSN_RDF_NS . 'type');
        }
    }

    /*
     * returns an array of used DSSN namespace/prefix tupels
     */
    static public function getNamespaces() {
        DSSN_Utils::setConstants();
        $namespaces = array(
            'aair' => DSSN_AAIR_NS,
            'rdf'  => DSSN_RDF_NS,
            'rdfs' => DSSN_RDFS_NS,
            'foaf' => DSSN_FOAF_NS,
            'atom' => DSSN_ATOM_NS,
            'xsd'  => DSSN_XSD_NS,
        );
        return $namespaces;
    }

}
