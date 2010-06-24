<?php
/**
 * Nav helper. builds a query for the nav controller and for the resource controller
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_navigation
 */
class NavigationHelper extends OntoWiki_Component_Helper
{
    public static function getInstancesTriples($uri, $setup){
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $classVar = new Erfurt_Sparql_Query2_Var('classUri');
        $elements = array();
        
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
        $elements[] = $union;
        
        $owApp = OntoWiki::getInstance();
        $modelIRI = (string)$owApp->selectedModel;
        $store = $owApp->erfurt->getStore();
        // get all subclass of the super class
        $classes = array();
        if( isset($setup->config->hierarchyRelations->out) ){
            foreach($setup->config->hierarchyRelations->out as $rel){
                $classes += $store->getTransitiveClosure($modelIRI, $rel, $uri, false);
            }
        }
        if( isset($setup->config->hierarchyRelations->in) ){
            foreach($setup->config->hierarchyRelations->in as $rel){
                $classes += $store->getTransitiveClosure($modelIRI, $rel, $uri, true);
            }
        }
        
        // create filter for types
        $filter_type = array();
        $counted = array();
        foreach ($classes as $class) {
            // get uri
            $uri = ($class['parent'] != '')?$class['parent']:$class['node'];
            
            // if this class is already counted - continue
            if( in_array($uri, $counted) ) {
                if( $class['node'] != '' ){
                    $uri = $class['node'];
                    if( in_array($uri, $counted) )
                        continue;
                }else{
                    continue;
                }
            }
            
            $filter_type[] = new Erfurt_Sparql_Query2_sameTerm($classVar, new Erfurt_Sparql_Query2_IriRef($uri));
            
            // add uri to counted
            $counted[] = $uri;
        }
        // add filter
        $elements[] = new Erfurt_Sparql_Query2_Filter(
            new Erfurt_Sparql_Query2_ConditionalOrExpression($filter_type)
        );
        
        return $elements;
    }



    public static function getSearchTriples($setup, $forImplicit = false){
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $classVar = new Erfurt_Sparql_Query2_Var('classUri');
        $subVar = new Erfurt_Sparql_Query2_Var('subResourceUri');
        $elements = array();

        // if deeper query
        if ( isset($setup->state->parent) ) {
            $mainUnion = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();

            // in relations
            if ( isset($setup->config->hierarchyRelations->in) ){
                // default stuff
                if( count($setup->config->hierarchyRelations->in) > 1 ){
                    // init union var
                    //$union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                    // parse config gile
                    foreach($setup->config->hierarchyRelations->in as $rel){
                        // set type
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),
                            new Erfurt_Sparql_Query2_IriRef($setup->state->parent)
                        );
                        // add triplet to union var
                        $mainUnion->addElement($u1);
                    }
                    //$elements[] = $union;
                }else{
                    $rel = $setup->config->hierarchyRelations->in;
                    $queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();//Erfurt_Sparql_Query2_OptionalGraphPattern();
                    $queryOptional->addTriple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel[0]),
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent)
                    );
                    $mainUnion->addElement($queryOptional);
                    /*$elements[] = new Erfurt_Sparql_Query2_Triple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel[0]),
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent)
                    );*/
                }
            }

            // out relations
            if ( isset($setup->config->hierarchyRelations->out) ){
                // if there's out relations
                if ( count($setup->config->hierarchyRelations->out) > 1 ){
                    // init union var
                    //$union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                    // parse config gile
                    foreach($setup->config->hierarchyRelations->out as $rel){
                        // set type
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple(
                            new Erfurt_Sparql_Query2_IriRef($setup->state->parent),
                            new Erfurt_Sparql_Query2_IriRef($rel), 
                            $searchVar
                        );
                        // add triplet to union var
                        $mainUnion->addElement($u1);
                    }
                    //$elements[] = $union;
                    //$query->addFilter( new Erfurt_Sparql_Query2_bound( new Erfurt_Sparql_Query2_Var('instance') ) );
                }else{
                    // get one relation
                    $rel = $setup->config->hierarchyRelations->out;
                    $queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();//Erfurt_Sparql_Query2_OptionalGraphPattern();
                    $queryOptional->addTriple(
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent),
                        new Erfurt_Sparql_Query2_IriRef($rel[0]),
                        $searchVar
                    );
                    $mainUnion->addElement($queryOptional);
                    /*$elements[] = new Erfurt_Sparql_Query2_Triple(
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent),
                        new Erfurt_Sparql_Query2_IriRef($rel[0]),
                        $searchVar
                    );*/
                }
            }

            $elements[] = $mainUnion;

        } else { // if default request
            if(!$forImplicit){
                // set hierarchy types
                /*$elements[] = new Erfurt_Sparql_Query2_Triple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
                    $classVar
                );*/
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $u1->addTriple( $searchVar,
                    new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
                    $classVar
                );

                $mainUnion = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                $mainUnion->addElement($u1);

                // request sub elements --------------
                // in relations
                if ( isset($setup->config->hierarchyRelations->in) ){
                    if( count($setup->config->hierarchyRelations->in) > 1 ){
                        // init union var
                        //$unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                        // parse config gile
                        foreach($setup->config->hierarchyRelations->in as $rel){
                            // sub stuff
                            $u1 = new Erfurt_Sparql_Query2_OptionalGraphPattern();//Erfurt_Sparql_Query2_GroupGraphPattern();//Erfurt_Sparql_Query2_OptionalGraphPattern();
                            // add triplen
                            $u1->addTriple(
                                $subVar,
                                new Erfurt_Sparql_Query2_IriRef($rel),
                                $searchVar
                            );
                            // add triplet to union var
                            $mainUnion->addElement($u1);
                        }
                        //$elements[] = $unionSub;
                        //$mainUnion->addElement($unionSub);
                    }else{
                        $rel = $setup->config->hierarchyRelations->in;
                        // add optional sub relation
                        // create optional graph to load sublacsses of selected class
                        $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();//Erfurt_Sparql_Query2_GroupGraphPattern();//Erfurt_Sparql_Query2_OptionalGraphPattern();
                        $queryOptional->addTriple(
                            $subVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $searchVar
                        );
                        //$elements[] = $queryOptional;
                        $mainUnion->addElement($queryOptional);
                    }
                }
                if ( isset($setup->config->hierarchyRelations->out) ){
                    // init union var
                    $unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                    if( count($setup->config->hierarchyRelations->out) > 1 ){
                        // parse config gile
                        foreach($setup->config->hierarchyRelations->out as $rel){
                            // sub stuff
                            $u1 = new Erfurt_Sparql_Query2_OptionalGraphPattern();//Erfurt_Sparql_Query2_GroupGraphPattern();// Erfurt_Sparql_Query2_OptionalGraphPattern();
                            // add triplen
                            $u1->addTriple(
                                $searchVar,
                                new Erfurt_Sparql_Query2_IriRef($rel),
                                $subVar
                            );
                            // add triplet to union var
                            $unionSub->addElement($u1);
                        }
                        //$elements[] = $unionSub;
                        //$mainUnion->addElement($unionSub);
                    }else{
                        $rel = $setup->config->hierarchyRelations->out;
                        // add optional sub relation
                        // create optional graph to load sublacsses of selected class
                        $elements[] = new Erfurt_Sparql_Query2_Triple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $subVar
                        );
                        /*$queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();//Erfurt_Sparql_Query2_OptionalGraphPattern();
                        $queryOptional->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $subVar
                        );*/
                        //$elements[] = $queryOptional;
                    }
                }

                $elements[] = $mainUnion;
                // -----------------------------------

                // create filter for types
                $filter_type = array();
                foreach ($setup->config->hierarchyTypes as $type) {
                    $filter_type[] = new Erfurt_Sparql_Query2_sameTerm($classVar, new Erfurt_Sparql_Query2_IriRef($type));
                }
                // add filter
                $elements[] = new Erfurt_Sparql_Query2_Filter(
                    new Erfurt_Sparql_Query2_ConditionalOrExpression($filter_type)
                );
            } else {
                // define subvar
                $subVar = new Erfurt_Sparql_Query2_Var('sub');
                // init union var
                $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config
                if( isset($setup->config->instanceRelation->out) ){
                    foreach($setup->config->instanceRelation->out as $rel){
                        // create new graph pattern
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( $subVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                            $searchVar
                        );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                }
                // parse config
                if( isset($setup->config->instanceRelation->in) ){
                    foreach($setup->config->instanceRelation->in as $rel){
                        // create new graph pattern
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                            $subVar
                        );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                }
                $elements[] = $union;
            }

        }
        
        if( isset($setup->config->rootElement) ){
            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            if( isset($setup->config->hierarchyRelations->in) ){
                foreach($setup->config->hierarchyRelations->in as $rel){
                    // create new graph pattern
                    $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    // add triplen
                    $u1->addTriple( $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                        new Erfurt_Sparql_Query2_IriRef($setup->config->rootElement)
                    );
                    // add triplet to union var
                    $union->addElement($u1);
                }
                $superUsed = true;
            }
            if( isset($setup->config->hierarchyRelations->out) ){
                foreach($setup->config->hierarchyRelations->out as $rel){
                    // create new graph pattern
                    $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    // add triplen
                    $u1->addTriple( new Erfurt_Sparql_Query2_IriRef($setup->config->rootElement),
                        new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                        $searchVar
                    );
                    // add triplet to union var
                    $union->addElement($u1);
                }
                $superUsed = true;
            }
            if($superUsed) $elements[] = $union;
        }        

        $elements[] = new Erfurt_Sparql_Query2_Filter(
            new Erfurt_Sparql_Query2_isUri(
                new Erfurt_Sparql_Query2_Var('resourceUri')
            )
        );

        // namespaces to be ignored, rdfs/owl-defined objects
        if ( !isset($setup->state->showHidden) ) {
            if( isset($setup->config->hiddenRelation) ){
                // optional var
                $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                // parse config
                //$regUsed = false;
                foreach ($setup->config->hiddenRelation as $ignore) {
                    $queryOptional->addTriple(
                        new Erfurt_Sparql_Query2_Var('resourceUri'),
                        new Erfurt_Sparql_Query2_IriRef($ignore),
                        new Erfurt_Sparql_Query2_Var('reg')
                    );
                    $regUsed = true;
                }
                if($regUsed){
                    $elements[] = $queryOptional;
                    $elements[] = new Erfurt_Sparql_Query2_Filter(
                        new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_bound(
                                new Erfurt_Sparql_Query2_Var('reg')
                            )
                        )
                    );
                }
            }

            if( isset($setup->config->hiddenNS) ){
                // parse config
                foreach ($setup->config->hiddenNS as $ignore) {
                    $elements[] = new Erfurt_Sparql_Query2_Filter(
                        new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_Regex(
                                new Erfurt_Sparql_Query2_Str( new Erfurt_Sparql_Query2_Var('resourceUri') ),
                                new Erfurt_Sparql_Query2_RDFLiteral('^' . $ignore)
                            )
                        )
                    );
                }
            }
        }

        // dont't show rdfs/owl entities and subtypes in the first level
        if ( !isset($setup->state->parent) && !isset($setup->config->rootElement) ) {
            //$superUsed = false;
            // optional var
            $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
            if( isset($setup->config->hierarchyRelations->in) ){
                foreach($setup->config->hierarchyRelations->in as $rel){
                    $queryOptional->addTriple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        new Erfurt_Sparql_Query2_Var('super')
                    );
                }
                $superUsed = true;
            }
            if( isset($setup->config->hierarchyRelations->out) ){
                foreach($setup->config->hierarchyRelations->out as $rel){
                    $queryOptional->addTriple(
                        new Erfurt_Sparql_Query2_Var('super'),
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        $searchVar
                    );
                }
                $superUsed = true;
            }
            if($superUsed){
                $elements[] = $queryOptional;

                $filter[] = new Erfurt_Sparql_Query2_Regex(
                                new Erfurt_Sparql_Query2_Str( new Erfurt_Sparql_Query2_Var('super') ),
                                new Erfurt_Sparql_Query2_RDFLiteral('^'.EF_OWL_NS )
                            );
                $filter[] = new Erfurt_Sparql_Query2_UnaryExpressionNot(
                                new Erfurt_Sparql_Query2_bound(
                                    new Erfurt_Sparql_Query2_Var('super')
                                )
                            );

                $elements[] = new Erfurt_Sparql_Query2_Filter(
                    new Erfurt_Sparql_Query2_ConditionalOrExpression($filter)
                );
            }
        }
        
        return $elements;
    }
}

