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
 * @package    OntoWiki_extensions_components_jsonrpc
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
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
            $this->server->handle();
            return;
        } else {
            $this->_response->setRawHeader('HTTP/1.0 404 Not Found');
            echo '400 Not Found - The given JSONRPC Server has corresponding wrapper class.';
            exit;
        }
    }
}

