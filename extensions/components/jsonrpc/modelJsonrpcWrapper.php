<?php
/**
 * JSON RPC Class, this wrapper class is for all model RPC calls
 *
 * @package    ontowiki
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */
class modelJsonrpcWrapper
{
    private $store = null;
    private $erfurt = null;

    public function __construct()
    {
        $this->store = Erfurt_App::getInstance()->getStore();
        $this->erfurt = Erfurt_App::getInstance();
    }

    /**
     * exportRdf
     *
     * @param string modelIri
     * @return string
     */
    public function export($modelIri)
    {
        return $this->store->exportRdf($modelIri);
    }

    /**
     * sparql
     *
     * @param string modelIri
     * @param string query
     * @return string
     */
    public function sparql($modelIri, $query = 'SELECT ?resource ?label WHERE {?resource rdfs:label ?label} LIMIT 5')
    {
        $model = $this->store->getModel($modelIri);
        $prefixes = $model->getNamespacePrefixes();
        foreach ($prefixes as $prefix => $namespace) {
            $query = 'PREFIX ' . $prefix . ': <' . $namespace . '>' . PHP_EOL . $query;
        }

        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = Erfurt_Sparql_SimpleQuery::initWithString($query);
        return $model->sparqlQuery($query);
    }

    /**
     * count
     *
     * @param string modelIri
     * @param string whereSpec
     * @param string countSpec
     * @return string
     */
    public function count($modelIri, $whereSpec = '{?s ?p ?o}', $countSpec = '?s ?p ?o')
    {
        return $this->store->countWhereMatches($modelIri, $whereSpec, $countSpec);
    }


    /**
     * add
     *
     * @param string $modelIri
     * @param string $inputModel
     * @return bool
     */
    public function add($modelIri, $inputModel)
    {
        $model = $this->store->getModel($modelIri);
        $model->addMultipleStatements($inputModel);

        return true;
    }

    /**
     * create a new knowledge base
     *
     * @param string $modelIri
     * @return bool
     */
    public function create($modelIri)
    {
        $this->store->getNewModel($modelIri);
        return true;
    }

    /**
     * drop an existing knowledge base
     *
     * @param string $modelIri
     * @return bool
     */
    public function drop($modelIri)
    {
        $this->store->deleteModel($modelIri);
        return true;
    }


}

