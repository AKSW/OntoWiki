<?php
/*


 */

require_once 'OntoWiki/Controller/Component.php';

/**
 * Component controller for the Freeb Editor.
 *
 * @author Kurt Jacobson <kurtjx at gmail>
 */
class FreebeditController extends OntoWiki_Controller_Component
{
	public function init()
    {
        parent::init();
        // m is automatically used and selected (as in FoafEdit)
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('No model pre-selected model and missing parameter m (model)!');
            exit;
        } else {
            $this->model = $this->_owApp->selectedModel;
            $this->view->resourceUri = (string) $this->_owApp->selectedResource;
        }
    }

	/**
	 * sparql query and return the array of defaultProperty's
	 */
	private function getDefaultProperties($type){
		require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();

        // build SPARQL query for getting class defualt properties
        $query->setProloguePart('SELECT DISTINCT ?p')
              ->setWherePart('WHERE {<' . $type . '> <' . $this->_privateConfig->defaultProperty . '> ?p. }');
        // query the store
        if ($result = $this->model->sparqlQuery($query)) {
			foreach ($result as $row){
            	$dprops[]=$row['p'];
            }
            return $dprops;
        }
        else{
        	return null;
        }

	}

	/**
	*  check if the property is an object property, return true if it is
	*   TODO: there must be a more clever way
	*/
	private function isObjectProperty($prop){
		require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();

		$query->setProloguePart('SELECT DISTINCT ?o')
			  ->setWherePart('WHERE { ?s <' . $prop . '> ?o . }');
		if ($result = $this->model->sparqlQuery($query)){
			$obj = (string) $result[0]['o'];
			if($this->string_begins_with($obj, "http://")){
				return true;
			}
			else{
				return false;
			}

		}
		else{
			// if we don't find any triples with this property, we assume a data prop which is stupid probably
			// TODO: fix this somehow re: data props and object props
			return false;
		}
	}

	private function string_begins_with($string, $search)
	{
	    return (strncmp($string, $search, strlen($search)) == 0);
	}


    public function thingAction()
    {
    	//$store       = $this->_owApp->erfurt->getStore();
    	$resource = $this->_owApp->selectedResource;


		// set the title
		$title = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace((string) $resource);
    	$this->view->placeholder('main.window.title')->append('FreeB view: ' . $title);
        $this->addModuleContext('main.window.properties');
        $this->view->title = $title;

        // we'll need the titleHelper later...
        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($this->model);

		/*
		 * get the types for the given URI
		 */
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();

        // build SPARQL query for getting class (rdf:type) of current resource
        $query->setProloguePart('SELECT DISTINCT ?t')
              ->setWherePart('WHERE {<' . $resource . '> a ?t.}');
        // query the store
        if ($result = $this->model->sparqlQuery($query)) {
            foreach ($result as $row){
            	$types[]=$row['t'];
            }
      		$this->view->types = $types;
      		$titleHelper->addResources($types);
	        // get titles for types
	        // do this later...
	        foreach ($types as $type){
	        	$titles[$type] = $titleHelper->getTitle($type, 'en');
	        }
	        $this->view->type_titles = $titles;

	        // if we've got types, get default properties
	        foreach ($types as $type){
	        	$default_props[$type] = $this->getDefaultProperties($type);
	        }
	        $this->view->default_props = $default_props;
        }


        if (!isset($this->_request->r)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception("Missing parameter 'r'.");
            exit;
        }

        require_once 'OntoWiki/Model/Resource.php';
        $resource = new OntoWiki_Model_Resource($this->model->getStore(), $this->model, $this->_request->r);
        $this->view->values = $resource->getValues();
        $predicates = $resource->getPredicates();
        $this->view->predicates = $predicates;



		// add graphs
        $graphs = array_keys($predicates);
        $titleHelper->addResources($graphs);

        /*$graphInfo = array();
        foreach ($graphs as $g) {
            $graphInfo[$g] = $titleHelper->getTitle($g, $this->_config->languages->locale);
        }*/
        $this->view->graphs = $graphs;//$graphInfo;

        // prepare namespaces
        /*
		* moved this to if statement below
		$namespaces = $this->model->getNamespaces();
        $graphBase  = $this->model->getBaseUri();
        if (!array_key_exists($graphBase, $namespaces)) {
            $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
        }
        $this->view->namespaces = $namespaces;*/

        $this->view->graphUri      = $this->model->getModelIri();
        $this->view->graphBaseUri  = $this->model->getBaseIri();

        // set RDFa widgets update info for editable graphs
        foreach ($graphs as $g) {
            if ($this->_erfurt->getAc()->isModelAllowed('edit', $g)) {
                $this->view->placeholder('update')->append(array(
                    'sourceGraph'    => $g,
                    'queryEndpoint'  => $this->_config->urlBase . 'sparql/',
                    'updateEndpoint' => $this->_config->urlBase . 'update/'
                ));
            }
        }

        // set up toolbar buttons
        if ($this->model->isEditable()) {
            // TODO: check acl
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Properties'));
            $params = array(
                'name' => 'Delete Resource',
                'url'  => $this->_config->urlBase . 'resource/delete/?r=' . urlencode((string) $this->view->resourceUri)
            );
            $toolbar->appendButton(OntoWiki_Toolbar::SEPARATOR)
                    ->appendButton(OntoWiki_Toolbar::DELETE, $params);

            $this->view->placeholder('main.window.toolbar')->set($toolbar);

            // prepare namespaces
            $namespaces = $this->model->getNamespaces();
            $graphBase  = $this->model->getBaseUri();
            if (!array_key_exists($graphBase, $namespaces)) {
                $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
            }
            $this->view->namespaces = $namespaces;

            // add update vocabulary graph definitions
            $this->view->placeholder('update')->append(array(
                'sourceGraph'    => $this->model->getModelUri(),
                'queryEndpoint'  => $this->_config->urlBase . 'sparql/',
                'updateEndpoint' => $this->_config->urlBase . 'update/'
            ));
        }


        /* ********************
         * parse the predicates into a freeb array
         */
        $freeb = array();
        $allprops = array();
        foreach($types as $type){
        	$freeb[$type]['title'] = $titleHelper->getTitle($type, 'en');

        	foreach(array_keys($predicates) as $graph){
				//$this->view->prop = $graph;
        		// check against default URIs
        		foreach(array_keys($predicates[$graph]) as $prop){
					//$this->view->prop = $prop;
        			if (is_array($default_props[$type])){
	        			foreach($default_props[$type] as $dprop){
	        				$this->view->prop = $default_props;
	        				if (strcmp($prop,$dprop)==0){
	        					$freeb[$type][$graph][$dprop] = $predicates[$graph][$prop];
	        					$allprops[$type][] = $prop;
	        					unset($predicates[$graph][$prop]);
	        					//$predicates[$graph][$prop] = null;
	        				}
	        			}
	        		}
        		}
        	}
        }
        $this->view->predicates = $predicates;
        $this->view->freeb = $freeb;
        //$this->view->allprops = $allprops;

        /* get an array with just the missing props */
        $missingprops = array();
		foreach($default_props as $type=>$props){
			if(array_key_exists($type, $allprops) && !is_null($props)){
				foreach($props as $prop){
					if(!in_array($prop,$allprops[$type])){
						$titleHelper->addResource($prop);
						$missingprops[$type][$prop]['title'] = $titleHelper->getTitle($prop, 'en');
						$missingprops[$type][$prop]['object'] = $this->isObjectProperty($prop);
					}
				}
			}
			else{
				if(!is_null($props)){
					$titleHelper->addResources($props);
					foreach($props as $prop){

					//$missingprops[$type][$prop] = $titleHelper->getTitle($prop, 'en');
					$missingprops[$type][$prop]['title'] = $titleHelper->getTitle($prop, 'en');
					$missingprops[$type][$prop]['object'] = $this->isObjectProperty($prop);
					}
				}
			}
		}
		$this->view->missingprops = $missingprops;

    }

}
?>
