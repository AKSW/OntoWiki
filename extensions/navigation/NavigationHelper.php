<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Nav helper. builds a query for the nav controller and for the resource controller
 * @category   OntoWiki
 * @package    Extensions_Navigation
 */
class NavigationHelper extends OntoWiki_Component_Helper
{
    /*
     * Returns Triples for list generation query
     */
    public static function getInstancesTriples($uri, $setup)
    {
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $classVar  = new Erfurt_Sparql_Query2_Var('classUri');
        $elements  = array();

        // init union var
        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();

        // parse config
        if (isset($setup->config->hierarchyRelations->out)) {
            foreach ($setup->config->hierarchyRelations->out as $rel) {
                // create new graph pattern
                $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $ggp->addTriple(
                    $classVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $searchVar
                );
                // add triplet to union var
                $union->addElement($ggp);
            }
        }

        // parse config
        if (isset($setup->config->hierarchyRelations->in)) {
            foreach ($setup->config->hierarchyRelations->in as $rel) {
                // create new graph pattern
                $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $ggp->addTriple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $classVar
                );
                // add triplet to union var
                $union->addElement($ggp);
            }
        }

        // parse config
        if (isset($setup->config->instanceRelation->in)) {
            foreach ($setup->config->instanceRelation->in as $rel) {
                // create new graph pattern
                $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $ggp->addTriple(
                    $classVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $searchVar
                );
                // add triplet to union var
                $union->addElement($ggp);
            }
        }

        // parse config
        if (isset($setup->config->instanceRelation->out)) {
            foreach ($setup->config->instanceRelation->out as $rel) {
                // create new graph pattern
                $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $ggp->addTriple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $classVar
                );
                // add triplet to union var
                $union->addElement($ggp);
            }
        }
        $elements[] = $union;

        $owApp    = OntoWiki::getInstance();
        $modelIRI = (string)$owApp->selectedModel;
        $store    = $owApp->erfurt->getStore();

        // get all subclass of the super class
        $classes = array();
        if (isset($setup->config->hierarchyRelations->out)) {
            foreach ($setup->config->hierarchyRelations->out as $rel) {
                $classes += $store->getTransitiveClosure($modelIRI, $rel, $uri, false);
            }
        }
        if (isset($setup->config->hierarchyRelations->in)) {
            foreach ($setup->config->hierarchyRelations->in as $rel) {
                $classes += $store->getTransitiveClosure($modelIRI, $rel, $uri, true);
            }
        }

        // create filter for types
        $filterType = array();
        $filterUris  = array();
        $counted     = array();
        foreach ($classes as $class) {
            // get uri
            $uri = ($class['parent'] != '')?$class['parent']:$class['node'];

            // if this class is already counted - continue
            if (in_array($uri, $counted)) {
                if ( $class['node'] != '' ) {
                    $uri = $class['node'];
                    if(in_array($uri, $counted))
                        continue;
                } else {
                    continue;
                }
            }

            $uriElem = new Erfurt_Sparql_Query2_IriRef($uri);
            $filterUris[] = $uriElem;
            $filterType[] = new Erfurt_Sparql_Query2_sameTerm($classVar, $uriElem);

            // add uri to counted
            $counted[] = $uri;
        }

        if ($store->isInSyntaxSupported()) { // e.g. Virtuoso
            $elements[] = new Erfurt_Sparql_Query2_Filter(
                new Erfurt_Sparql_Query2_InExpression($classVar, $filterUris)
            );
        } else { // sameTerm || sameTerm ... as supported by EfZendDb adapter
            // add filter
            $elements[] = new Erfurt_Sparql_Query2_Filter(
                new Erfurt_Sparql_Query2_ConditionalOrExpression($filterType)
            );
        }

        return $elements;
    }

    public static function getSearchTriples($setup, $forImplicit = false, $backend = 'zenddb')
    {
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $classVar = new Erfurt_Sparql_Query2_Var('classUri');
        $subVar = new Erfurt_Sparql_Query2_Var('subResourceUri');
        $elements = array();

        // if deeper query
        if (isset($setup->state->parent)) {
            $mainUnion = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();

            // in relations
            if (isset($setup->config->hierarchyRelations->in)) {
                // default stuff
                if (count($setup->config->hierarchyRelations->in) > 1) {
                    // parse config gile
                    foreach ($setup->config->hierarchyRelations->in as $rel) {
                        // set type
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $ggp->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),
                            new Erfurt_Sparql_Query2_IriRef($setup->state->parent)
                        );
                        // add triplet to union var
                        $mainUnion->addElement($ggp);
                    }
                } else {
                    $rel = $setup->config->hierarchyRelations->in;
                    $queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    $queryOptional->addTriple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel[0]),
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent)
                    );
                    $mainUnion->addElement($queryOptional);
                }
            }

            // out relations
            if (isset($setup->config->hierarchyRelations->out)) {
                // if there's out relations
                if (count($setup->config->hierarchyRelations->out) > 1) {
                    // parse config gile
                    foreach ($setup->config->hierarchyRelations->out as $rel) {
                        // set type
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $ggp->addTriple(
                            new Erfurt_Sparql_Query2_IriRef($setup->state->parent),
                            new Erfurt_Sparql_Query2_IriRef($rel),
                            $searchVar
                        );
                        // add triplet to union var
                        $mainUnion->addElement($ggp);
                    }
                } else {
                    // get one relation
                    $rel = $setup->config->hierarchyRelations->out;
                    $queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    $queryOptional->addTriple(
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent),
                        new Erfurt_Sparql_Query2_IriRef($rel[0]),
                        $searchVar
                    );
                    $mainUnion->addElement($queryOptional);
                }
            }

            $elements[] = $mainUnion;

        } else { // if default request
            if (!$forImplicit) {
                // set hierarchy types
                //$u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
//                $u1->addTriple( $searchVar,
//                    new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
//                    $classVar
//                );

                $elements[] = new Erfurt_Sparql_Query2_Triple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
                    $classVar
                );

                //$mainUnion = new Erfurt_Sparql_Query2_GroupGraphPattern();//OrUnion
                //$mainUnion->addElement($u1);

                // request sub elements
                // in relations
                $optional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                $unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                if (isset($setup->config->hierarchyRelations->in)) {
                    if (count($setup->config->hierarchyRelations->in) > 1) {
                        // init union var
                        //$unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                        // parse config gile
                        foreach ($setup->config->hierarchyRelations->in as $rel) {
                            // sub stuff
                            $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                            // add triplen
                            $ggp->addTriple(
                                $subVar,
                                new Erfurt_Sparql_Query2_IriRef($rel),
                                $searchVar
                            );
                            // add triplet to union var
                            $unionSub->addElement($ggp);
                        }
                    } else {
                        $rel = $setup->config->hierarchyRelations->in;
                        // add optional sub relation
                        // create optional graph to load sublacsses of selected class
                        //$queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        $optional->addTriple(
                            $subVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $searchVar
                        );
                        //$unionSub->addElement($queryOptional);
                    }
                }
                if (isset($setup->config->hierarchyRelations->out)) {
                    // init union var
                    //$unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                    if (count($setup->config->hierarchyRelations->out) > 1) {
                        // parse config gile
                        foreach ($setup->config->hierarchyRelations->out as $rel) {
                            // sub stuff
                            $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                            // add triplen
                            $ggp->addTriple(
                                $searchVar,
                                new Erfurt_Sparql_Query2_IriRef($rel),
                                $subVar
                            );
                            // add triplet to union var
                            $unionSub->addElement($ggp);
                        }
                    } else {
                        $rel = $setup->config->hierarchyRelations->out;
                        // add optional sub relation
                        // create optional graph to load sublacsses of selected class
                        //$queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        $optional->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $subVar
                        );
                        //$unionSub->addElement($queryOptional);
                    }
                }
                //$mainUnion->addElement($unionSub);
                if ($unionSub->size() > 0) $optional->addElement($unionSub);
                $elements[] = $optional;

                // create filter for types
                $filterType = array();
                $filterUris = array();
                foreach ($setup->config->hierarchyTypes as $type) {
                    $uriElem = new Erfurt_Sparql_Query2_IriRef($type);
                    $filterUris[] = $uriElem;
                    $filterType[] = new Erfurt_Sparql_Query2_sameTerm($classVar, $uriElem);
                }

                $owApp = OntoWiki::getInstance();
                $store = $owApp->erfurt->getStore();
                if ($store->isInSyntaxSupported()) { // e.g. Virtuoso
                    $elements[] = new Erfurt_Sparql_Query2_Filter(
                        new Erfurt_Sparql_Query2_InExpression($classVar, $filterUris)
                    );
                } else { // sameTerm || sameTerm ... as supported by EfZendDb adapter
                    // add filter
                    $elements[] = new Erfurt_Sparql_Query2_Filter(
                        new Erfurt_Sparql_Query2_ConditionalOrExpression($filterType)
                    );
                }
            } else {
                // define subvar
                $subVar = new Erfurt_Sparql_Query2_Var('sub');
                // init union var
                $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config
                if (isset($setup->config->hierarchyRelations->out)) {
                    if (is_string($setup->config->hierarchyRelations->out)) {
                        $setup->config->hierarchyRelations->out = array($setup->config->hierarchyRelations->out);
                    }
                    foreach ($setup->config->hierarchyRelations->out as $rel) {
                        // create new graph pattern
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $ggp->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                            $subVar
                        );
                        // add triplet to union var
                        $union->addElement($ggp);
                    }
                }
                // parse config
                if (isset($setup->config->hierarchyRelations->in)) {
                    if (is_string($setup->config->hierarchyRelations->in)) {
                        $setup->config->hierarchyRelations->in = array($setup->config->hierarchyRelations->in);
                    }
                    foreach ($setup->config->hierarchyRelations->in as $rel) {
                        // create new graph pattern
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $ggp->addTriple(
                            $subVar,
                            new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                            $searchVar
                        );
                        // add triplet to union var
                        $union->addElement($ggp);
                    }
                }
                // parse config
                if (isset($setup->config->instanceRelation->out)) {
                    if (is_string($setup->config->instanceRelation->out)) {
                        $setup->config->instanceRelation->out = array($setup->config->instanceRelation->out);
                    }
                    foreach ($setup->config->instanceRelation->out as $rel) {
                        // create new graph pattern
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $ggp->addTriple(
                            $subVar,
                            new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                            $searchVar
                        );
                        // add triplet to union var
                        $union->addElement($ggp);
                    }
                }
                // parse config
                if (isset($setup->config->instanceRelation->in)) {
                    if (is_string($setup->config->instanceRelation->in)) {
                        $setup->config->instanceRelation->in = array($setup->config->instanceRelation->in);
                    }
                    foreach ($setup->config->instanceRelation->in as $rel) {
                        // create new graph pattern
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $ggp->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                            $subVar
                        );
                        // add triplet to union var
                        $union->addElement($ggp);
                    }
                }
                $elements[] = $union;
            }

        }

        if (isset($setup->config->rootElement)) {
            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            if (isset($setup->config->hierarchyRelations->in)) {
                foreach ($setup->config->hierarchyRelations->in as $rel) {
                    // create new graph pattern
                    $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    // add triplen
                    $ggp->addTriple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                        new Erfurt_Sparql_Query2_IriRef($setup->config->rootElement)
                    );
                    // add triplet to union var
                    $union->addElement($ggp);
                }
                $superUsed = true;
            }
            if (isset($setup->config->hierarchyRelations->out)) {
                foreach ($setup->config->hierarchyRelations->out as $rel) {
                    // create new graph pattern
                    $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    // add triplen
                    $ggp->addTriple(
                        new Erfurt_Sparql_Query2_IriRef($setup->config->rootElement),
                        new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                        $searchVar
                    );
                    // add triplet to union var
                    $union->addElement($ggp);
                }
                $superUsed = true;
            }
            if($superUsed) $elements[] = $union;
        }

        $elements[] = new Erfurt_Sparql_Query2_Filter(
            new Erfurt_Sparql_Query2_UnaryExpressionNot(
                new Erfurt_Sparql_Query2_isBlank(
                    new Erfurt_Sparql_Query2_Var('resourceUri')
                )
            )
        );

        // namespaces to be ignored, rdfs/owl-defined objects
        if (!isset($setup->state->showHidden)) {
            if (isset($setup->config->hiddenRelation)) {
                // optional var
                $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                // parse config
                if (is_string($setup->config->hiddenRelation)) {
                    $setup->config->hiddenRelation = array($setup->config->hiddenRelation);
                }
                foreach ($setup->config->hiddenRelation as $ignore) {
                    $queryOptional->addTriple(
                        new Erfurt_Sparql_Query2_Var('resourceUri'),
                        new Erfurt_Sparql_Query2_IriRef($ignore),
                        new Erfurt_Sparql_Query2_Var('reg')
                    );
                    $regUsed = true;
                }
                if ($regUsed) {
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

            if (isset($setup->config->hiddenNS)) {
                // parse config
                foreach ($setup->config->hiddenNS as $ignore) {
                    $elements[] = new Erfurt_Sparql_Query2_Filter(
                        new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_Regex(
                                new Erfurt_Sparql_Query2_Str(new Erfurt_Sparql_Query2_Var('resourceUri')),
                                new Erfurt_Sparql_Query2_RDFLiteral('^' . $ignore)
                            )
                        )
                    );
                }
            }
        }

        // dont't show rdfs/owl entities and subtypes in the first level
        if (!isset($setup->state->parent) && !isset($setup->config->rootElement)) {

            OntoWiki::getInstance()->logger->info("BACKEND: ".$backend);

            // optional var
            if ($backend == "zenddb") {
                $queryUnion = new Erfurt_Sparql_Query2_OptionalGraphPattern();
            } else {
                $queryUnion = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            }
            if (isset($setup->config->hierarchyRelations->in)) {
                if (count($setup->config->hierarchyRelations->in) > 1) {
                    foreach ($setup->config->hierarchyRelations->in as $rel) {
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        $ggp->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),
                            new Erfurt_Sparql_Query2_Var('super')
                        );
                        $queryUnion->addElement($ggp);
                    }
                } else {
                    $rel = $setup->config->hierarchyRelations->in;
                    // add optional sub relation
                    if ($backend == "zenddb") {
                        $queryUnion->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            new Erfurt_Sparql_Query2_Var('super')
                        );
                    } else {
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        $ggp->addTriple(
                            $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            new Erfurt_Sparql_Query2_Var('super')
                        );
                        $queryUnion->addElement($ggp);
                    }
                }
                //$mainUnion->addElement($u1);
                $superUsed = true;
            }
            if (isset($setup->config->hierarchyRelations->out)) {
                if (count($setup->config->hierarchyRelations->out) > 1) {
                    foreach ($setup->config->hierarchyRelations->out as $rel) {
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        $ggp->addTriple(
                            new Erfurt_Sparql_Query2_Var('super'),
                            new Erfurt_Sparql_Query2_IriRef($rel),
                            $searchVar
                        );
                        $queryUnion->addElement($ggp);
                    }
                } else {
                    $rel = $setup->config->hierarchyRelations->out;
                    // add optional sub relation
                    if ($backend == "zenddb") {
                        $queryUnion->addTriple(
                            new Erfurt_Sparql_Query2_Var('super'),
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $searchVar
                        );
                    } else {
                        $ggp = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        $ggp->addTriple(
                            new Erfurt_Sparql_Query2_Var('super'),
                            new Erfurt_Sparql_Query2_IriRef($rel[0]),
                            $searchVar
                        );
                        $queryUnion->addElement($ggp);
                    }
                }
                //$mainUnion->addElement($u1);
                $superUsed = true;
            }
            if ($superUsed) {
                if ($backend == "zenddb") {
                    $elements[] = $queryUnion;
                } else {
                    $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                    $queryOptional->addElement($queryUnion);
                    $elements[] = $queryOptional;
                }

                $filter[] = new Erfurt_Sparql_Query2_Regex(
                    new Erfurt_Sparql_Query2_Str(new Erfurt_Sparql_Query2_Var('super')),
                    new Erfurt_Sparql_Query2_RDFLiteral('^'.EF_OWL_NS)
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
        if (isset($setup->state->sorting)) {
            $sortRel = new Erfurt_Sparql_Query2_IriRef($setup->state->sorting);
            $sortVar = new Erfurt_Sparql_Query2_Var('sortRes');

            $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
            $queryOptional->addTriple(new Erfurt_Sparql_Query2_Var('resourceUri'), $sortRel, $sortVar);
            $elements[] = $queryOptional;
        }

        return $elements;
    }
}
