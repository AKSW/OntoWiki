<?php
/**
 * A set of Statements (memory model)
 * deprecated - use ARC2 indexes instead!!!
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
     * return the statement array
     */
    public function getStatements(){
        return $this->statements;
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
}
