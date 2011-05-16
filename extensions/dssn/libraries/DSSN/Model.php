<?php
/**
 * A set of Statements (memory model) / ARC2 index / phprdf array
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Model
{
    private $statements = array();

    function __construct( array $init = array()){
        $this->addStatements($init);
    }


    /*
     * checks if there is at least one statement for resource $iri
     */
    public function hasS($s = null) {
        if ($s == null) {
            throw new Exception('need an IRI string as first parameter');
        }
        if (isset($this->statements[$s])) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * checks if there is at least one statement for resource $iri with
     * predicate $p
     */
    public function hasSP($s = null, $p = null ) {
        if (!$this->hasS($s)) {
            return false;
        } else {
            if ($p == null) {
                throw new Exception('need an IRI string as second parameter');
            }
            if (isset($this->statements[$s][$p])) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
     * search for a value where S and P is fix
     */
    public function hasSPvalue($s = null, $p = null, $value = null){
        if ($value == null) {
            throw new Exception('need a value string as third parameter');
        } else {
            $values = $this->getValues($s, $p);
            foreach ($values as $key => $object) {
                if ($object['value'] == $value) {
                    return true;
                }
            }
            return false;
        }
    }

    /*
     * count statements where S and P is fix
     */
    public function countSP($s = null, $p = null){
        if (!$this->hasSP($s, $p)) {
            return 0;
        } else {
            //var_dump(count($this->statements[$s][$p]));
            return count($this->statements[$s][$p]);
        }
    }

    /*
     * returns an array of values where S and P is fix
     */
    public function getValues($s = null, $p = null){
        if (!$this->hasSP($s, $p)) {
            return array();
        } else {
            return $this->statements[$s][$p];
        }
    }

    /*
     * returns the first object value where S and P is fix
     */
    public function getValue($s = null, $p = null){
        if (!$this->hasSP($s, $p)) {
            return false;
        } else {
            return $this->statements[$s][$p][0]['value'];
        }
    }

    /*
     * return the statement array, limited to a subject uri
     */
    public function getStatements($iri = null){
        if ($iri == null) {
            return $this->statements;
        } else {
            if ($this->hasS($iri)) {
                return array( $iri => $this->statements[$iri] );
            } else {
                return array();
            }
        }
    }

    /*
     * This adds a statement array to the model by merging the arrays
     * This function is the base for all other add functions
     */
    public function addStatements(array $statements){
        $model = $this->statements;
        foreach ($statements as $subjectIri => $subjectArray) {
            if (!isset($model[$subjectIri])) {
                // new subject
                $model[$subjectIri] = $subjectArray;
            } else {
                // existing subject
                foreach ($subjectArray as $predicateIri => $predicateArray) {
                    if (!isset($model[$subjectIri][$predicateIri])) {
                        // new predicate on subject
                        $model[$subjectIri][$predicateIri] = $predicateArray;
                    } else {
                        // existing predicate on subject
                        foreach ($predicateArray as $objectArray) {
                            if (!in_array($objectArray, $model[$subjectIri][$predicateIri])) {
                                // new object for subject/predicate pattern
                                $model[$subjectIri][$predicateIri][] = $objectArray;
                            } else {
                                // same triple
                            }
                        }
                    }
                }
            }
        }
        $this->statements = $model;
    }

    /*
     * adds a triple based on the result of an extended SPARQL query
     */
    public function addStatementFromExtendedFormatArray(array $s, array $p, array $o) {
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
                /* blank nodes are ignore */
                /* be quiet here */
                break;
        }

        $statement = array();
        $s = $s['value']; // is always an IRI (or bnode)
        $p = $p['value']; // is always an IRI

        $pArray[$p] = array(0 => $object);
        $statement[$s] = $pArray;

        $this->addStatements($statement);
    }
}
