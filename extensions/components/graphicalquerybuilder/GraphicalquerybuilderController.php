<?php
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';

/**
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_graphicalquerybuilder
 * @author     Jonas Brekle <j.brekle.o@gmx.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: GraphicalquerybuilderController.php 
 */
class GraphicalquerybuilderController extends OntoWiki_Controller_Component {
	//uris that are used for saving
	public $baseQueryDbUri = 'http://ns.ontowiki.net/SysOnt/UserQueries/';
	public $saveQueryClassUri = 'http://ns.ontowiki.net/SysOnt/SparqlQuery';
	public $saveQueryNameUri = 'http://purl.org/dc/elements/1.1/title';
	public $saveQueryDateUri = 'http://purl.org/dc/elements/1.1/created';
	public $saveQueryDescriptionUri = 'http://purl.org/dc/elements/1.1/description';
	public $saveQueryModelUri = 'http://ns.ontowiki.net/SysOnt/Model';
	public $saveQueryIdUri = 'http://rdfs.org/sioc/ns#id';
	public $saveQueryNumViewsUri = 'http://rdfs.org/sioc/ns#num_views';
	public $saveQueryCreatorUri = 'http://rdfs.org/sioc/ns#has_creator';
	public $saveQueryGeneratorUri = 'http://ns.ontowiki.net/SysOnt/generator'; // which query builder created this query. is there a better namespace?
	public $saveQueryQueryUri = 'http://ns.ontowiki.net/SysOnt/sparql_code'; // the actual content. is there a better namespace?
	public $saveQueryJsonUri = 'http://ns.ontowiki.net/SysOnt/json_code'; // the actual content. is there a better namespace?

	//specific for graphical query builder
	public $saveQuerySelClassUri = 'http://ns.ontowiki.net/GQB/UserQueries/Pattern/Type';
	public $saveQuerySelClassLabelUri = 'http://ns.ontowiki.net/GQB/UserQueries/Pattern/TypeLabel';
	private $userUri;
	private $userName;
	private $userDbUri;

	/**
	 * Default action. Forwards to get action.
	 */
	public function __call($action, $params) {
		$this->_forward('display', 'graphicalquerybuilder');
	}

	public function init() {

		/*
		    // Action Based Access Control for the Query Builder
		    $owApp = OntoWiki::getInstance();
		    if (!$owApp->erfurt->isActionAllowed('QueryBuilding')){
		      require_once 'Erfurt/Ac/Exception.php';
		      throw new Erfurt_Ac_Exception("You are not allowed to use the Query Builder.");
		    }
		*/
		parent :: init();

		// setup the tabbed navigation
		OntoWiki_Navigation :: reset();
		if (class_exists("QuerybuildingHelper")) {
			OntoWiki_Navigation :: register('listquery', array (
				'controller' => "querybuilding",
				'action' => "listquery",
				'name' => "Saved Queries",
				'position' => 0,
				'active' => true
			));
		}
		OntoWiki_Navigation :: register('queryeditor', array (
			'controller' => "querybuilding",
			'action' => "editor",
			'name' => "Query Editor",
			'position' => 1,
			'active' => true
		));
		if (class_exists("QuerybuilderHelper")) {
			OntoWiki_Navigation :: register('querybuilder', array (
				'controller' => "querybuilder",
				'action' => "manage",
				'name' => "Query Builder ",
				'position' => 2,
				'active' => true
			));
		}

		OntoWiki_Navigation :: register('graphicalquerybuilder', array (
			'controller' => "graphicalquerybuilder",
			'action' => "display",
			'name' => "Graphical Query Builder",
			'position' => 3,
			'active' => true
		));

		OntoWiki_Navigation :: setActive('graphicalquerybuilder', true);

		$user = $this->_erfurt->getAuth()->getIdentity();
		$this->userUri = $user->getUri();
		$this->userName = $user->getUsername();
		$this->userDbUri = $this->baseQueryDbUri . 'user-' . $this->userName . '/';

	}

	public function displayAction() {
		$include_base = $this->_componentUrlBase;
		$this->view->componentUrlBase = $this->_componentUrlBase;
		if ($this->_owApp->selectedModel != null) {
			//stylesheets
			$this->view->headLink()->appendStylesheet($include_base . 'resources/graphicalquerybuilder.css');
			$this->view->headLink()->appendStylesheet($include_base . 'resources/jquery.treeview.css');

			// Stylesheet for printing
			$this->view->headLink()->appendStylesheet($include_base . 'resources/graphicalquerybuilder_print.css', 'print');

			//include utils/libs
			$this->view->headScript()->appendFile($include_base . 'resources/jquery.dump.js');
			$this->view->headScript()->appendFile($include_base . 'resources/jquery.treeview.js');
			$this->view->headScript()->appendFile($include_base . 'resources/jquery.scrollTo-1.4.2-min.js');
			$this->view->headScript()->appendFile($include_base . 'resources/sparql.js');
			$this->view->headScript()->appendFile($include_base . 'resources/raphael.js');
			$this->view->headScript()->appendFile($include_base . 'resources/raphael.gqb.js');

			//generate some js
			$lang = $this->_owApp->config->languages->locale;

			$modelUri = $this->_owApp->selectedModel->getModelIri();
			$this->view->headScript()->appendScript("var GQB = {};" .
			"GQB.selectedModelUri = \"" . $modelUri . "\";\n" .
			"GQB.userDbUri = \"" . $this->userDbUri . "\";\n" .
			"GQB.patternClassName = \"" . $this->saveQueryClassUri . "\";\n" .
			"GQB.patternJson = \"" . $this->saveQueryJsonUri . "\";\n" .
			"GQB.patternName = \"" . $this->saveQueryNameUri . "\";\n" .
			"GQB.patternDesc = \"" . $this->saveQueryDescriptionUri . "\";\n" .
			"GQB.patternType = \"" . $this->saveQuerySelClassUri . "\";\n" .
			"GQB.patternTypeLabel = \"" . $this->saveQuerySelClassLabelUri . "\";\n" .
			"GQB.patternDate = \"" . $this->saveQueryDateUri . "\";\n" .
			"GQB.patternQuery = \"" . $this->saveQueryQueryUri . "\";\n" .
			"GQB.currLang = \"" . $lang . "\"; \n" .
			"GQB.supportedLangs = [ \"en\", \"de\" ];");

			$open = $this->_request->getParam('open', '');
			if ($open == "true") {
				$this->view->headScript()->appendScript("GQB.toload = \"" .
				$this->_request->getParam('queryuri', '') . "\";");
			}
			//include the js code
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.translations.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.controller.js');

			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.model.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.model.restrictions.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.model.GQBClass.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.model.GQBQueryPattern.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.model.GQBModel.js');

			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.view.GQBView.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.view.GQBView.restrictions.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.view.GQBView.init.js');
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.view.GQBViewPattern.js');

			//start
			$this->view->headScript()->appendFile($include_base . 'resources/graphicalquerybuilder.js');
		} else {
			//no model selected error
			throw new OntoWiki_Exception("no model selected - maybe your session timed out");
		}
		$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Graphical Query Builder'));
	}

	public function displayprototypeAction() {
		$include_base = $this->_componentUrlBase;
		$this->view->headLink()->appendStylesheet($include_base . 'resources/prototype/graphicalquerybuilder.prototype.css');

		$this->view->headScript()->appendFile($include_base . 'resources/jquery.dump.js');
		$this->view->headScript()->appendFile($include_base . 'resources/sparql.js');

		$this->view->headScript()->appendFile($include_base . 'resources/prototype/graphicalquerybuilder.classdefs.restrictions.prototype.js');
		$this->view->headScript()->appendFile($include_base . 'resources/prototype/graphicalquerybuilder.classdefs.prototype.js');
		$this->view->headScript()->appendFile($include_base . 'resources/prototype/graphicalquerybuilder.prototype.js');

		$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Graphical Query Builder (Prototype)'));
	}

	// usage: "graphicalquerybuilder/getquerysize/?query=<QUERY>"
	//       for any valid SPARQL-Query <QUERY>
	// returns the number of elements returned by the query
	public function getquerysizeAction() {
		$store = OntoWiki::getInstance()->erfurt->getStore();
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/plain');
		$count = 0;

		// fetch params
		$queryString = $this->_request->getParam('query', '');
		if (get_magic_quotes_gpc()) {
			$queryString = stripslashes($queryString);
		}
		$defaultGraph = $this->_request->getParam('default-graph-uri', null);
		$namedGraph = $this->_request->getParam('named-graph-uri', null);

		if (!empty ($queryString)) {
			require_once 'Erfurt/Sparql/SimpleQuery.php';
			$query = Erfurt_Sparql_SimpleQuery :: initWithString($queryString);

			// overwrite query-specidfied dataset with protocoll-specified dataset
			if (null !== $defaultGraph) {
				$query->setFrom((array) $defaultGraph);
			}
			if (null !== $namedGraph) {
				$query->setFromNamed((array) $namedGraph);
			}

			// check graph availability
			require_once 'Erfurt/App.php';
			$ac = Erfurt_App :: getInstance()->getAc();
			foreach (array_merge($query->getFrom(), $query->getFromNamed()) as $graphUri) {
				if (!$ac->isModelAllowed('view', $graphUri)) {
					$count = -3;
					$response->setBody($count);
					$response->sendResponse();
					exit;
				}
			}

			try {
				$result = $store->sparqlQuery($query, array (
					'result_format' => 'json'
				));
				$resarray = json_decode($result);
				$count = count($resarray-> {
					'bindings' });
			} catch (Exception $e) {
				$count = -2;
				$response->setBody($count);
				$response->sendResponse();
				exit;
			}

		} else {
			$count = -1;
		}

		$response->setBody($count);
		$response->sendResponse();
		exit;
	}

	public function getresulttableAction() {

		// stripping automatic escaped chars
		$params = array ();
		foreach ($this->_request->getParams() as $key => $param) {
			if (get_magic_quotes_gpc()) {
				$params[$key] = stripslashes($param);
			} else {
				$params[$key] = $param;
			}
		}

		$now = microtime(true);

		$query = $this->_request->getParam('query', '');
		$queryObj = Erfurt_Sparql_SimpleQuery :: initWithString($query);
		$store = OntoWiki::getInstance()->erfurt->getStore();
		$data = $store->sparqlQuery($queryObj);

		$time = round((microtime(true) - $now) * 1000) . " msec needed";

		// disabling layout and template as we make no use of these
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();

		// now rendering
		$this->view->data = $data;
		$this->view->cssid = "gqbresulttable";
		echo $this->view->render('partials/resultset.phtml', array (
			'data' => $data,
			'caption' => 'Results',
			'cssid' => 'gqbresulttable'
		));
	}
}