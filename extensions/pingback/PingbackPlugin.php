<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The pingback plugin is used on different events
 *
 * @category   OntoWiki
 * @package    Extensions_Pingback
 * @author     Philipp Frischmuth
 * @author     Sebastian Tramp <mail@sebastian.tramp.name>
 */

class PingbackPlugin extends OntoWiki_Plugin
{

    public function onBeforeLinkedDataRedirect($event)
    {
        if ($event->response === null) {
            return;
        }
        $response = $event->response;

        $serverUrl = null;
        if (isset($this->_privateConfig->external_pingback_server)) {
            $serverUrl = $this->_privateConfig->external_pingback_server;
        } else {
            $owApp     = OntoWiki::getInstance();
            $serverUrl = $owApp->config->urlBase . 'pingback/ping/';
        }

        $url = $serverUrl;
        $response->setHeader('X-Pingback', $url, true);
    }

    public function onAfterInitController($event)
    {
        if ($event->response === null) {
            return;
        }
        $response = $event->response;

        $serverUrl = null;
        if (isset($this->_privateConfig->external_pingback_server)) {
            $serverUrl = $this->_privateConfig->external_pingback_server;
        } else {
            $owApp     = OntoWiki::getInstance();
            $serverUrl = $owApp->config->urlBase . 'pingback/ping/';
        }

        $url = $serverUrl;
        $response->setHeader('X-Pingback', $url, true);
    }

    public function onAddStatement($event)
    {
        // Check the environement... e.g. Linked Data plugin needs to be enabled.
        if (!$this->_check()) {
            return;
        }

        $s = $event->statement['subject'];
        $p = $event->statement['predicate'];
        $o = $event->statement['object'];
        $this->_checkAndPingback($s, $p, $o);
    }

    public function onAddMultipleStatements($event)
    {
        // Check the environement... e.g. Linked Data plugin needs to be enabled.
        if (!$this->_check()) {
            return;
        }
        // Parse SPO array.
        $statements = $event->statements;
        foreach ($statements as $subject => $predicatesArray) {
            foreach ($predicatesArray as $predicate => $objectsArray) {
                foreach ($objectsArray as $object) {
                    $this->_checkAndPingback($subject, $predicate, $object);
                }
            }
        }
    }

    public function onDeleteMultipleStatements($event)
    {
        // If pingOnDelete is configured, we also ping on statement delete, otherwise we skip.
        if (
            !isset($this->_privateConfig->pingOnDelete)
            || ((boolean)$this->_privateConfig->pingOnDelete === false)
        ) {

            return;
        }

        // Check the environement... e.g. Linked Data plugin needs to be enabled.
        if (!$this->_check()) {
            return;
        }

        // Parse SPO array.
        foreach ($event->statements as $subject => $predicatesArray) {
            foreach ($predicatesArray as $predicate => $objectsArray) {
                foreach ($objectsArray as $object) {
                    $this->_checkAndPingback($subject, $predicate, $object);
                }
            }
        }
    }

    /*
     * used on export event to add a statement to the export
     */
    public function beforeExportResource($event)
    {
        $owApp = OntoWiki::getInstance();

        // this is the event value if there are other plugins before
        $prevModel = $event->getValue();
        // throw away non memory model values OR use the given one if valid
        if (is_object($prevModel) && get_class($prevModel) == 'Erfurt_Rdf_MemoryModel') {
            $newModel = $prevModel;
        } else {
            $newModel = new Erfurt_Rdf_MemoryModel();
        }

        // prepare the statement URIs
        $subjectUri  = (string)$event->resource;
        $propertyUri = 'http://purl.org/net/pingback/to';
        $objectUri   = $owApp->config->urlBase . 'pingback/ping/';

        $newModel->addRelation(
            $subjectUri, $propertyUri, $objectUri
        );

        return $newModel;
    }

    protected function _check()
    {
        // Check, whether linked data plugin is enabled.
        $owApp         = OntoWiki::getInstance();
        $pluginManager = $owApp->erfurt->getPluginManager(false);
        if (!$pluginManager->isPluginEnabled('linkeddata')) {
            $this->_logInfo('Linked Data plugin disabled, Pingbacks are not allowed.');

            return false;
        }

        return true;
    }

    protected function _checkAndPingback($subject, $predicate, $object)
    {
        // If at least one ping_properties value is set in config, we only ping for matching predicates.
        if (isset($this->_privateConfig->ping_properties)) {
            $props = (array)$this->_privateConfig->ping_properties;
            if (!in_array($predicate, $props)) {
                return;
            }
        }

        $owApp   = OntoWiki::getInstance();
        $base    = $owApp->config->urlBase;
        $baseLen = strlen($base);

        // Object (target) needs to be an external URI
        if ($object['type'] !== 'uri') {
            return;
        } else {
            $targetUri = $object['value'];
            $owApp     = OntoWiki::getInstance();
            $owBase    = $owApp->config->urlBase;

            // Check, whether URI is external.
            if (substr($targetUri, 0, strlen($owBase)) === $owBase) {
                return;
            }

            // Check, whether URI is derefderencable via HTTP.
            if ((substr($object['value'], 0, 7) !== 'http://') && (substr($object['value'], 0, 8) !== 'https://')) {
                return;
            }
        }

        // Source URI (subject) needs to be a (internal) Linked Data resource
        if (!$this->_isLinkedDataUri($subject)) {
            return;
        }

        // All tests passed... send the pingback.
        $this->_sendPingback($subject, $object['value']);
    }

    protected function _discoverPingbackServer($targetUri)
    {
        // 1. Retrieve HTTP-Header and check for X-Pingback
        $headers = self::_get_headers($targetUri);
        if (!is_array($headers)) {
            return null;
        }
        if (isset($headers['X-Pingback'])) {
            if (is_array($headers['X-Pingback'])) {
                $this->_logInfo($headers['X-Pingback'][0]);

                return $headers['X-Pingback'][0];
            }

            $this->_logInfo($headers['X-Pingback']);

            return $headers['X-Pingback'];
        }

        // 2. Check for (X)HTML Link element, if target has content type text/html
        // TODO Fetch only the first X bytes...???
        $client = Erfurt_App::getInstance()->getHttpClient(
            $targetUri, array(
                             'maxredirects' => 0,
                             'timeout'      => 3
                        )
        );

        $response = $client->request();
        if ($response->getStatus() === 200) {
            $htmlDoc     = new DOMDocument();
            $result      = @$htmlDoc->loadHtml($response->getBody());
            $relElements = $htmlDoc->getElementsByTagName('link');

            foreach ($relElements as $relElem) {
                $rel = $relElem->getAttribute('rel');
                if (strtolower($rel) === 'pingback') {
                    return $relElem->getAttribute('href');
                }
            }
        }

        // 3. Check RDF/XML
        require_once 'Zend/Http/Client.php';
        $client = Erfurt_App::getInstance()->getHttpClient(
            $targetUri, array(
                             'maxredirects' => 10,
                             'timeout'      => 3
                        )
        );
        $client->setHeaders('Accept', 'application/rdf+xml');

        $response = $client->request();
        if ($response->getStatus() === 200) {
            $rdfString = $response->getBody();

            $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdfxml');
            try {
                $result = $parser->parse($rdfString, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
            } catch (Exception $e) {
                $this->_logError($e->getMessage());

                return null;
            }

            if (isset($result[$targetUri])) {
                $pArray = $result[$targetUri];

                foreach ($pArray as $p => $oArray) {
                    if ($p === 'http://purl.org/net/pingback/service') {
                        return $oArray[0]['value'];
                    }
                }
            }
        }

        return null;
    }

    protected function _logError($msg)
    {
        if (is_array($msg)) {
            $this->_log('Pingback Plugin Error - ' . var_export($msg, true));
        } else {
            $this->_log('Pingback Plugin Error - ' . $msg);
        }
    }

    protected function _logInfo($msg)
    {
        if (is_array($msg)) {
            $this->_log('Pingback Plugin Info - ' . var_export($msg, true));
        } else {
            $this->_log('Pingback Plugin Info - ' . $msg);
        }
    }

    protected function _sendPingback($sourceUri, $targetUri)
    {
        $pingbackServiceUrl = $this->_discoverPingbackServer($targetUri);
        if ($pingbackServiceUrl === null) {
            $this->_logInfo('No Pingback server discovered');

            return;
        }

        $xml = '<?xml version="1.0"?><methodCall><methodName>pingback.ping</methodName><params>' .
            '<param><value><string>' . $sourceUri . '</string></value></param>' .
            '<param><value><string>' . $targetUri . '</string></value></param>' .
            '</params></methodCall>';

        // TODO without curl? with zend?
        $rq = curl_init();
        curl_setopt($rq, CURLOPT_URL, $pingbackServiceUrl);
        curl_setopt($rq, CURLOPT_POST, 1);
        curl_setopt($rq, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($rq, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($rq, CURLOPT_RETURNTRANSFER, true);
        $synchronous = false;
        if (!$synchronous) { //a timeout of one ms is like ignoring the http response -> asynchonous
            curl_setopt($rq, CURLOPT_TIMEOUT, 1);
            $this->_logInfo(
                'Now sending Pingback (not waiting for response) - ' . $sourceUri . ', ' . $targetUri
            );
        }
        $res = curl_exec($rq);
        if ($synchronous) {
            $this->_logInfo('Pingback Result for (' . $pingbackServiceUrl . ', ' . $xml . ') - ' . $res);
        }
        curl_close($rq);

        return true;
    }

    private function _isLinkedDataUri($uri)
    {
        $event      = new Erfurt_Event('onNeedsLinkedDataUri');
        $event->uri = $uri;

        $result = (bool)$event->trigger();

        return $result;
    }

    private function _log($msg)
    {
        $logger = OntoWiki::getInstance()->getCustomLogger('pingback_plugin');
        $logger->debug($msg);
    }

    /**
     * This method provides and alternative to PHP's get_headers method, which has no timeout.
     * It is inspired by: http://snipplr.com/view/61985/
     */
    private static function _get_headers($url)
    {
        $connection = curl_init();

        $options = array(
            CURLOPT_URL             => $url,
            CURLOPT_NOBODY          => true,
            CURLOPT_HEADER          => true,
            CURLOPT_TIMEOUT         => 10,
            CURLOPT_RETURNTRANSFER  => true
        );

        curl_setopt_array($connection, $options);
        $result = curl_exec($connection);
        curl_close($connection);

        if ($result !== false) {
            $resultArray = explode("\n", $result);

            $header = array();
            foreach ($resultArray as $headerLine) {
                $match = preg_match('#(.*?)\:\s(.*)#', $headerLine, $part);
                if ($match) {
                    $header[$part[1]] = trim($part[2]);
                }
            }

            return $header;
        } else {
            return false;
        }
    }

}
