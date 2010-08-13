<?php

/**
 * JSON RPC Class, this wrapper class is for all Evolution Engine RPC calls.
 *
 * @category	OntoWiki
 * @package		extensions_components_jsonrpc
 * @copyright	Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license		http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author		Marvin Frommhold
 */

require_once 'extensions/components/patternmanager/classes/PatternEngine.php';
require_once 'extensions/components/patternmanager/classes/ComplexPattern.php';
require_once 'extensions/components/patternmanager/classes/BasicPattern.php';
require_once 'extensions/components/patternmanager/classes/PatternFunction.php';

class evolutionJsonrpcWrapper
{

	private $owApp = null;
	private $engine = null;

	public function __construct() {

		$this->owApp = OntoWiki::getInstance();

		$patternManagerConfig = $this->owApp->componentManager->getComponentPrivateConfig("patternmanager");

		$this->engine = new PatternEngine();
		$this->engine->setConfig($patternManagerConfig);
		$this->engine->setBackend($this->owApp->erfurt);
	}

	/**
	 * @desc Executes the given Evolution pattern (in JSON format).
	 * @param params		the POST data parameters (structure: [{"pattern":"foo","graph":"foo","variables":{"var1":"foo","var2":"foo"}}], variables is not mandatory)
	 * @return string
	 * @throws Exception
	 */
	public function execPattern($params) {

		// get pattern
		if ( !array_key_exists("pattern", $params)) {

			throw new Erfurt_Exception("No pattern given!");
		}
		$pattern = urldecode($params['pattern']);

		// get graph
		if ( !array_key_exists("graph", $params) ) {

			throw new Erfurt_Exception("No graph given!");
		}
		$graph = $params["graph"];

		// get variables
		$variables = array();
		if ( array_key_exists("variables", $params) ) {

			$variables = $params["variables"];
		}

		// set graph
		$this->engine->setDefaultGraph($graph);

		// create pattern
		$complexPattern = new ComplexPattern();
		// load pattern from json string
		$complexPattern->fromArray($pattern, true);

		// bound variables
 		$unboundVariables = $complexPattern->getVariables(false);

 		if (!empty($variables) && is_array($variables)) {

			foreach ($variables as $name => $value) {
				unset($unboundVariables[$name]);
				$complexPattern->bindVariable($name,$value);
			}

			// process the pattern
			$this->engine->processPattern($complexPattern);
        }

		return "Success";
	}
}

?>

