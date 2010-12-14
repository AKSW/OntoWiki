<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'InfRule.php';
require_once 'InfRuleContainer.php';

define('EI_CONTROLLER_RULE_MODEL_URI','http://ns.ontowiki.net/Extension/EasyInference/');
define('EI_CONTROLLER_INFRULE_APPLIED_CLASS',EI_CONTROLLER_RULE_MODEL_URI.'InfRule/Applied');
define('EI_CONTROLLER_HAS_CONCRETE',EI_CONTROLLER_RULE_MODEL_URI.'hasConcrete');



/**
 * Controller to manage inferences for EasyInference
 *
 * @package    easyinference
 * @author     swp-09-7
 * @author Sebastian Hellmann <hellmann@informatik.uni-leipzig.de>
 */
class EasyinferenceController extends OntoWiki_Controller_Component {

    /**
     * activate an inference rule on model
     *
     */
    public function addAction () {


        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $_app = $this->_owApp;
        $store = $this->_owApp->erfurt->getStore();
        $config = $this->_owApp->erfurt->getConfig();
        $logger = $this->_owApp->logger;

        $rules = InfRuleContainer::getInstance()->getRules();
        $ruleParam = $this->getParam('rule', true);
        $rule = $rules[$ruleParam];

        $resource = $_app->selectedResource->getIri();
        if (!$resource || $resource === '') {
            $resource = $_app->selectedModel->getModelIri();
            $logger->debug("selectedResource was empty using selectedModel instead");
        }

        if(empty( $ruleParam) || empty($rule)) {
            $msg = 'No rule found! ';
            $success = false;

        }else {

            $logger->debug("addAction called for adding rule: ".$rule->getName());

            //create a unique id as the hash over the model URI and the selected resource
            $hash =  substr(md5($_app->selectedModel->getModelIri().$resource), 0, 10);

            $uniqueId = EI_CONTROLLER_RULE_MODEL_URI.'InfRule/Applied/' . $rule->getName(). '#'.  $hash  ;

            $logger->debug("created resource: ".$uniqueId);

            $label = $rule->getName() . ' ' . $_app->translate->_('on') . ' ' . $_app->selectedModel->getModelIri();
            if ((string)$_app->selectedModel != (string)$_app->selectedResource) {
                $label .= ' ' . $_app->translate->_('for') . ' ' . $_app->selectedResource->getLocalName();
            }

            $statement = array();
            $statements[] = array($uniqueId,  EI_CONTROLLER_RULE_MODEL_URI."hasRule",array('type'=>'uri','value'=>$ruleParam) );
            $statements[] = array($uniqueId,  EI_CONTROLLER_RULE_MODEL_URI."hasModel",array('type'=>'uri','value'=>$_app->selectedModel->getModelIri()) );
            $statements[] = array($uniqueId,  EI_CONTROLLER_HAS_CONCRETE,array('type'=>'uri','value'=>$resource) );
            $statements[] = array($uniqueId,  EF_RDF_TYPE,array('type'=>'uri','value'=>EI_CONTROLLER_INFRULE_APPLIED_CLASS) );
            $statements[] = array($uniqueId,  EF_RDFS_LABEL ,array('type'=>'literal','value'=>$label) );
            try {
                $this->_addStatements($store,  $config->sysont->modelUri, $statements) ;
            }catch (Exception $e) {
                $msg = "adding of rule failed ";
                $logger->info($msg);
                $this->_jsonHelper(false, $msg);
            }

            $msg ='Rule '.$rule->getName().' was activated!';
            $success = true;

            // add the inferences?
            if ($this->getParam('with_inferences',false) == 1) {

                if ((string)$resource === (string)$_app->selectedModel)
                    $resource = null;

                $this->addInferences($_app->selectedModel,
                        $this->getInferenceModel($_app->selectedModel),
                        $rule->getQueryFromRule ( $resource, array('disallowBoundedTripel'=>true)));
                $msg .= PHP_EOL.'Inferences were generated.';
            }

        }

        $this->_jsonHelper($success, $msg);
        $logger->info("result of add rule action: Success: ".$success." msg: ".$msg);
    }

    private function _addStatements($store,  $graph, $statementArray) {
        $this->_owApp->logger->debug("trying to add ".count($statementArray)." statements");
        foreach ($statementArray as $one) {
            $store->addStatement($graph, $one[0], $one[1], $one[2], false);
        }
        $this->_owApp->logger->debug("success");
    }

    private function _jsonHelper( $success, $msg ) {
        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('success'=>$success, 'msg'=>$msg)));

    }

    // Gibt alle addRules und deleteRules zum füllen der Comboboxen im Modul zurück
    public function getallrulesAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        $this->_response->setHeader('Content-Type', 'application/json', true);

        try {
            // get existing rules
            $deleteRules = array();
            $query = 'PREFIX : <'.EI_CONTROLLER_RULE_MODEL_URI.'>'.
                    ' PREFIX rule: <http://ns.ontowiki.net/Extension/EasyInference/InfRule/>'.
                    ' PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>'.
                    ' SELECT DISTINCT ?n ?r'.
                    ' FROM <'.EI_CONTROLLER_RULE_MODEL_URI.'>'.
                    ' FROM <'.$this->_owApp->erfurt->getConfig()->sysont->modelUri.'>'.
                    ' WHERE {'.
                    ' ?s a <'.EI_CONTROLLER_INFRULE_APPLIED_CLASS.'> .'.
                    ' ?s :hasRule ?r .'.
                    ' ?s :hasModel <'.$this->_owApp->selectedModel.'> .'.
                    ' ?s :hasConcrete <'.$this->_owApp->selectedResource.'> .'.
                    ' ?r rdfs:label ?n'.
                    ' }';
            //echo $query;
            $res = $this->_owApp->erfurt->getStore()->sparqlQuery(
                    Erfurt_Sparql_SimpleQuery::initWithString($query)
            );
            foreach ($res as $result) {
                $deleteRules[$result['r']] = $result['n'];
            }

            // get applicable rules
            $addRules = array();
            foreach (InfRuleContainer::getInstance()->getRules() as $uri => $rule) {
                // skip already enabled rules
                if (array_key_exists($uri, $deleteRules))
                    continue;

                // use a global rule on the model
                if ((string)$this->_owApp->selectedResource != (string)$this->_owApp->selectedModel)
                    $resource = (string)$this->_owApp->selectedResource; //$this->getParam('resource', true);
                else
                    $resource = null;

                $prologue = 'ASK';      //TODO: if ASK not available, do SELECT *
                if ($rule->checkRuleApplicable ($resource,
                $this->_owApp->selectedModel,
                $prologue))
                    $addRules[$uri] = $rule->getName();

            }
            $json = json_encode(array('success' => 1,'addRules'=>$addRules, 'deleteRules' => $deleteRules));
            $this->_response->setBody($json);
            $this->_owApp->logger->debug($json);
        }
        catch (Erfurt_Exception $e) {
            $msg = $e.' : '.PHP_EOL;
            $this->_response->setBody(json_encode(array('success' => 0,'msg'=>$msg)));
        }
    }


    /**
     * disable an inference rule on model
     */
    public function deleteAction () {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $rule = $this->getParam('rule',true);
        $delete_directly = $this->getParam('delete_directly',false);
        $_app = $this->_owApp;
        $sysont = $this->_owApp->erfurt->getSysOntModel();
        $rules = InfRuleContainer::getInstance()->getRules();
        $msg = '';
        if ($this->getParam('rule',true) && array_key_exists($rule, $rules)) {
            $fromGraph = $this->_owApp->erfurt->getConfig()->sysont->modelUri;
            $conditions =
                    ' ?s a <'.EI_CONTROLLER_INFRULE_APPLIED_CLASS.'> .'.
                    ' ?s <http://ns.ontowiki.net/Extension/EasyInference/hasRule> <'.$rule.'> .'.
                    ' ?s <http://ns.ontowiki.net/Extension/EasyInference/hasModel> <'.$_app->selectedModel.'> .'.
                    ' ?s <http://ns.ontowiki.net/Extension/EasyInference/hasConcrete> <'.$_app->selectedResource.'>';

            $result = $this->_owApp->erfurt->getStore()->sparqlQuery
                    (Erfurt_Sparql_SimpleQuery::initWithString
                    ('PREFIX : <http://ns.ontowiki.net/Extension/EasyInference/>'.
                    ' PREFIX rule: <http://ns.ontowiki.net/Extension/EasyInference/InfRule/>'.
                    ' SELECT DISTINCT ?s'.
                    ' FROM <'.$fromGraph.'>'.
                    ' WHERE {'.
                    $conditions.
                    ' }'));

            if ($result) {
                foreach ($result as $r)
                    $this->_owApp->erfurt->getStore()->deleteMatchingStatements($fromGraph, $r['s'], null, null);
            }

            $msg .= "Die Regel wurde gelöscht.";

            //should the inferences be deleted to?
            if (($delete_directly == 1)) {
                $this->deleteInferences ($_app->selectedModel,
                        $this->getInferenceModel($_app->selectedModel),
                        $rule, $_app->selectedResource);
                $msg .= "\nDie Inferenzen wurden gelöscht.";
            }

        } else {
            $msg = 'Es wurde keine Regel zum Löschen ausgewählt.';
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('success'=>true, 'msg'=>$msg)));
    }

    /**
     * generate inferences according to activated rules
     */
    public function generateAction () {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $_app = $this->_owApp;
        $_model  = $_app->selectedModel;

        $inferenceModel = $this->makeNewInferenceModel($_model);

        $rules = InfRuleContainer::getInstance()->getRules();
        $msg='';
        $sparqlQuery =
                'PREFIX : <http://ns.ontowiki.net/Extension/EasyInference/>'.
                ' PREFIX rule: <http://ns.ontowiki.net/Extension/EasyInference/InfRule/>'.
                ' SELECT DISTINCT ?s ?r'.
                ' FROM <'.$this->_owApp->erfurt->getConfig()->sysont->modelUri.'>'.
                ' WHERE {'.
                ' ?u a rule:Applied .'.
                ' ?u :hasRule ?r .'.
                ' ?u :hasModel <'.$_model.'> .'.
                ' ?u :hasConcrete ?s'.
                ' }';

        $appliedRules = $this->_owApp->erfurt->getStore()->sparqlQuery(Erfurt_Sparql_SimpleQuery::initWithString($sparqlQuery));
        $this->_owApp->logger->debug("found ".count($appliedRules)." rules.");
        foreach ($appliedRules as $result) {
            $rule = $rules[$result['r']];
            if (!$rule) {
                $msg .= '{'.$result['r'].'} NOT FOUND'.PHP_EOL;
                $this->_owApp->logger->warn($msg);
                continue;
            }

            //test if the rule applies to the model or the resource
            if ($result['s'] != (string)$_model) {
                $resource = $result['s'];
            } else {
                $resource = null;
            }

            //generate the inference queries
            $queryFromRule = $rule->getQueryFromRule($resource, array('disallowBoundedTripel'=>true));

            $this->_owApp->logger->debug( "the query for rule ".$result['r'].": \n".$queryFromRule );
            //push to the inference model
            $this->addInferences($_model,$inferenceModel,$queryFromRule);

            $msg .= $rule->getName().' angewendet.'.PHP_EOL;
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('success'=>true, 'msg'=>$msg)));
    }


    /**
     * update the inferences
     */
    public function refreshAction () {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $rules = InfRuleContainer::getInstance()->getRules();
        $msg='';
        foreach ($this->_owApp->erfurt->getStore()->sparqlQuery
        (Erfurt_Sparql_SimpleQuery::initWithString
        ('PREFIX : <http://ns.ontowiki.net/Extension/EasyInference/>'.
        ' PREFIX rule: <http://ns.ontowiki.net/Extension/EasyInference/InfRule/>'.
        ' SELECT DISTINCT ?s ?r ?m'.
        ' FROM <'.$this->_owApp->erfurt->getConfig()->sysont->modelUri.'>'.
        ' WHERE {'.
        ' ?u a rule:Applied .'.
        ' ?u :hasRule ?r .'.
        ' ?u :hasModel ?m .'.
        ' ?u :hasConcrete ?s'.
        ' }'), array('use_ac'=>false)) as $result) {
            $rule = $rules[$result['r']];
            if (!$rule) {
                $msg .= '{'.$result['r'].'} NICHT GEFUNDEN!'.PHP_EOL;
                continue;
            }

            $_model = null;
            try {
                $_model = $this->_owApp->erfurt->getStore()->getModel($result['m'], false);
            } catch (Erfurt_Store_Exception $e) {
                $msg .= ' ERROR { MODEL '.$result['m'].' NOT FOUND } '.PHP_EOL;
                continue;
            }

            $inferenceModel = $this->makeNewInferenceModel($_model);

            if ($result['s'] != (string)$_model)
                $resource = $result['s'];
            else
                $resource = null;

            $result = $rule->getQueryFromRule($resource, array('disallowBoundedTripel'=>true));

            $this->addInferences($_model,$inferenceModel,$result, false);

            $msg .= '{'.$rule->getName().(($resource) ? ' on '.$resource : ' ').' at model '.$_model.' } angewendet.'.PHP_EOL;
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('success'=>true, 'msg'=>$msg)));
    }

    /**
     * checks, if a model has changed
     */
    public function isChangedAction () {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $_model  = $this->_owApp->selectedModel;
        $changed = false;

        if (hasChanged($_model, $this->getInferenceModel($_model)))
            $changed = true;

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('changed'=>$changed)));
    }

    /**
     * activate/deactivate a inference model. thereby an hidden import in the sysont is set/unset
     */
    public function activateAction () {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $_model = $this->_owApp->selectedModel;
        $infModel = $this->getInferenceModel ($_model);
        $activate = $this->getParam('activate', false) == 'true' ? true : false;

        if ($activate)
            $this->_owApp->erfurt->getSysOntModel()->addStatement($_model->getModelIri(),
                    $this->_owApp->erfurt->getConfig()->sysont->properties->hiddenImports,
                    array('type'=>'uri','value'=>$infModel->getModelIri()));
        else {
            $this->_owApp->erfurt->getSysOntModel()->deleteStatement($_model->getModelIri(),
                    $this->_owApp->erfurt->getConfig()->sysont->properties->hiddenImports,
                    array('type'=>'uri','value'=>$infModel->getModelIri()));
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('success'=>true)));
    }

    /** return true, if an inference model is imported from the source model
     */
    public function getactivestateAction () {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        $result = false;
        $_model = $this->_owApp->selectedModel;

        try {
            $infModel = $this->_owApp->erfurt->getStore()->getModel($_model->getModelIri().'inference/');

            $query = Erfurt_Sparql_SimpleQuery::initWithString(' ASK '.
                    ' FROM <'.$this->_owApp->erfurt->getSysOntModel()->getModelIri().'>'.
                    ' WHERE { <'.$_model->getModelIri().'>'.
                    ' <'.$this->_owApp->erfurt->getConfig()->sysont->properties->hiddenImports.'>'.
                    ' <'.$infModel->getModelIri().'> }');

            if ($this->_owApp->erfurt->getStore()->sparqlQuery($query)) {
                $result = true;
            }

        } catch (Erfurt_Store_Exception $e) {
            $result = false;
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode(array('active'=>$result)));
    }

    /**
     * get inference model, create new inference model when none exists
     */
    private function getInferenceModel ($sourceModel) {
        try {
            return $this->_owApp->erfurt->getStore()->getModel($sourceModel->getModelIri().'inference/');
        }
        catch (Erfurt_Store_Exception $e) { // didn't exist yet
            $this->_owApp->logger->warn("exception in getInferenceModel");
            $this->setupInferenceModel($sourceModel);
            return $this->createInferenceModel($sourceModel);
        }
    }

    /**
     * deletes old and makes new inference model
     */
    private function makeNewInferenceModel ($sourceModel) {
        try {
            $this->_owApp->erfurt->getStore()->getModel($sourceModel->getModelIri().'inference/', false);
            $this->_owApp->erfurt->getStore()->deleteModel($sourceModel->getModelIri().'inference/', false);
        }
        catch (Erfurt_Store_Exception $e) { // didn't exist yet
            $this->_owApp->logger->warn("exception in makeNewInferenceModel");
        }
        $this->setupInferenceModel($sourceModel);
        return $this->createInferenceModel($sourceModel);
    }


    /**
     * creates an inference model
     * @param $sourceModel base model for the inferences
     */
    private function createInferenceModel ($sourceModel) {
        $store = $this->_owApp->erfurt->getStore();
        $infModelIri = $sourceModel->getModelIri().'inference/';


        // setup inference model & label
        $infModel = $store->getNewModel($infModelIri);

        $title = $sourceModel->getTitle();

        if(empty($title)) {
            $iri =  $sourceModel->getModelIri();
            if(strrpos($iri, "#")>0) {
                $title = substr($iri,strrpos($iri, "#")+1);
            }else  if(strrpos($iri, "/")>0) {
                $title = substr($iri,strrpos($iri, "/")+1);
            }
        }

        $store->addStatement($infModelIri, $infModelIri, EF_RDFS_LABEL,
                array('type'=>'literal','lang'=>'en','value'=>$title.'-inferences'));
        $store->addStatement($infModelIri, $infModelIri, EF_RDFS_LABEL,
                array('type'=>'literal','lang'=>'de','value'=>$title.'-Inferenzen'));

        $this->setChangeLog ($sourceModel, $infModel);

        return $infModel;
    }

    /**
     * sets up the inference model in the sysOnt
     */
    private function setupInferenceModel ($sourceModel) {
        $store = $this->_owApp->erfurt->getStore();
        $infModelIri = $sourceModel->getModelIri().'inference/';

        // hide inference model
        $_sysOnt = $this->_owApp->erfurt->getConfig()->sysont->modelUri;
        $store->addStatement($_sysOnt, $infModelIri,
                $this->_owApp->erfurt->getConfig()->sysont->properties->hidden,
                array('type'=>'literal','value'=>'true'), false);

        // from Erfurt_Rdf_model with this comment: "TODO add this statement on model add?!"
        $store->addStatement($_sysOnt, $infModelIri, EF_RDF_TYPE,
                array('type'=>'uri','value'=>'http://ns.ontowiki.net/SysOnt/Model'), false);

        // import inferences to base model
        $store->addStatement($_sysOnt, $sourceModel->getModelIri(),
                $this->_owApp->erfurt->getConfig()->sysont->properties->hiddenImports,
                array('type'=>'uri','value'=>$infModelIri), false);
    }

    /**
     * Generate inferences and store them
     * @param $sourceModel base model for the inferences
     * @param $inferenceModel model to store the inferences into
     * @param $query the query for getting inferences
     * @param $useAc if false, the store ignores the user account
     */
    private function addInferences ( $sourceModel, $inferenceModel, $query, $useAc = true ) {
        $query->addFrom($sourceModel->getModelIri());

        if ($results = $this->_owApp->erfurt->getStore()->sparqlQuery($query, array('use_ac'=>$useAc))) {
            $addArray = array();
            $count = 0;

            foreach ($results as $data) {
                if (!array_key_exists($data['subject'], $addArray))
                    $addArray[$data['subject']] = array();

                if (!array_key_exists($data['predicate'], $addArray[$data['subject']]))
                    $addArray[$data['subject']][$data['predicate']] = array ();

                $addArray[$data['subject']][$data['predicate']][] = array ('type'=>'uri',
                        'value'=>$data['object']);
                if ($count > 200) {
                    $count = 0;
                    $this->_owApp->erfurt->getStore()->addMultipleStatements ($inferenceModel->getModelIri (), $addArray, $useAc);
                    $addArray = array();
                }

                $count ++;
            }

            try {
                $this->_owApp->erfurt->getStore()->addMultipleStatements ($inferenceModel->getModelIri (), $addArray, $useAc);
            } catch (Erfurt_Exception $e) {
                die(print_r($e));
            }
        }
    }

    /**
     * deletes all Inferences, a rule has generated and restore inferences, who
     * are created by other activated rules too.
     *
     * @param $sourceModel the source model
     * @param $infModel the inference model
     * @param $rule the rule
     * @param $resource the resource the rule was applied
     */
    private function deleteInferences ( $sourceModel, $infModel, $rule, $resource ) {
        $resource = ((string)$sourceModel === (string)$resource) ? null : $resource;
        $rules = InfRuleContainer::getInstance()->getRules();
        $rule = $rules[$rule];
        if ($result = $this->_owApp->erfurt->getStore()->sparqlQuery ($rule->getQueryFromRule($resource))) {
            $deleteArray = array();
            $count = 0;
            foreach ($result as $data) {
                if (!array_key_exists($data['subject'], $deleteArray))
                    $deleteArray[$data['subject']] = array();

                if (!array_key_exists($data['predicate'], $deleteArray[$data['subject']]))
                    $deleteArray[$data['subject']][$data['predicate']] = array ();

                $deleteArray[$data['subject']][$data['predicate']][] = array ('type'=>'uri',
                        'value'=>$data['object']);
                if ($count > 200) {
                    $infModel->deleteMultipleStatements ($deleteArray);
                    $count = 0;
                    $deleteArray = array();
                }
                $count++;
            }

            $infModel->deleteMultipleStatements ($deleteArray);

            // restore inferences generated from other rules
            // get all active rules
            $activeRules = array ();
            foreach ($this->_owApp->erfurt->getStore()->sparqlQuery
            (Erfurt_Sparql_SimpleQuery::initWithString
            ('PREFIX : <http://ns.ontowiki.net/Extension/EasyInference/>'.
            ' PREFIX rule: <http://ns.ontowiki.net/Extension/EasyInference/InfRule/>'.
            ' SELECT DISTINCT ?r ?s'.
            ' FROM <'.$this->_owApp->erfurt->getConfig()->sysont->modelUri.'>'.
            ' WHERE { ?u a rule:Applied . ?u :hasRule ?r .'.
            ' ?u :hasModel <'.$sourceModel.'> .'.
            ' ?u :hasConcrete ?s }')) as $result) {
                if (array_key_exists($result['r'], $rules))
                    $activeRules[] = array('r'=>$rules[$result['r']], 's'=>$result['s']);
            }

            $settledConclusion = '';
            $tok = strtok (trim($rule->getConclusion()), ' ');
            do {
                if ($tok && $tok[0] !== '?')
                    $settledConclusion .= $tok;

                $tok = strtok (' ');
            } while($tok);

            /* compare the conclusion of all active rules with the conclusion of the
             * deleted rule. If a rule, which conclusion seems to produce the same inference as the
             * deleted rule, the inferences will be generated again, because they could be deleted                 
            */
            foreach ($activeRules as $rule) {
                $compareConclusion = '';
                $resource = ($rule['s'] === (string)$sourceModel) ? null : $rule['s'];
                $tok = strtok (trim($rule['r']->getConclusion()), ' ');
                do {
                    if ($tok && $tok[0] !== '?')
                        $compareConclusion .= $tok;

                    $tok = strtok (' ');
                } while($tok);

                if ($compareConclusion === '' || $settledConclusion === '' ||
                        $compareConclusion === $settledConclusion) {
                    $this->addInferences ( $sourceModel,
                            $infModel,
                            $rule['r']->getQueryFromRule($resource, array ('disallowBoundedTripel'=>true)));
                }
            }
        }
    }

    /**
     * checks wether a model has changed
     *
     * @param $model the model who is checked
     * @param $inferencemodel the model where the old history id is saved
     */
    private function hasChanged ($model, $inferenceModel) {
        $versioning = $this->_owApp->erfurt->getVersioning();
        $return = true;

        $history = $versioning->getHistoryForGraph($model);

        if (!$history)
            return false;

        $query = Erfurt_Sparql_SimpleQuery::initWithString
                (' SELECT DISTINCT ?v'.
                ' FROM <'.$inferenceModel.'>'.
                ' WHERE { <'.$inferenceModel.'>'.
                ' <http://ns.ontowiki.net/Extension/EasyInference/based-on-version>'.
                ' ?v }');

        $result = $this->_owApp->erfurt->getStore()->sparqlQuery ($query);

        if (!$result || $result[0]['v'] == $history[0]['id']) {
            $return = false;
        }

        return $return;
    }

    /**
     * Save the history id of a source model to the inference model
     * @param model, which history id is saved at the inference model
     * @param inferencemodel the model, where the history id is saved
     */
    private function setChangeLog ($model, $inferenceModel) {
        $versioning = $this->_owApp->erfurt->getVersioning();

        $history = $versioning->getHistoryForGraph($model);

        if (!$history)
            return false;

        $this->_owApp->erfurt->getStore ()->deleteMatchingStatements ($inferenceModel->getModelIri(),
                $inferenceModel->getModelIri (),
                'http://ns.ontowiki.net/Extension/EasyInference/based-on-version',
                null);

        $this->_owApp->erfurt->getStore ()->addStatement ($inferenceModel->getModelIri(),
                $inferenceModel->getModelIri (),
                'http://ns.ontowiki.net/Extension/EasyInference/based-on-version',
                array ('value'=>$history[0]['id'],'type'=>'literal'));

        return true;
    }

}
