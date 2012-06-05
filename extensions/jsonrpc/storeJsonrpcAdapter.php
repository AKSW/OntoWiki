<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * JSON RPC Class, this wrapper class is for all store RPC calls
 *
 * @category   OntoWiki
 * @package    Extensions_Jsonrpc
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class storeJsonrpcAdapter
{
    private $store = null;
    private $erfurt = null;

    public function __construct()
    {
        $this->store = Erfurt_App::getInstance()->getStore();
        $this->erfurt = Erfurt_App::getInstance();
    }

    /**
     * @desc list modelIris which are readable with the current identity
     * @return array
     */
    public function listModels()
    {
        $models = $this->store->getAvailableModels( true );
        // transform result to one-dim array
        $array = array();
        foreach ($models as $model => $bool) {
            $array[] = $model;
        }
        return $array;
    }

    /**
     * @desc return the name of the backend (e.g. Zend or Virtuoso)
     * @return string
     */
    public function getBackendName()
    {
        return $this->store->getBackendName();
    }

    /**
     * @desc performs a sparql query on the store
     * @param string query
     * @return string
     */
    public function sparql($query = 'SELECT ?resource ?label WHERE {?resource ?prop ?label} LIMIT 5')
    {
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = Erfurt_Sparql_SimpleQuery::initWithString($query);
        return $this->store->sparqlQuery($query);
    }

    /**
     * @desc returns the label of the current identity
     * @return string
     */
    public function getIdentity()
    {
        return $this->erfurt->getAuth()->getIdentity()->getUsername();
    }


}

