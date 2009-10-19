<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dllearner
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: DllearnerController.php 4303 2009-10-14 09:09:16Z christiankoetteritzsch@yahoo.de $
 */

require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Module/Registry.php';
require_once 'OntoWiki/Model/TitleHelper.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'OntoWiki/Model/TitleHelper.php';

/**
 * DL-Learner plugin controller.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dllearner
 * @author Jens Lehmann
 * @author Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @author Christian Kötteritzsch
 * @author Maria Moritz
 * @author Vu Duc Minh
 */
class DllearnerController extends OntoWiki_Controller_Component {

    private $client;
    private $titleHelper;


    // ID given to this client by the web service
    private $id;
    private $ksID;
    private $_selectedClass;
    private $_selectedModel;
    private $sparqlOntowikiUrl;

	/*
	 * Initialise controller.
	 */
    public function init() {
        parent::init();

        // prepare the title helper
        $this->titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);

        // disable tabs
        require_once 'OntoWiki/Navigation.php';
        OntoWiki_Navigation::disableNavigation();

        $this->sparqlOntowikiUrl=$this->_config->urlBase.'sparql';
        ini_set('default_socket_timeout',200);
    }

    public function knowledgebaseaddAction() {
        $selectedModel = $this->_owApp->selectedModel->getModelIri();
        $sol = $this->_request->solution;
        if($sol === null || $sol === '') {
            $this->_abort('No class expression could be added because nothing was selected.', OntoWiki_Message::ERROR);
        } else {
            if($_SESSION['isEquivalence']) {
                //for equivalent classes
                $sol = '<rdf:RDF xmlns="' . (string) $selectedModel .'/"
            	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
            	xmlns:owl="http://www.w3.org/2002/07/owl#"
            	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
            	<owl:Class rdf:about="' .(string)$selectedModel . (string) $this->_request->solution . '" rdfs:label="' . (string) $this->_request->solution . '" /> 
            	<owl:equivalentClass rdf:nodeID="' . $_SESSION['resource'] . '"/>
            	</rdf:RDF>';
            } else {
                //for superClasses
                $sol = '<rdf:RDF xmlns="' . (string) $selectedModel .'/"
            	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
            	xmlns:owl="http://www.w3.org/2002/07/owl#"
            	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
	    		<owl:Class rdf:about="' .(string)$selectedModel . (string) $this->_request->solution . '" rdfs:label="' . (string) $this->_request->solution . '" />
            	<owl:Class rdf:about="' .$_SESSION['resource'] . '" rdfs:label="' .$_SESSION['resource'] . '" >
					<rdfs:subClassOf rdf:resource="' .(string)$selectedModel . (string) $this->_request->solution . '"/>
	    		</owl:Class> 
            	</rdf:RDF>';
            }
            
            $newModelUri = $this->_owApp->selectedModel->getModelIri();
            $createGraph = false;
            
            require_once 'Erfurt/Syntax/RdfParser.php';
            $file = tempnam('/tmp', 'ow');
            $temp = fopen($file, 'wb');
            fwrite($temp, $sol);
            fclose($temp);
            $locator = Erfurt_Syntax_RdfParser::LOCATOR_FILE; 
            // import statements
            try {
                $this->_erfurt->getStore()->importRdf($newModelUri, $file, 'rdfxml', $locator);
            } catch (Erfurt_Exception $e) {
                if ($createGraph) {
                    // graph had been created: delete it
                    $this->_erfurt->getStore()->deleteModel($newModelUri);
                }
                $this->_abort("Graph '<$newModelUri>' could not be imported: " . $e->getMessage(), OntoWiki_Message::ERROR);
            }

            $this->view->placeholder('main.window.title')->set('Add status');
            $this->_owApp->appendMessage(new OntoWiki_Message('Class expression added', OntoWiki_Message::INFO)); 
            $this->_response->setBody($this -> view -> render('dllearner/knowledgebaseadd.phtml'));
        }
    }
    /**
     * this method does some preparation work for the view e.g.
     * - create titles and links of instances
     * - create a display version of the manchester string
     *
     * @param rawSolutions array from the jsonDecode and output
     * @return array modified solutions array
     */
    private function _prepareSolutionsForView($rawSolutions) {

        $solutions = array();
        
        foreach ($rawSolutions as $key => $rawSolution) {
            if($rawSolution != null) {
            $rawSolution['accuracy'] = round(100*$rawSolution['scoreValue']);
            $rawSolution['coverage'] = round(100*$rawSolution['coverage']);

            $coveredInstances = $rawSolution['coveredInstances'];
            $additionalInstances = $rawSolution['additionalInstances'];
            
            // find all URIs using the XML and feed the title helper with them
            preg_match_all('/URI=\"([^"]+)\"/', $rawSolution['descriptionOWLXML'], $matches);
            $this->titleHelper->addResources(array_unique($matches[1]));
            $rawSolution['usedURIs'] = array_unique($matches[1]);
            // feed the title helper
            $this->titleHelper->addResources($coveredInstances);
            $this->titleHelper->addResources($additionalInstances);

            $solutions[$key] = $rawSolution;
            } else {
                $this->_abort('There are no Instances for this class available. Please insert some Instances.', OntoWiki_Message::ERROR);
            }
        }

        // after feeding, get the titles
        foreach ($solutions as $key => $solution) {
            // for link creation
            $linkurl = new OntoWiki_Url(array('route' => 'properties'), array('r'));

            // prepare manchester string ...
            $newDescription = $solution['descriptionManchesterSyntax'];
            // ... by deleting outside paranthesis ...
            if (preg_match('/^\(.*\)$/', $newDescription) == 1) {
                $newDescription = preg_replace  ('/^\(/', '', $newDescription);
                $newDescription = preg_replace  ('/\)$/', '', $newDescription);
            }
            // ... and replacing URIs with links
            foreach ($solution['usedURIs'] as $uri) {
                $uriLink = (string) $linkurl->setParam('r', $uri, true);
                $uriTitle = $this->titleHelper->getTitle($uri);
                $uriHtml = '<a about="'.$uri.'" href="'.$uriLink.'" class="Resource hasMenu">'.$uriTitle.'</a>';
                $newDescription = str_replace($uri, $uriHtml, $newDescription);
            }
            // TODO?: Keyword highlightning for "AND", "OR", "NOT", "SOME", "ONLY", "MIN", "MAX", "EXACTLY", "VALUE"
            $solution['preparedDescription'] = $newDescription;

            // prepare view array
            $titledCoveredInstances = array();
            foreach ($solution['coveredInstances'] as $coveredInstance) {
                $titledCoveredInstances[$coveredInstance]['title'] = $this->titleHelper->getTitle($coveredInstance);
                $titledCoveredInstances[$coveredInstance]['link'] = (string) $linkurl->setParam('r', $coveredInstance, true);
            }
            $solution['titledCoveredInstances'] = $titledCoveredInstances;

            // prepare view array
            $titledAdditionalInstances = array();
            foreach ($solution['additionalInstances'] as $additionalInstance) {
                $titledAdditionalInstances[$additionalInstance]['title'] = $this->titleHelper->getTitle($additionalInstance);
                $titledAdditionalInstances[$additionalInstance]['link'] = (string) $linkurl->setParam('r', $additionalInstance, true);
            }
            $solution['titledAdditionalInstances'] = $titledAdditionalInstances;

            // create the venn diagram image link to google chart
            $imgInstances = 100;
            $imgAll = count($solution['coveredInstances']) + count($solution['additionalInstances']);
            $imgWanted = round ((count($solution['coveredInstances']) * $solution['coverage'] ) /100);
            $imgCoverage = count($solution['coveredInstances']);
            $solution['chartImg'] = "http://chart.apis.google.com/chart";
            $solution['chartImg'] .= "?chd=t:$imgAll,$imgWanted,0,$imgCoverage";
            $solution['chartImg'] .= "&chs=150x100&cht=v&chdl=Suggested|Wanted&chdlp=t&chco=00FF00,0077FF";

            $solutions[$key] = $solution;
        }
        #var_dump($solution);
        return $solutions;
    }


    public function learnclassAction()
    {
        // Needed here because of the automatic rendering should be deactivated
        // (Warum wird das deaktiviert? Bitte kommentieren.)
        $this->_helper->viewRenderer->setNoRender(true);
        $isEquivalence = $this->_getParam('equivalence');
        // get necessary variables
        
        $_SESSION['isEquivalence'] = $isEquivalence;
        $resource = $this->getRequest()->getParam('resource');
        $_SESSION['resource'] = $resource;
        $this->titleHelper->addResource($resource);

        // check whether DL-Learner is running - if not, then start it
        if(!$this->_isDlLearnerServiceRunning()) {
            $this->_abort('The Reasoner Service isn\'t running. Please start it.', OntoWiki_Message::ERROR);
        }

        // connect to DL-Learner and configure components
        $this->_dlLearnerConnection($resource, $isEquivalence);
        // we make a case distinction between small and big knowledge bases here:
        // Small knowledge bases are exported and treated as OWL files in DL-Learner.
        // Big knowledge bases are imported via the SPARQL component.

        // get toolbar and translation object
        $toolbar = $this->_owApp->toolbar;
        $translate = $this->_owApp->translate;

        //set title of main window ...
        $this->view->placeholder('main.window.title')->set('DL Learner - '.$translate->_('Learnt Class Expressions') );

        // create a new button on the toolbar
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => $translate->_('Add Class Expression'), 'id' => 'classdescription'));
        $this->view->formActionUrl = $this->_config->urlBase . 'dllearner/knowledgebaseadd';
        $this->view->formMethod = 'post';
        $this->view->formName = 'classdescription';
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        $this->view->thExpression = $translate->_('suggested class expressions');
        $this->view->thAccuracy = $translate->_('accuracy');
        $this->view->actionUrl = $this->_config->urlBase . 'dllearner/knowledgebaseadd';

        // add the javascript to the view
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'dllearner.js');

        $rawSolutions = json_decode($this->client->learnDescriptionsEvaluated($this->id) , true);
        $solutions = $this->_prepareSolutionsForView($rawSolutions);

        if ($isEquivalence) {
            $this->view->legend = $translate->_('Suggested Equivalence Classes for ') . $this->titleHelper->getTitle($resource);
        } else {
            $this->view->legend = $translate->_('Suggested Super Classes for ') . $this->titleHelper->getTitle($resource);
        }

        $this->view->solutions = $solutions;
        $this->view->isEquivalence = $isEquivalence;

        $this->_response->setBody($this -> view -> render('dllearner/learnequivalentclass.phtml'));
    }

    private function _dlLearnerConnection($resource, $isEquivalence) {
        $this->client=new SoapClient("extensions/components/dllearner/main.wsdl",array('features' => SOAP_SINGLE_ELEMENT_ARRAYS));
        $this->id=$this->client->generateID();
        
        $owApp    = $this->_owApp;
        $store    = $owApp->erfurt->getStore();
        $graph    = $owApp->selectedModel;
        $modelIRI = $owApp->selectedModel->getModelIri();
        $isBigModel = false;
        // TODO: implement this check via the following SPARQL query:
        // select * {?x ?y ?z} LIMIT 1 OFFSET 10000
        // It return an entry if there are at least 10000 triples in the store.
        // Everything below is considered to be small.

        if($isBigModel) {
        // get a super class of the current class
        // TODO: Gibt es nicht eine bereits implementierte Methode, die die Superklassen
        // zurückgibt bzw. Instanzen einer Klasse? Dann wird der folgende Codeteil übersichtlicher.
            $query = new Erfurt_Sparql_SimpleQuery();

            $superClass = '';
            $query->setProloguePart('SELECT ?parent ')
                ->setFrom(array($modelIRI))
                ->setWherePart(' WHERE {?child <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?parent.
					FILTER (sameTerm(?child, <'. $this->getRequest()->getParam('resource') . '>))} LIMIT 1');
            if ($result = $owApp->selectedModel->sparqlQuery($query)) {
                $superClass = $result[0]['parent'];
            }

            // get all subclass of the super class
            $classes = $store->getTransitiveClosure($modelIRI, EF_RDFS_SUBCLASSOF, array((string) $superClass), true);

            // get instances of all class
            $instances = array();
            foreach ($classes as $cl => $val) {
                $query = new Erfurt_Sparql_SimpleQuery();
                $query->setProloguePart('SELECT DISTINCT ?resourceUri')
                    ->setFrom(array($modelIRI))
                    ->setWherePart(' WHERE {?resourceUri a <'.$cl.'>}');
                if ($result = $owApp->selectedModel->sparqlQuery($query)) {
                    foreach($result as $entry) {
                        $instances[] = $entry['resourceUri'];
                    }
                }
            }
            $this->ksID=$this->client->addKnowledgeSource($this->id, "sparql", $this->sparqlOntowikiUrl);
            $this->client->applyConfigEntryInt($this->id, $this->ksID, "recursionDepth",2);
            $this->client->applyConfigEntryStringArray($this->id, $this->ksID, "instances", $instances);
            $this->client->applyConfigEntryBoolean($this->id, $this->ksID, "saveExtractedFragment", true);
        } else {
            $exportURI = $this->_config->urlBase . 'model/export/?m='.urlencode($modelIRI).'&f=rdfxml';
            $this->ksID=$this->client->addKnowledgeSource($this->id, "owlfile",  $exportURI);
        }

        $this->client->setReasoner($this->id, "fastInstanceChecker");
        $lp_id = $this->client->setLearningProblem($this->id, "classLearning");
        $this->client->applyConfigEntryURL($this->id, $lp_id, "classToDescribe", $resource);
        if($isEquivalence) {
            $this->client->applyConfigEntryString($this->id, $lp_id, "type", "equivalence");
        } else {
            $this->client->applyConfigEntryString($this->id, $lp_id, "type", "superClass");
        }
        $la_id=$this->client->setLearningAlgorithm($this->id, "celoe");
        $this->client->applyConfigEntryInt($this->id, $la_id, "maxExecutionTimeInSeconds", 5);

        // initialise all components
        // TODO Codequalität: Matching auf manuell geschriebenen Fehlermeldungen ist kein guter Stil ...
        try {
            $_SESSION['startat'] = date('H' .':'. 'i' .':'. 's');
            $this->client->initAll($this->id);
        } catch (Exception $e) {
        	$this->_abort("Could not inititalize reasoner.", OntoWiki_Message::ERROR);
        }
        
    }
    
    private function _isDlLearnerServiceRunning() {
        return fsockopen("localhost:8181/services", 8181, $errno, $errstr, 5);
    }

    private function _startDlLearnerService() {
    // TODO: automatischen Start implementieren; falls es Rechteprobleme gibt oder der Nutzer kein
    // Java hat, muss eine entsprechende Meldung angezeigt werden
    }

    // TODO: Bitte Verhalten der Methode kommentieren!
    // Bitte nachfragen, ob es dafür (ausgeben von Meldungen) keine generische OntoWiki-Methode gibt.
    private function _abort($msg, $type = null, $redirect = null) {
        if (empty($type)) {
            $type = OntoWiki_Message::INFO;
        }

        $this->_owApp->appendMessage(new OntoWiki_Message($msg, $type));

        if (empty($redirect)) {
            if ($redirect !== false) {
                $this->_redirect($this->_config->urlBase);
            }
        } else {
            $this->redirect((string)$redirect);
        }

        return true;
    }
}
?>