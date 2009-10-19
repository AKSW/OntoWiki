<?php
/**
 * JSON RPC Class, this wrapper class is for all store RPC calls
 *
 * @package    ontowiki
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */
class storeJsonrpcWrapper
{
    private $store = null;
    private $erfurt = null;

    public function __construct()
    {
        $this->store = Erfurt_App::getInstance()->getStore();
        $this->erfurt = Erfurt_App::getInstance();
    }

    /**
     * getAvailableModels
     *
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
     * getBackendName
     *
     * @return string
     */
    public function getBackendName()
    {
        return $this->store->getBackendName();
    }

    /**
     * sparql
     *
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
     * getIdentity
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->erfurt->getAuth()->getIdentity()->getUsername();
    }


}

