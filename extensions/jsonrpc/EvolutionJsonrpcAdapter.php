<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

$ep = OntoWiki::getInstance()->extensionManager->getExtensionPath();
require_once $ep . 'patternmanager/classes/PatternEngine.php';
require_once $ep . 'patternmanager/classes/ComplexPattern.php';
require_once $ep . 'patternmanager/classes/BasicPattern.php';
require_once $ep . 'patternmanager/classes/PatternFunction.php';
unset($ep);

/**
 * JSON RPC Class, this wrapper class is for all Evolution Engine RPC calls.
 *
 * @category    OntoWiki
 * @package     Extensions_Jsonrpc
 * @copyright   Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license     http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author      Marvin Frommhold
 */
class EvolutionJsonrpcAdapter
{

    private $_owApp = null;
    private $_engine = null;

    public function __construct()
    {
        $this->_owApp = OntoWiki::getInstance();

        $patternManagerConfig = $this->_owApp->componentManager->getComponentPrivateConfig("patternmanager");

        $this->_engine = new PatternEngine();
        $this->_engine->setConfig($patternManagerConfig);
        $this->_engine->setBackend($this->_owApp->erfurt);
    }

    /**
     * @desc Executes the given Evolution pattern (in JSON format).
     *
     * @param params the POST data parameters (structure: [{"pattern":"foo","graph":"foo",
     *        "variables":{"var1":"foo","var2":"foo"}}], variables is not mandatory)
     *
     * @return string
     * @throws Exception
     */
    public function execPattern($params)
    {
        // get pattern
        if (!array_key_exists("pattern", $params)) {

            throw new Erfurt_Exception("No pattern given!");
        }
        $pattern = urldecode($params['pattern']);

        // get graph
        if (!array_key_exists("graph", $params)) {

            throw new Erfurt_Exception("No graph given!");
        }
        $graph = $params["graph"];

        // get variables
        $variables = array();
        if (array_key_exists("variables", $params)) {

            $variables = $params["variables"];
        }

        // set graph
        $this->_engine->setDefaultGraph($graph);

        // create pattern
        $complexPattern = new ComplexPattern();
        // load pattern from json string
        $complexPattern->fromArray($pattern, true);

        // check for errors while decoding json pattern
        switch (json_last_error()) {

            case JSON_ERROR_DEPTH:
                throw new Erfurt_Exception("JSON error - maximum stack depth exceeded.");
            case JSON_ERROR_CTRL_CHAR:
                throw new Erfurt_Exception("JSON error - unexpected control character found.");
            case JSON_ERROR_SYNTAX:
                throw new Erfurt_Exception("JSON error - syntax error, malformed JSON.");
            case JSON_ERROR_NONE:
                break;
        }

        // bound variables
        $unboundVariables = $complexPattern->getVariables(false);

        foreach ($variables as $name => $value) {
            unset($unboundVariables[$name]);
            $complexPattern->bindVariable($name, $value);
        }

        // process the pattern
        $this->_engine->processPattern($complexPattern);

        return true;
    }
}
