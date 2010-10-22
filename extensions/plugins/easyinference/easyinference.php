<?php
/**
 * EI Plugin
 *
 * @copyright  Copyright (c) 2008, swp09-7
 */

require_once 'OntoWiki/Plugin.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';

define('EI_INFERENCE_SUFFIX','inference/');
define('EI_RULE_MODELURI','http://ns.ontowiki.net/Extension/EasyInference/');
define('EI_SYSONT_MODEL', 'http://ns.ontowiki.net/SysOnt/Model');

class EasyinferencePlugin extends OntoWiki_Plugin {
    private $infModel = null;
    private $has_ask = true;
    private $has_star_select = true;
    private $prologue;

    public function init() {
        $_app = OntoWiki::getInstance();
        $_erfurt = $_app->erfurt;
        $logger = $_app->logger;
        $store = $_erfurt->getStore();


        $this->prologue = ($this->has_ask ? 'ASK' : ($this->has_star_select ? 'SELECT *' : die('db not supported')));


        // create inference rule knowledge base when not available
        if(!$store->isModelAvailable(EI_RULE_MODELURI, false)) {
            $logger->info('Model not found, creating:'.EI_RULE_MODELURI);
            $this->_setupInferenceRuleKB();
        }

        //if no model selected do nothing
        if(!$_app->selectedModel) {
            return;
        }

        $infModelURI = $_app->selectedModel->getModelIri().EI_INFERENCE_SUFFIX;
        // does this model have inferences?
        if (!$store->isModelAvailable($infModelURI, false)) {
            return;
        }

        $this->view->addScriptPath($this->_pluginRoot);
        $_app->translate->addTranslation($this->_pluginRoot . DIRECTORY_SEPARATOR . 'languages/', null, array('scan' => Zend_Translate::LOCALE_FILENAME));
    }

    /**
     * this function has been deactivated,
     * as showing inference isa acore feature now
     * @param <type> $event
     * @return <type>
     */
    public function onDisplayObjectPropertyValue($event) {
       /*
        if (!$this->infModel) return;

        if ($this->_isInferenced($event, true)) {
            return $this->view->render('inferenceprop.phtml');
        }*/
    }

    /**
     * checks, wether a property is inferred
     * @param $event the Erfurt_Event
     * @param $option true, if the object is a resource, false, if it is a literal
     *
     * @return true, if the object was inferred
     */
    private function _isInferenced($event, $object) {
        $this->view->assign('title', $event->title);
        $this->view->assign('value', $event->value);
        $this->view->assign('object', $object);

        $object = $object ? '<'.$event->value.'>' : '"'.$event->value.'"';

        $_app = OntoWiki::getInstance();
        $_erfurt = $_app->erfurt;

        $where = 'WHERE { <'.$_app->selectedResource.'> <'.$event->property.'> '.$object.' }';

        if(!! $_erfurt->getStore()->sparqlQuery( // the blank needs to be in front of the ASK! erfurt parser bug.
        Erfurt_Sparql_SimpleQuery::initWithString(' '.$this->prologue.' FROM <'.$this->infModel.'> '.$where)
        ))
            if (! $_erfurt->getStore()->sparqlQuery( // the blank needs to be in front of the ASK! erfurt parser bug.
            Erfurt_Sparql_SimpleQuery::initWithString(' '.$this->prologue.' FROM <'.$_app->selectedModel.'> '.$where)
            ,array('use_additional_imports'=>false)))
                return true;

        return false;
    }

    /**
     * imports the rule knowledgebase into ontowiki
     */
    private function _setupInferenceRuleKB() {
        $_app = OntoWiki::getInstance();
        $_erfurt = $_app->erfurt;
        $store = $_erfurt->getStore();
        $config = $_erfurt->getConfig();


        // patch inference list
        /* @exec('sed -i -e\'s,FILTER (isURI(?resourceUri)),FILTER (isURI(?resourceUri) || isBlank(?resourceUri)),\' \''.
                _OWROOT.'application/classes/OntoWiki/Model/Instances.php\'');
        */
        $_sysOnt = $store->getModel($config->sysont->modelUri, false);

        //print_r($_sysOnt);
        // echo $_erfurt->getConfig()->sysont->properties->hidden;
        // echo $store->isModelAvailable($config->sysont->modelUri, false);
        // echo $_sysOnt->getModelUri();

        $statements = array();

        // hide the inference rule kb by default
        $statements[] = array( EI_RULE_MODELURI,$_erfurt->getConfig()->sysont->properties->hidden, array('type'=>'literal','value'=>'true') );
        // from Erfurt_Rdf_model with this comment: "TODO add this statement on model add?!"
        $statements[] = array( EI_RULE_MODELURI, EF_RDF_TYPE, array('type'=>'uri','value'=>EI_SYSONT_MODEL) );

        // import inference rules to the system ontology
        $statements[] = array( $_sysOnt->getModelIri(), $config->sysont->properties->hiddenImports, array('type'=>'uri','value'=>EI_RULE_MODELURI) );

        // from Erfurt_Rdf_model with this comment: "TODO add this statement on model add?!"
        $statements[] = array( $_sysOnt->getModelIri(), EF_RDF_TYPE, array('type'=>'uri','value'=>EI_SYSONT_MODEL) );

        foreach ( $statements as $one) {
            $store->addStatement($_sysOnt->getModelUri(), $one[0], $one[1], $one[2], false);
        }


        // import rdf
        $sourcepath = $_app->componentManager->getComponentPath() . 'easyinference/rule.rdf';
        //$this->_removeForTestPurposes($store);
        $store->getNewModel(EI_RULE_MODELURI, EI_RULE_MODELURI , Erfurt_Store::MODEL_TYPE_OWL, false);
        try {
            require_once 'Erfurt/Syntax/RdfParser.php';
            $store->importRdf(EI_RULE_MODELURI, $sourcepath, 'rdfxml',
                    Erfurt_Syntax_RdfParser::LOCATOR_FILE, false);
        }catch (Exception $e) { // roll back
            $store->deleteModel(EI_RULE_MODELURI, false);
            throw $e;
        }
    }

    private function _removeForTestPurposes($store) {
        try {
            $store->deleteModel(EI_RULE_MODELURI, false);
        }catch (Exception $e) {

        }
    }


}

