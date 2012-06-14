<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * semantic pingback controller
 *
 * @category   OntoWiki
 * @package    Extensions_Pingback
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @author     Sebastian Tramp <mail@sebastian.tramp.name>
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @author     Natanael Arndt <arndtn@gmail.com>
 */
class PingbackController extends OntoWiki_Controller_Component
{

    protected $_targetGraph = null;
    protected $_sourceRdf = null;
    private $_dbChecked = false;

    /**
     * receive a ping
     */
    public function pingAction()
    {
        $this->_logInfo('Pingback Server Init.');

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $this->_owApp->appendMessage(
            new OntoWiki_Message('Ping received.', OntoWiki_Message::INFO)
        );

        if (isset($_POST['source']) && isset($_POST['target'])) {
            // Simplified Semantic Pingback
            echo $this->ping($_POST['source'], $_POST['target']);
            return;
        } else {
            // Create XML RPC Server
            $server = new Zend_XmlRpc_Server();
            $server->setClass($this, 'pingback');

            // Let the server handle the RPC calls.
            $response = $this->getResponse();
            $response->setBody($server->handle());
            return;
        }
    }

    /**
     * receive a ping API
     *
     * @param string $sourceUri The source URI
     * @param string $targetUri The target URI
     *
     * @return integer An integer (fault) code
     */
    public function ping($sourceUri, $targetUri)
    {
        $this->_logInfo('Method ping was called.');

        // Is $targetUri a valid linked data resource in this namespace?
        if (!$this->_checkTargetExists($targetUri)) {
            $this->_logError('0x0021');
            return 0x0021;
        }

        $config = $this->_privateConfig;
        $foundPingbackTriplesGraph = array();

        // 1. Try to dereference the source URI as RDF/XML, N3, Truples, Turtle
        $foundPingbackTriplesGraph = $this->_getResourceFromWrapper($sourceUri, $targetUri, 'Linkeddata');

        // 2. If nothing was found, try to use as RDFa service
        if (((boolean) $config->rdfa->enabled) && (count($foundPingbackTriplesGraph) === 0)) {
            $foundPingbackTriplesGraph = $this->_getResourceFromWrapper($sourceUri, $targetUri, 'Rdfa');
        }

        $foundPingbackTriples = array();
        foreach ($foundPingbackTriplesGraph as $s => $predicates) {
            foreach ($predicates as $p => $objects) {
                foreach ($objects as $o) {
                    $foundPingbackTriples[] = array(
                        's' => $s,
                        'p' => $p,
                        'o' => $o['value']
                    );
                }
            }
        }

        $versioning = Erfurt_App::getInstance()->getVersioning();
        $versioning->startAction(
            array(
                'type' => '9000',
                'modeluri' => $this->_targetGraph,
                'resourceuri' => $sourceUri
            )
        );

        // 3. If still nothing was found, try to find a link in the html
        if (count($foundPingbackTriples) === 0) {
            $client = Erfurt_App::getInstance()->getHttpClient(
                $sourceUri,
                array(
                    'maxredirects' => 10,
                    'timeout' => 30
                )
            );

            try {
                $response = $client->request();
            } catch (Exception $e) {
                $this->_logError($e->getMessage());
                $versioning->endAction();
                return 0x0000;
            }
            if ($response->getStatus() === 200) {
                $htmlDoc = new DOMDocument();
                $result = @$htmlDoc->loadHtml($response->getBody());
                $aElements = $htmlDoc->getElementsByTagName('a');

                foreach ($aElements as $aElem) {
                    $a = $aElem->getAttribute('href');
                    if (strtolower($a) === $targetUri) {
                        $foundPingbackTriples[] = array(
                            's' => $sourceUri,
                            'p' => $config->generic_relation,
                            'o' => $targetUri
                        );
                        break;
                    }
                }
            } else {
                $this->_logError('0x0010');
                $versioning->endAction();
                return 0x0010;
            }
        }

        // 4. If still nothing was found, the sourceUri does not contain any link to targetUri
        if (count($foundPingbackTriples) === 0) {
            // Remove all existing pingback triples from that sourceUri.
            $removed = $this->_deleteInvalidPingbacks($sourceUri, $targetUri);

            if (!$removed) {
                $this->_logError('0x0011');
                $versioning->endAction();
                return 0x0011;
            } else {
                $this->_logInfo('All existing Pingbacks removed.');
                $versioning->endAction();
                return 'Existing Pingbacks have been removed.';
            }
        }

        // 6. Iterate through pingback triples candidates and add those, who are not already registered.
        $added = false;
        foreach ($foundPingbackTriples as $triple) {
            if (!$this->_pingbackExists($triple['s'], $triple['p'], $triple['o'])) {
                $this->_addPingback($triple['s'], $triple['p'], $triple['o']);
                $added = true;
            }
        }

        // Remove all existing pingbacks from that source uri, that were not found this time.
        $removed = $this->_deleteInvalidPingbacks($sourceUri, $targetUri, $foundPingbackTriples);

        if (!$added && !$removed) {
            $this->_logError('0x0030');
            $versioning->endAction();
            return 0x0030;
        }

        $this->_logInfo('Pingback registered.');
        $versioning->endAction();

        return 'Pingback has been registered or updated... Keep spinning the Data Web ;-)';
    }

    protected function _addPingback($s, $p, $o)
    {
        if ($this->_targetGraph === null) {
            return false;
        }

        $store = Erfurt_App::getInstance()->getStore();

        $sql = 'INSERT INTO ow_pingback_pingbacks (source, target, relation) '
            . 'VALUES ("' . $s . '", "' . $o . '", "' . $p . '")';
        $this->_query($sql);

        $store->addStatement(
            $this->_targetGraph, $s, $p, array('value' => $o, 'type' => 'uri'), false
        );

        if ($this->_sourceRdf !== null) {
            foreach ($this->_sourceRdf as $prop => $oArray) {
                $titleProps = $this->_privateConfig->title_properties->toArray();
                if (in_array($prop, $titleProps)) {
                    $store->addStatement(
                        $this->_targetGraph, $s, $prop, $oArray[0], false
                    );
                    break; // only one title
                }
            }
        }

        $event = new Erfurt_Event('onPingReceived');
        $event->s = $s;
        $event->p = $p;
        $event->o = $o;
        $event->trigger();

        return true;
    }

    protected function _checkTargetExists($targetUri)
    {
        if ($this->_targetGraph == null) {
            $event = new Erfurt_Event('onNeedsGraphForLinkedDataUri');
            $event->uri = $targetUri;

            $graph = $event->trigger();
            if ($graph) {
                $this->_targetGraph = $graph;
                // If we get a target graph from linked data plugin, we no that the target uri exists, since
                // getGraphsUsingResource ist used by store.
                return true;
            } else {
                return false;
            }
        }
    }

    function _deleteInvalidPingbacks($sourceUri, $targetUri, $foundPingbackTriples = array())
    {
        $store = Erfurt_App::getInstance()->getStore();

        $sql = 'SELECT * FROM ow_pingback_pingbacks WHERE source="' . $sourceUri . '" AND target="' . $targetUri . '"';
        $result = $this->_query($sql);

        $removed = false;
        if ($result !== false) {
            foreach ($result as $row) {
                $found = false;
                foreach ($foundPingbackTriples as $triple) {
                    if ($triple['p'] === $row['relation']) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $sql = 'DELETE FROM ow_pingback_pingbacks WHERE id=' . $row['id'];
                    $this->_query($sql);

                    $oSpec = array(
                        'value' => $targetUri,
                        'type' => 'uri'
                    );

                    $store->deleteMatchingStatements(
                        $this->_targetGraph,
                        $sourceUri,
                        $row['relation'],
                        $oSpec,
                        array('use_ac' => false)
                    );
                    $removed = true;
                }
            }
        }

        return $removed;
    }

    protected function _determineInverseProperty($propertyUri)
    {
        $client = Erfurt_App::getInstance()->getHttpClient(
            $propertyUri,
            array(
                'maxredirects' => 10,
                'timeout' => 30
            )
        );
        $client->setHeaders('Accept', 'application/rdf+xml');
        try {
            $response = $client->request();
        } catch (Exception $e) {
            return null;
        }
        if ($response->getStatus() === 200) {
            $data = $response->getBody();

            $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdfxml');
            try {
                $result = $parser->parse($data, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
            } catch (Exception $e) {
                return null;
            }

            if (isset($result[$propertyUri])) {
                $pArray = $result[$propertyUri];
                if (isset($pArray['http://www.w3.org/2002/07/owl#inverseOf'])) {
                    $oArray = $pArray['http://www.w3.org/2002/07/owl#inverseOf'];
                    return $oArray[0]['value'];
                }
            }

            return null;
        }
    }

    private function _getResourceFromWrapper($sourceUri, $targetUri, $wrapperName = 'Linkeddata')
    {
        $r = new Erfurt_Rdf_Resource($sourceUri);

        // Try to instanciate the requested wrapper
        new Erfurt_Wrapper_Manager();
        $wrapper = Erfurt_Wrapper_Registry::getInstance()->getWrapperInstance($wrapperName);

        $wrapperResult = null;
        $wrapperResult = $wrapper->run($r, null, true);

        $newStatements = null;
        if ($wrapperResult === false) {
            // IMPORT_WRAPPER_NOT_AVAILABLE;
        } else if (is_array($wrapperResult)) {
            $newStatements = $wrapperResult['add'];
            // TODO make sure to only import the specified resource
            $newModel = new Erfurt_Rdf_MemoryModel($newStatements);
            $newStatements = array();
            $object = array('type' => 'uri', 'value' => $targetUri);
            $newStatements = $newModel->getP($sourceUri, $object);
        } else {
            // IMPORT_WRAPPER_ERR;
        }

        return $newStatements;
    }

    protected function _logError($msg)
    {
        $owApp = OntoWiki::getInstance();
        $logger = $owApp->logger;

        if (is_array($msg)) {
            $logger->debug('Pingback Component Error: ' . var_export($msg, true));
        } else {
            $logger->debug('Pingback Component Error: ' . $msg);
        }
    }

    protected function _logInfo($msg)
    {
        $owApp = OntoWiki::getInstance();
        $logger = $owApp->logger;

        if (is_array($msg)) {
            $logger->debug('Pingback Component Info: ' . var_export($msg, true));
        } else {
            $logger->debug('Pingback Component Info: ' . $msg);
        }
    }

    protected function _pingbackExists($s, $p, $o)
    {
        $sql = 'SELECT * FROM ow_pingback_pingbacks '
            . 'WHERE source="' . $s . '" AND target="' . $o . '" AND relation="' . $p . '" '
            . 'LIMIT 1';
        $result = $this->_query($sql);
        if (is_array($result) && (count($result) === 1)) {
            return true;
        }

        return false;
    }

    private function _checkDb()
    {
        if ($this->_dbChecked) {
            return;
        }

        $store = Erfurt_App::getInstance()->getStore();
        $sql = 'SELECT * FROM ow_pingback_pingbacks LIMIT 1';

        try {
            $result = $store->sqlQuery($sql);
        } catch (Exception $e) {
            $this->_createTable();
        }

        $this->_dbChecked = true;
    }

    private function _createTable()
    {
        $store = Erfurt_App::getInstance()->getStore();

        $sql = 'CREATE TABLE IF NOT EXISTS ow_pingback_pingbacks (
            id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            source    VARCHAR(255) COLLATE ascii_bin NOT NULL,
            target    VARCHAR(255) COLLATE ascii_bin NOT NULL,
            relation  VARCHAR(255) COLLATE ascii_bin NOT NULL
        );';

        return $this->_query($sql, false);
    }

    protected function _query($sql, $withCheck = true)
    {
        if ($withCheck) {
            $this->_checkDb();
        }

        $store = Erfurt_App::getInstance()->getStore();

        try {
            $result = $store->sqlQuery($sql);
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }
}
