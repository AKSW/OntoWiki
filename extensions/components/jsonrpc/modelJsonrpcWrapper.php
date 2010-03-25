<?php
/**
 * JSON RPC Class, this wrapper class is for all model RPC calls
 *
 * @category   OntoWiki
 * @package    extensions_components_jsonrpc
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
     * @desc exports a model as rdf/xml
     * @param string modelIri
     * @return string
     */
    public function export($modelIri)
    {
        return $this->store->exportRdf($modelIri);
    }

    /**
     * @desc performs a sparql query on the model
     * @param string modelIri
     * @param string query
     * @return string
     */
    public function sparql($modelIri, $query = 'SELECT DISTINCT ?resource ?label WHERE {?resource rdfs:label ?label} LIMIT 5')
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
     * @desc counts the number of statements of a model
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
     * @desc add all input statements to the model
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
     * @desc create a new knowledge base
     * @param string $modelIri
     * @return bool
     */
    public function create($modelIri)
    {
        $this->store->getNewModel($modelIri);
        return true;
    }

    /**
     * @desc drop an existing knowledge base
     * @param string $modelIri
     * @return bool
     */
    public function drop($modelIri)
    {
        $this->store->deleteModel($modelIri);
        return true;
    }


}

