<?php
/**
 * Nav helper. builds a query for the nav controller and for the resource controller
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_navigation
 */
class NavigationHelper extends OntoWiki_Component_Helper
{
    public static function buildQuery($uri, $setup)
    {
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $classVar = new Erfurt_Sparql_Query2_Var('classUri'); // new Erfurt_Sparql_Query2_IriRef($uri)
        $query = new Erfurt_Sparql_Query2();
        $query->setCountStar(true);
        //$query->setDistinct();

        // init union var
        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        // parse config
        if( isset($setup->config->instanceRelation->in) ){
            foreach($setup->config->instanceRelation->in as $rel){
                // create new graph pattern
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $u1->addTriple( $classVar,
                    new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                    $searchVar
                );
                // add triplet to union var
                $union->addElement($u1);
            }
        }
        // parse config
        if( isset($setup->config->instanceRelation->out) ){
            foreach($setup->config->instanceRelation->out as $rel){
                // create new graph pattern
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $u1->addTriple( $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                    $classVar
                );
                // add triplet to union var
                $union->addElement($u1);
            }
        }
        $query->addElement($union);
        $query->addFilter( new Erfurt_Sparql_Query2_sameTerm($classVar, new Erfurt_Sparql_Query2_IriRef($uri)) );
        
        return $query;
    }
    
    /*public static function buildQuery($uri, $setup)
    {
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $query = new Erfurt_Sparql_Query2();
        $query->setCountStar(true);
        //$query->setDistinct();

        // init union var
        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        // parse config
        if( isset($setup->config->hierarchyRelations->in) ){
            foreach($setup->config->hierarchyRelations->in as $rel){
                // create new graph pattern
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $u1->addTriple( $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                    new Erfurt_Sparql_Query2_IriRef($uri)
                );
                // add triplet to union var
                $union->addElement($u1);
            }
        }
        // parse config
        if( isset($setup->config->hierarchyRelations->out) ){
            foreach($setup->config->hierarchyRelations->out as $rel){
                // create new graph pattern
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $u1->addTriple(  new Erfurt_Sparql_Query2_IriRef($uri),
                    new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                    $searchVar
                );
                // add triplet to union var
                $union->addElement($u1);
            }
        }
        $query->addElement($union);

        return $query;
    }*/
}

