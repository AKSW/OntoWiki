<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * JSON RPC Class, this wrapper class is for all model RPC calls
 *
 * @category   OntoWiki
 * @package    Extensions_Jsonrpc
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class modelJsonrpcAdapter
{
    private $store = null;
    private $erfurt = null;

    public function __construct()
    {
        $this->store = Erfurt_App::getInstance()->getStore();
        $this->erfurt = Erfurt_App::getInstance();
        $this->config = $this->erfurt->getConfig();
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
     * @desc List all prefix-namespace mappings
     * @param string modelIri
     * @return array
     */
    public function getPrefixes($modelIri)
    {
        $model = $this->store->getModel($modelIri);
        $prefixes = $model->getNamespacePrefixes();
        $return = array();
        foreach ($prefixes as $index => $key) {
            $return[] = array('prefix' => $index, 'namespace' => $key);
        }
        return $return;
    }

    /**
     * @desc Add a prefix to namespace mapping
     * @param string modelIri
     * @param string prefix
     * @param string namespace uri
     * @return bool
     */
    public function addPrefix($modelIri, $prefix, $namespace = false)
    {
        $model = $this->store->getModel($modelIri);
        $prefixes = $model->getNamespacePrefixes();
        if (isset($prefixes[$prefix])) {
            return false;
        } else {
            // try to use a standard namespace if no parameter is given
            if ($namespace == false) {
                $standards = isset($this->config->namespaces) ? $this->config->namespaces->toArray() : array();
                if (isset($standards[$prefix])) {
                    $namespace = $standards[$prefix];
                } else {
                    return false;
                }
            }
            $model->addNamespacePrefix($prefix, $namespace);
            return true;
        }
    }

    /**
     * @desc delete a prefix to namespace mapping
     * @param string modelIri
     * @param string prefix
     * @return bool
     */
    public function deletePrefix($modelIri, $prefix = 'rdf')
    {
        $model = $this->store->getModel($modelIri);
        $prefixes = $model->getNamespacePrefixes();
        if (!isset($prefixes[$prefix])) {
            return false;
        } else {
            $model->deleteNamespacePrefix($prefix);
            return true;
        }
    }

    /**
     * @desc counts the number of statements of a model
     * @param string modelIri
     * @param string whereSpec
     * @param string countSpec
     * @return string
     */
    public function count($modelIri, $whereSpec = '{?s ?p ?o}', $countSpec = '*')
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
        $versioning = $this->erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['type']         = 80201;
        $actionSpec['modeluri']     = (string) $model;
        $actionSpec['resourceuri']  = (string) $model;

        $versioning->startAction($actionSpec);
        $model->addMultipleStatements($inputModel);
        $versioning->endAction();

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

