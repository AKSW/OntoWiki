<?php


/**
 * Nav helper. builds a query for the nav controller and for the resource controller
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author Norman Heino <norman.heino@gmail.com>
 * @version $Id: FoafeditHelper.php 3053 2009-05-08 12:15:51Z norman.heino $
 */
class NavigationHelper extends OntoWiki_Component_Helper
{

    public function init()
    {
        
    }

    public function shouldShow () 
    {
        return true; // right?
    }

    public static function buildQuery($uri, $setup)
    {
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $query = new Erfurt_Sparql_Query2();
        $query->setCountStar(true);
        //$query->setDistinct();
        //$query->addProjectionVar($searchVar);

        // init union var
        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        // parse config
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
        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
        // add triplen
        $u1->addTriple( $searchVar,
            new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
            new Erfurt_Sparql_Query2_IriRef($uri) );
        $union->addElement($u1);
        $query->addElement($union);

        return $query;
    }
}

