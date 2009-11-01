<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'Zend/Json/Server.php';

/**
 * JSON RPC Server (http://json-rpc.org/) Controller for OntoWiki
 *
 * test it e.g. with:
 * wget -q -O - --post-data='{"method": "count", "params": {"uri": "yourmodeluri"}, "id": 33}' "ONTOWIKI/jsonrpc/request"
 *
 * @category   OntoWiki
 * @package    extensions_components_jsonrpc
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class JsonrpcController extends OntoWiki_Controller_Component
{
    private $server = null;

    public function init()
    {
        parent::init();
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        $this->server = new Zend_Json_Server();
    }

    public function __call($method, $args)
    {
        $classname = str_replace  ( 'Action', '', $method) . 'JsonrpcWrapper';
        @include_once $classname.'.php';
        if (class_exists($classname)) {
            $this->server->setClass($classname);

            // JSONRPC Autodiscovery: http://framework.zend.com/manual/en/zend.json.server.html
            if ('GET' == $_SERVER['REQUEST_METHOD']) {
                // Indicate the URL endpoint, and the JSON-RPC version used:
                $this->server->setTarget('/json-rpc.php')
                       ->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2);

                // TODO: Add a description to each service method
                // http://framework.zend.com/manual/de/zend.reflection.examples.html

                // Grab the SMD
                $smd = $this->server->getServiceMap();

                // Set Dojo compatibility:
                #$smd->setDojoCompatible(true);

                // Return the SMD to the client
                header('Content-Type: application/json');
                echo $smd;
                return;
            }

            $this->server->handle();
            return;
        } else {
            $this->_response->setRawHeader('HTTP/1.0 404 Not Found');
            echo '400 Not Found - The given JSONRPC Server has corresponding wrapper class.';
            exit;
        }
    }
}

