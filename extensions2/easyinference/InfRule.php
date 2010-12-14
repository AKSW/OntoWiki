<?php

/**
 * A InfRule has the needed information of a rule to generate inferences.
 * It consists of a predicate and a conclusion as well as methods, which help
 * to generate inferences due to the rule.
 *
 * @package    easyinference
 * @author     swp-09-7
 * @author Sebastian Hellmann <hellmann@informatik.uni-leipzig.de>
 */

define('EI_INFRULE_EIVAR','?eiVar');
class InfRule {

    private $predicate = null;
    private $conclusion = null;
    private $name = null;

    /**
     * constructor;
     *
     * @param predicate the predicate of the rule
     * @param conclusion the conclusion of the rule
     * @param name the name of the rule
     */
    public function __construct($predicate, $conclusion, $name) {
        $this->predicate = $predicate;
        $this->conclusion = $conclusion;
        $this->name = $name;
    }

    /**
     * @return the predicate
     */
    public function getPredicate () {
        return $this->predicate;
    }

    /**
     * @return the conclusion
     */
    public function getConclusion () {
        return $this->conclusion;
    }

    /**
     * @return the name
     */
    public function getName () {
        return $this->name;
    }

    /**
     * Method, which build parts of a Sparql-query to get inferent data from a model.
     * It generate only inferences of a specific resource given as $subject.
     *
     * @param resource the resource the rule is applied
     * @param options an array with options, options can be:
     *                index      | value   | Description
     *            ---------------|------------------------
     *             fromGraph     | array   | array of graph-uris to add to the FROM clause
     *     disallowBoundedTripel | boolean | true to ignore tripel already exists, false/null else
     *
     * @return array, which consists of the where and prologue part of the rule and an
     *         array of constants, which complete the results of the query to tripels
     */
    public function getQueryFromRule ($resource, $options = null) {
        $logger = OntoWiki::getInstance()->logger;
        $query = new Erfurt_Sparql_SimpleQuery();
        $conclusionParts = array ('subject', 'predicate', 'object');
        $conclusionArray = array();
        $prologuePart = '';
        $wherePart = $this->predicate;
        $filterString = '';
        $filterArray = array();
        $filterArrayInsideOptional = array();

        // add a filter to the first variable to generate only inferences of a specific resource given as $resource
        trim ($wherePart);
        if (isset ($resource)) {
            $resource = '<'.$resource.'>';
            $resourceVariable = strtok ($wherePart, ' ');
            $tmp = ' '.$resourceVariable.' = '. $resource;
            $filterArray[] = $tmp;
            $logger->debug("applying filter to resource: ".$tmp);
        }

        // add the parts of the Conclusion to the conlusionArray
        $tok = strtok ( $this->conclusion , ' ');
        for ($count = 0; $count < 3 && $tok !== false; $count++) {
            $conclusionArray[$count] = $tok;
            $tok = strtok (' ');
        }

        //make the Prologue-Part and replace constants with a variable on which a filter to the constant is set
        for ($count = 0; $count < 3; $count++) {
            //is it a variable(with a prefixed '?') or a constant
            if ($conclusionArray[$count][0] === '?') {
                //add the variable to the prologue part
                $prologuePart .= $conclusionArray[$count].' AS ?'.$conclusionParts[$count].' ';
            } else {

                // replace all occurrence of the constant to the variable and add a filter
                $stringToReplace = $conclusionArray[$count];
                $replaceTo = EI_INFRULE_EIVAR.$count;
                $wherePart = str_replace ($stringToReplace, $replaceTo , $wherePart );
                $conclusionArray = str_replace ($stringToReplace,
                        $replaceTo ,
                        $conclusionArray );

                $prologuePart .= $replaceTo.' AS ?'.$conclusionParts[$count].' ';
                $tmpFilter = $replaceTo.' = '.$stringToReplace;
                $filterArrayInsideOptional[] = $tmpFilter;
                $logger->debug("applying special filter : ".$tmpFilter);
            }
        }

        //add a filter to get only uris
        foreach ($conclusionArray as $var) {
            $tmpFilter = 'isIRI ('.$var.')';
            //this is filtered out atm cause it was producing errors
            if($this->startsWith($var, EI_INFRULE_EIVAR)) {
                $filterArrayInsideOptional[] = $tmpFilter;
                //continue;
            }else {
                $filterArray[] = $tmpFilter;
            }
            $logger->debug("applying iri filter : ".$tmpFilter);
        }

        //add a filter to get eliminate reflexive duplicates
        $filterArray[] = $conclusionArray[0].' != '.$conclusionArray[2];
        $logger->debug("eliminate reflexives : ".$tmpFilter);
      

        if (isset($options)) {
            if (isset ($options['fromGraph']))
                foreach ($options['fromGraph'] as $graph)
                    $query -> addFrom($graph);

            if (isset ($options['disallowBoundedTripel'])
                    && $options['disallowBoundedTripel'] == true) {
                $filterInside = $this->expandFilter($filterArrayInsideOptional);
                $wherePart .= ' OPTIONAL { GRAPH ?graph { '
                        .$conclusionArray[0].' '
                        .$conclusionArray[1].' '
                        .$conclusionArray[2].' } '. $filterInside.' }';

                $tmpFilter = ' !BOUND(?graph) ';
                $filterArray[] = $tmpFilter;
                $logger->debug("applying bounded graph filter : ".$tmpFilter);
            }
        }

        $filterString = $this->expandFilter($filterArray);

        $wherePart = 'WHERE { '.$wherePart.$filterString.' } ';
        $prologuePart = 'SELECT DISTINCT '.$prologuePart;

        $query->setProloguePart($prologuePart)
                ->setWherePart($wherePart);

        return $query;
    }

    /**
     * This function is not needed
     * @param <type> $wherePart
     * @return <type>
     */
    private function getAllVars($wherePart) {
        $clean =  str_replace("}", " ",  str_replace("{", " ",  str_replace("(", " ",  str_replace(")", " ",  str_replace("}", " ", $wherePart)))));
        $split = explode(" ", $clean);
        $vars = array();
        foreach($split as $one) {
            if($this->startsWith($one, '?')) {
                $vars[$one] = "";
            }
        }
        return array_keys($vars);
    }


    private function expandFilter(array $filterArray) {
        $retval = "";
        for($i=0;$i<count($filterArray);$i++) {
            $retval .= ($i==0)?"":" && ";
            $retval .= $filterArray[$i];
        }
        return (empty($retval))?"":' FILTER ( '.$retval.' )';
    }

    private function startsWith($Haystack, $Needle) {
        // Recommended version, using strpos
        return strpos($Haystack, $Needle) === 0;
    }

    /**
     * Checks if the rule could be applied to the resource in the model
     *
     * @param resource the resource, which constraint the check. no constraints if resource is null,
     * @param model the model, where the resource is inside
     * @prologue the method, how is checked (SPARQL ASK or SELECT *)
     * @limit not used by SPARQL ASK
     *
     * @return boolean, wether the rule can applie
     */
    public function checkRuleApplicable ($resource, $model, $prologue = 'ASK', $limit = 1) {
        $applicable = false;
        $res = array();
        $limitString = '';
        $wherePart = trim ($this->predicate);
        $variable = strtok ( $wherePart, ' ');

        if (!preg_match('/(.*?)[\.}]/', $wherePart, $res))
            return false;

        if (isset ($limit) && $prologue != 'ASK')
            $limitString = 'LIMIT '.$limit;

        $wherePart = ' WHERE { '.$res[1];

        if ($resource)
            $wherePart .= ' FILTER ( '.$variable.' = <'.$resource.'> )';

        $query = new Erfurt_Sparql_SimpleQuery ();
        $query->setProloguePart(' '.$prologue)      //space prevents from a bug
                ->addFrom ($model->getModelUri ())
                ->setWherePart($wherePart.' } '.$limitString);

        if ( Erfurt_App::getInstance()->getStore()->sparqlQuery ($query) )
            $applicable = true;

        return $applicable;
    }

}
