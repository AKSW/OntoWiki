<?php
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'OntoWiki/Navigation.php';

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_querybuilding
 * @author     Sebastian Hellmann <hellmann@informatik.uni-leipzig.de>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class QuerybuildingController extends OntoWiki_Controller_Component {
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
	public $saveQueryJsonUri = 'http://ns.ontowiki.net/SysOnt/json_code'; // the actual content for gqb. is there a better namespace?

	//specific for graphical query builder
	public $saveQuerySelClassUri = 'http://ns.ontowiki.net/GQB/UserQueries/Pattern/Type';
	public $saveQuerySelClassLabelUri = 'http://ns.ontowiki.net/GQB/UserQueries/Pattern/TypeLabel';
	private $userUri;
	private $userName;
	private $userDbUri;
	
	/**
	 * init() Method to init() normal and add tabbed Navigation
	 */
	public function init() {
		parent :: init();

		// setup the navigation
		OntoWiki_Navigation :: reset();
		OntoWiki_Navigation :: register('listquery', array (
			'controller' => "querybuilding",
			'action' => "listquery",
			'name' => "Saved Queries",
			'position' => 0,
			'active' => true
		));
		OntoWiki_Navigation :: register('queryeditor', array (
			'controller' => "model",
			'action' => "query",
			'name' => "Query Editor",
			'position' => 1,
			'active' => false
		));
		if(class_exists("QuerybuilderHelper")){
		OntoWiki_Navigation :: register('querybuilder', array (
			'controller' => "querybuilder",
			'action' => "manage",
			'name' => "Query Builder ",
			'position' => 2,
			'active' => false
		));
		}
		if(class_exists("GraphicalquerybuilderHelper")){
		OntoWiki_Navigation :: register('graphicalquerybuilder', array (
			'controller' => "graphicalquerybuilder",
			'action' => "display",
			'name' => "Graphical Query Builder",
			'position' => 3,
			'active' => false
		));
		}
		
		$user = $this->_erfurt->getAuth()->getIdentity();
		$this->userUri = $user->getUri();
		$this->userName = $user->getUsername();
		$this->userDbUri = $this->baseQueryDbUri . 'user-' . $this->userName . '/';
		

	}

	/**
	 * Action that will take POST-Data to save query and load existing Queries for listing
	 */
	public function listqueryAction() {
		// set the active tab navigation
		OntoWiki_Navigation :: setActive('listquery', true);

		$store = $this->_owApp->erfurt->getStore();

		//Loading data for list of saved queries
                //(even queries that are not for the selectedModel - do we want this?)
		//TODO Paging for Queries
		$loadInfoQuery = 'SELECT *
		             FROM <'.$this->_owApp->selectedModel.'>
		             FROM <'.$this->userDbUri.'>
		     		WHERE {        		
		             ?query a <' . $this->saveQueryClassUri . '> .
		             ?query <' . $this->saveQueryDateUri . '> ?date .
		             OPTIONAL {?query <' . $this->saveQueryModelUri . '> ?model} .
		             OPTIONAL {?query <' . $this->saveQueryJsonUri . '> ?json }.
		             OPTIONAL {?query <' . $this->saveQueryNameUri . '> ?name }.
		             OPTIONAL {?query <' . $this->saveQueryDescriptionUri . '> ?desc} .
		             OPTIONAL {?query <' . $this->saveQueryQueryUri . '> ?sparql} .
		             OPTIONAL {?query <' . $this->saveQueryGeneratorUri . '> ?generator} .
		             OPTIONAL {?query <' . $this->saveQueryNumViewsUri . '> ?num_views }.
		             OPTIONAL {?query <' . $this->saveQueryCreatorUri . '> ?creator } 
		             } ORDER BY DESC(?date)';

		$loadInfoData = $store->sparqlQuery($loadInfoQuery);

		//$this->view->getQueriesQuery = $loadInfoQuery;
		// Assign data to view
		$this->view->listData = $loadInfoData;

		// set the active tab navigation
		OntoWiki_Navigation :: setActive('listquery', true);

		$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Query Building'));
	}
	
	public function savequeryAction(){
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/plain');
		
		$store = $this->_erfurt->getStore();
		$storeGraph = $this->_owApp->selectedModel;
		$graphUri = (string) $this->_owApp->selectedModel;

		// stripping automatic escaped chars
		$params = array ();
		foreach ($this->_request->getParams() as $key => $param) {
			if (get_magic_quotes_gpc()) {
				$params[$key] = stripslashes($param);
			} else {
				$params[$key] = $param;
			}
		}
		$res = "json or desc missing";
		// checking for post data to save queries
		if (isset ($params['json']) && isset ($params['qdesc'])) {
			$qdesc = addslashes(trim($params['qdesc']));
			$json = addslashes($params['json']);

			if ($params['share'] == "true") {
				// store in the model itself - everybody can see it
				$storeGraph = $this->_owApp->selectedModel;
			} else {
				//private db - should be configured so only the user can see it
				$storeGraph = $this->getUserQueryDB();
			}
			
			// checking whether any queries exist yet in this store
			$existingQueriesQuery = Erfurt_Sparql_SimpleQuery :: initWithString('SELECT * 
				                 WHERE {
				                 ?query <' . EF_RDF_TYPE . '> <' . OntoWiki_Utils :: expandNamespace($this->saveQueryClassUri) . '> .
				                 }');
			$existingQueries = $storeGraph->sparqlQuery($existingQueriesQuery);
			if (empty ($existingQueries)) {
				//this is the first query
				$this->insertInitials($storeGraph);
			}
			
			// checking whether a query with same dc:title and soic:content (Where-Part) already exists
			$existingDataQuery = Erfurt_Sparql_SimpleQuery :: initWithString('SELECT * 
				                 WHERE {
				                 ?query <' . EF_RDF_TYPE . '> <' . OntoWiki_Utils :: expandNamespace($this->saveQueryClassUri) . '> .
				                 ?query <' . OntoWiki_Utils :: expandNamespace($this->saveQueryJsonUri) . '> "' . $json . '" .
				                 ?query <' . OntoWiki_Utils :: expandNamespace($this->saveQueryDescriptionUri) . '> "' . $qdesc . '" .
				                 ?query <' . OntoWiki_Utils :: expandNamespace($this->saveQueryModelUri) . '> <' . $graphUri . '> .
				                 }');

			$existingData = $storeGraph->sparqlQuery($existingDataQuery);

			if (empty ($existingData)) {
				//such a query is not saved yet - lets save it
				$name = (string) $storeGraph . '#Query-' . md5($json . $qdesc);

				$storeGraph->addStatement($name, EF_RDF_TYPE, array (
					'value' => $this->saveQueryClassUri,
					'type' => 'uri'
				), false);
				$storeGraph->addStatement($name, 
					$this->saveQueryModelUri, array (
					'value' => (string) $this->_owApp->selectedModel, 'type' => 'uri'
				), false);
				$storeGraph->addStatement($name, $this->saveQueryDescriptionUri, array (
					'value' => $params['qdesc'],
					'type' => 'literal'
				), false);
				$storeGraph->addStatement($name, $this->saveQueryNameUri, array (
					'value' => $params['name'],
					'type' => 'literal'
				), false);
				$storeGraph->addStatement($name, $this->saveQueryDateUri, array (
					'value' => (string) date('c'),
					'type' => 'literal',
					'datatype' => OntoWiki_Utils :: expandNamespace('xsd:dateTime')
				), false);
				$storeGraph->addStatement($name, OntoWiki_Utils :: expandNamespace($this->saveQueryNumViewsUri), array (
					'value' => '1',
					'type' => 'literal',
					'datatype' => OntoWiki_Utils :: expandNamespace('xsd:integer')
				), false);
				if($params['generator'] == "gqb" || $params['generator'] == "qb"){
					$storeGraph->addStatement($name, $this->saveQueryJsonUri, array (
						'value' => $params['json'],
						'type' => 'literal'
					), false);
				}
				$storeGraph->addStatement($name, $this->saveQueryQueryUri, array (
					'value' => $params['query'],
					'type' => 'literal'
				), false);
				$storeGraph->addStatement($name, $this->saveQueryGeneratorUri, array (
					'value' => $params['generator'],
					'type' => 'literal'
				), false);
				if($params['generator'] == "gqb"){
					$storeGraph->addStatement($name, $this->saveQueryIdUri, array (
						'value' => $params['id'],
						'type' => 'literal'
					), false);
					$storeGraph->addStatement($name, $this->saveQuerySelClassUri, array (
						'value' => $params['type'],
						'type' => 'uri'
					), false);
					$storeGraph->addStatement($name, $this->saveQuerySelClassLabelUri, array (
						'value' => $params['typelabel'],
						'type' => 'literal'
					), false);
				} else {
					//TODO gqb uses id - qb not... needed?
					$storeGraph->addStatement($name, $this->saveQueryIdUri, array (
						'value' => md5($json . $qdesc),
						'type' => 'literal'
					), false);
				}
				$user = $this->_erfurt->getAuth()->getIdentity();
				$userUri = $user->getUri();

				$storeGraph->addStatement($name, $this->saveQueryCreatorUri, array (
					'value' => $userUri,
					'type' => 'uri'
				), false);

				$res = 'All OK'; 
			} else {
				$res = 'Save failed. (Query with same title and patterns exists)'; 
			}
		}
		$response->setBody($res);
		$response->sendResponse();
		exit;
	}
	
	public function deleteAction() {
		$store = OntoWiki::getInstance()->erfurt->getStore();

		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'text/plain');

		// fetch param
		$uriString = $this->_request->getParam('uri', '');
		
		if (get_magic_quotes_gpc()) {
			$uriString = stripslashes($uriString);			
		}
		
		$res = 'All OK';
		if (!empty ($uriString)) {
			try {
				//find the db
				$userdb = $this->getUserQueryDB(false);
				
				//TODO pass the "where it is" as param
				//delete from private
				if($userdb != null)
					$userdb->deleteMatchingStatements($uriString, null, null);
				//delete from shared
				$this->_owApp->selectedModel->deleteMatchingStatements($uriString, null, null);
			} catch (Exception $e) {
				$res = $e;
			}
		} else {
			$res = 'need to pass uri';
		}

		$response->setBody($res);
		$response->sendResponse();
		exit;
	}
	
	private function getUserQueryDB($create = true) {
		$userdb = $this->findDB($this->userDbUri);
		if ($userdb != null || !$create) {
			return $userdb;
		} else {
			return $this->createUserQueryDB();
		}
	}

	/**
	* find db by name
	*
	* @param string name of searched db
	* @return Model-Object
	*/
	private function findDB($name) {
		$_store = $this->_erfurt->getStore();

		//get all Models (including hidden Models)
		$allModels = $_store->getAvailableModels(true);

		foreach ($allModels as $graphUri => $true) {
			if ($graphUri === $name) {
				//get the model (without authentification)
				return $_store->getModel($graphUri, false);
			}
		}

		return null;
	}
	
	private function insertInitials($db){
		//add the "Pattern" Class
		$object['value'] = EF_RDFS_CLASS;
		$object['type'] = 'uri';
		$db->addStatement($this->saveQueryClassUri, EF_RDF_TYPE, $object);

		//domain for the class
		$object['value'] = $db->getModelIri();
		$object['type'] = 'uri';
		$db->addStatement($this->saveQueryClassUri, 'http://www.w3.org/2000/01/rdf-schema#domain', $object);

		//label for the class
		$object['value'] = "Query";
		$object['type'] = 'literal';
		$db->addStatement($this->saveQueryClassUri, 'http://www.w3.org/2000/01/rdf-schema#label', $object);
	}

	private function createUserQueryDB() {
		$proposedDBname = $this->userDbUri;

		$store = $this->_erfurt->getStore();
		$newModel = $store->getNewModel($proposedDBname);

		$options = array ();
		$object = array ();

		// add english label for this db
		$options['object_type'] = Erfurt_Store :: TYPE_LITERAL;
		$object['value'] = 'GQB Query DB of ' . $this->userName;
		$newModel->addStatement($proposedDBname, 'http://www.w3.org/2000/01/rdf-schema#label', $object);

		// german label
		$options['literal_language'] = 'de';
		$object['value'] = 'GQB Anfrage-DB von ' . $this->userName;
		$newModel->addStatement($proposedDBname, 'http://www.w3.org/2000/01/rdf-schema#label', $object);

		// add description of this db
		$object['value'] = 'Hier werden Sparql-Queries gespeichert, die User ' . $this->userName . ' erstellt und gespeichert hat.';
		$newModel->addStatement($proposedDBname, EF_RDFS_COMMENT, $object);

		//domain of this db (needed?)
		$object['value'] = $this->baseQueryDbUri;
		$options['object_type'] = Erfurt_Store :: TYPE_IRI;
		$newModel->addStatement($proposedDBname, 'http://www.w3.org/2000/01/rdf-schema#domain', $object);

		//add owner/maker of this db (is foaf:maker ok? curi's are not good)
		$object['value'] = $this->userUri;
		$newModel->addStatement($proposedDBname, $this->saveQueryCreatorUri, $object);

		$this->insertInitials($newModel);
		
		return $newModel;
	}

}