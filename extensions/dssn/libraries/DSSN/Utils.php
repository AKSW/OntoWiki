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

    static public function setConstants() {
        if (defined('DSSN_AAIR_NS')) {
            return;
        } else {
            /*
             * DSSN namespace constants
             */
            define('DSSN_AAIR_NS', 'http://xmlns.notu.be/aair#');
            define('DSSN_RDF_NS',  'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            define('DSSN_RDFS_NS', 'http://www.w3.org/2000/01/rdf-schema#');
            define('DSSN_FOAF_NS', 'http://xmlns.com/foaf/0.1/');
            define('DSSN_ATOM_NS', 'http://www.w3.org/2005/Atom/');
            define('DSSN_XSD_NS', 'http://www.w3.org/2001/XMLSchema#');

            /*
             * DSSN resource constants
             */
            define('DSSN_ATOM_published', DSSN_ATOM_NS . 'published');
            define('DSSN_AAIR_Activity', DSSN_AAIR_NS . 'Activity');
            define('DSSN_AAIR_activityActor', DSSN_AAIR_NS . 'activityActor');
            define('DSSN_AAIR_activityVerb', DSSN_AAIR_NS . 'activityVerb');
            define('DSSN_AAIR_activityObject', DSSN_AAIR_NS . 'activityObject');
            define('DSSN_AAIR_avatar', DSSN_AAIR_NS . 'avatar');
            define('DSSN_AAIR_content', DSSN_AAIR_NS . 'content');
            define('DSSN_AAIR_thumbnail', DSSN_AAIR_NS . 'thumbnail');
        }
    }

}
