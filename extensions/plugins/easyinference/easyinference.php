<?php
/**
 * EI Plugin
 *
 * @copyright  Copyright (c) 2008, swp09-7
 */

require_once 'OntoWiki/Plugin.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';

class EasyinferencePlugin extends OntoWiki_Plugin
{
    private $infModel = null;
	private $has_ask = true;
	private $has_star_select = true;
	private $prologue;

   public function init()
    {
	  $_app = OntoWiki_Application::getInstance();
	  $_erfurt = $_app->erfurt;

	  $this->prologue = ($this->has_ask ? 'ASK' : ($this->has_star_select ? 'SELECT *' : die('db not supported')));

	  // create inference rule knowledge base when not available
	  try {
		$_erfurt->getStore()->getModel('http://ns.ontowiki.net/Extension/EasyInference/', false);
	  }
	  catch (Erfurt_Store_Exception $e) { // not existing yet
		$this->_setupInferenceRuleKB();
	  }

	  if (!$_app->selectedModel)
		return;

	  // does this model have inferences?
	  try {
		$this->infModel = $_erfurt->getStore()->getModel($_app->selectedModel->getModelIri().'inference/', false);
	  }
	  catch (Erfurt_Store_Exception $e) {
		return;
	  }

	  $this->view->addScriptPath($this->_pluginRoot);
	  $_app->translate->addTranslation($this->_pluginRoot . DIRECTORY_SEPARATOR . 'languages/', null, array('scan' => Zend_Translate::LOCALE_FILENAME));
    }
    
    public function onDisplayObjectPropertyValue($event)
    {
	  if (!$this->infModel) return;

	  if ($this->_isInferenced($event, true))
		return $this->view->render('inferenceprop.phtml');
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

	  $_app = OntoWiki_Application::getInstance();
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
	private function _setupInferenceRuleKB()
	{
	  $_app = OntoWiki_Application::getInstance();
	  $_erfurt = $_app->erfurt;

	  // patch inference list
	  @exec('sed -i -e\'s,FILTER (isURI(?resourceUri)),FILTER (isURI(?resourceUri) || isBlank(?resourceUri)),\' \''.
            _OWROOT.'application/classes/OntoWiki/Model/Instances.php\'');

	  $_sysOnt = $_erfurt->getStore()->getModel($_erfurt->getConfig()->sysOnt->modelUri, false);
	  // hide the inference rule kb by default
	  $_sysOnt->addStatement('http://ns.ontowiki.net/Extension/EasyInference/',
					 $_erfurt->getConfig()->sysOnt->properties->hidden,
					 array('type'=>'literal','value'=>'true'));
	  // from Erfurt_Rdf_model with this comment: "TODO add this statement on model add?!"
	  $_sysOnt->addStatement('http://ns.ontowiki.net/Extension/EasyInference/', EF_RDF_TYPE,
						   array('type'=>'uri','value'=>'http://ns.ontowiki.net/SysOnt/Model'));

	  // import inference rules to the system ontology
	  $_sysOnt->addStatement($_sysOnt->getModelIri(), $_erfurt->getConfig()->sysOnt->properties->hiddenImports,
						  array('type'=>'uri','value'=>'http://ns.ontowiki.net/Extension/EasyInference/'));
	  // from Erfurt_Rdf_model with this comment: "TODO add this statement on model add?!"
	  $_sysOnt->addStatement($_sysOnt->getModelIri(), EF_RDF_TYPE,
						   array('type'=>'uri','value'=>'http://ns.ontowiki.net/SysOnt/Model'));

	  // import rdf
	  $sourcepath = $_app->componentManager->getComponentPath() . 'easyinference/rule.rdf';
	  $_erfurt->getStore()->getNewModel('http://ns.ontowiki.net/Extension/EasyInference/');
	  try {
		require_once 'Erfurt/Syntax/RdfParser.php';
		$_erfurt->getStore()->importRdf('http://ns.ontowiki.net/Extension/EasyInference/', $sourcepath, 'rdfxml',
                                        Erfurt_Syntax_RdfParser::LOCATOR_FILE, false);
	  }
	  catch (Exception $e) { // roll back
		$_erfurt->getStore()->deleteModel('http://ns.ontowiki.net/Extension/EasyInference/');
		throw $e;
	  }
	}
}

