<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * JSON RPC Class, this wrapper class is for all meta RPC calls
 *
 * @category   OntoWiki
 * @package    Extensions_Jsonrpc
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class MetaJsonrpcAdapter
{
    private $_store = null;
    private $_erfurt = null;

    private $_server = array(
        'meta'      => 'methods to query the json service itself',
        'store'     => 'methods to manipulate and query the store',
        'model'     => 'methods to manipulate and query a specific model',
        'resource'  => 'methods to manipulate and query a specific resource',
        //'evolution' => 'methods to manage and use the evolution engine',
    );

    public function __construct()
    {
        $this->_store  = Erfurt_App::getInstance()->getStore();
        $this->_erfurt = Erfurt_App::getInstance();
    }

    /**
     * @desc lists all jsonrpc server from the wiki instance
     * @return array
     */
    public function listServer()
    {
        $returnArray = array();
        foreach ($this->_server as $_server => $description) {
            $returnArray[] = array(
                'name'        => $_server,
                'description' => $description,
            );
        }

        return $returnArray;
    }

    /*
     * from: http://de3.php.net/manual/en/class.reflectionmethod.php
     * I have written a function which returns the value of a given DocComment tag.
     */
    protected function getDocComment($string, $tag = '')
    {
        if (empty($tag)) {
            return $string;
        }

        $matches = array();
        preg_match("/" . $tag . "(.*)(\\r\\n|\\r|\\n)/U", $string, $matches);

        if (isset($matches[1])) {
            return trim($matches[1]);
        } else {
            return false;
        }
    }

    /**
     * @desc lists all remote procedures from a specific jsonrpc server
     *
     * @param string server
     *
     * @return array
     */
    public function listProcedures($_server)
    {
        $classname = ucfirst($_server) . 'JsonrpcAdapter';
        @include_once $classname . '.php';
        if (class_exists($classname)) {
            $reflectionClass = new ReflectionClass($classname);
            // get only public functions
            $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
            $returnArray       = array();
            foreach ($reflectionMethods as $method) {
                $methodName        = $method->name;
                $methodDescription = $this->getDocComment($method, $tag = '@desc');
                // we return only methods with a descriptions
                if ($methodDescription) {
                    $returnArray[] = array(
                        'name'        => $_server . ':' . $methodName,
                        'description' => $methodDescription,
                    );
                }
            }
            ksort($returnArray);

            return $returnArray;
        } else {
            throw new Erfurt_Exception('Error: Server ' . $_server . ' does not exist.');
        }
    }

    /**
     * @desc lists all remote procedures from ALL jsonrpc server
     * @return array
     */
    public function listAllProcedures()
    {
        $procedures = array();
        foreach ($this->_server as $_server => $desc) {
            $proceduresOfServer = self::listProcedures($_server);
            $procedures         = array_merge($procedures, $proceduresOfServer);
        }

        return $procedures;
    }
}
