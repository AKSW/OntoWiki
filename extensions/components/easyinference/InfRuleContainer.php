<?php

require_once "InfRule.php";

/**
 * A container to parse the rules and make them available
 * This is a singleton class, therefore it will be instantiated only one time.  
 *
 * @package    easyinference
 * @author     swp-09-7
 */

class InfRuleContainer
{
    //Singleton-Instance
    private static $instance = null;
    // array of the rules
    private static $rules = null;

    /**
     * get the instance of this class. if this class is not instantiated, a
     * instance will be created
     * @return the instance
     */
    public static function getInstance ()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
            self::loadRules ();
        }

        return self::$instance;
    }

    /**
     * returns the rules
     * @return the rules
     */
    public function getRules ()
    {
        return self::$rules;  
    }

    /**
     * load and parse the Rules
     */
    private static function loadRules () 
    {
        //hardcoded rules; TODO: write a parser to load the rules
	     $ruleData = Erfurt_App::getInstance()->getStore()->sparqlQuery(
             Erfurt_Sparql_SimpleQuery::initWithString(
                 'PREFIX : <http://ns.ontowiki.net/Extension/EasyInference/>'.
				 //' PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>'.
				 //' PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>'.
				 ' SELECT DISTINCT ?name ?r ?predicate ?conclusion'.
                 ' FROM :'.
                 ' WHERE { ?r a :InfRule ;'
                       .' rdfs:label ?name ;'
					   .' :Conclusion ?conclusion ;'
                       .' :Predicate ?predicate }'));
	    

        self::$rules = array ();

        //make rules and put them in the rules-array
        foreach ($ruleData as $index => $rule)
        {
            self::$rules[$rule['r']] = new InfRule ($rule['predicate'],
                                                $rule['conclusion'],
                                                $rule['name']);
        }     
    }

}