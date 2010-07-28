<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * Controller for OntoWiki foaf component to display a foaf profile
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_foaf
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class FoafController extends OntoWiki_Controller_Component
{
       
    public function displayAction() {
        OntoWiki_Navigation::disableNavigation();
        $translate  = $this->_owApp->translate;
        
        $store = $this->_erfurt->getStore();
        $model = $this->_owApp->selectedModel;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);

        $prefixes = 'PREFIX foaf:<http://xmlns.com/foaf/0.1/> .
            PREFIX dc:<http://purl.org/dc/elements/1.1/> .
            PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $prefixes.'SELECT ?about ?date ?comment WHERE {
                <'.((string) $model).'> a foaf:PersonalProfileDocument .
                <'.((string) $model).'> foaf:primaryTopic ?about
                OPTIONAL{<'.((string) $model).'> dc:date ?date}
                OPTIONAL{<'.((string) $model).'> rdfs:comment ?comment}
            }';
        $result = $store->sparqlQuery($query);
        
        $titleHelper->addResource((string) $model);
        $this->view->hasError = false;
        if(isset($result[0])){
            $about = $result[0]['about'];
            $titleHelper->addResource($about);
            $this->view->about = $titleHelper->getTitle($about);
            $this->view->aboutUri = $about;
            if(isset($result[0]['date'])){
                if (($timestamp = strtotime($result[0]['date'])) === false) {
                    $this->view->date = $result[0]['date']; //unparsed
                } else {
                    $this->view->date = date('l dS \o\f F Y h:i:s A', $timestamp);
                }
            }
            if(isset($result[0]['comment'])){
                $this->view->comment = $result[0]['comment'];
            }
            $this->view->graph = $model;

            $resource = new OntoWiki_Model_Resource($model->getStore(), $model, $about, 100);
        $results = $resource->getValues();
        //var_dump($this->_privateConfig);

        $output = "";
        $titleHelper = new OntoWiki_Model_TitleHelper($model);
        $selProps = array();
        $simpleProps = array();
        foreach($this->_privateConfig->foafProperties->basic as $key => $value){
            $simpleProps[$key] = $value;
        }
        $personProps = array();
        foreach($this->_privateConfig->foafProperties->Persons as $key => $value){
            $personProps[$key] = $value;
        }
        $projectProps = array();
        foreach($this->_privateConfig->foafProperties->Projects as $key => $value){
            $projectProps[$key] = $value;
        }
        $selProps["basic"] = array();
        $selProps["persons"] = array();
        $selProps["projects"] = array();
        foreach($results[(string) ($model->getModelIri())] as $property => $values){
            if(in_array($property, $simpleProps) ||
               in_array($property, $personProps) ||
               in_array($property, $projectProps)){
                $titleHelper->addResource($property);
                //sort by cat
                if(in_array($property, $simpleProps)){
                    $cat = 'basic';
                } elseif(in_array($property, $personProps)){
                    $cat = 'persons';
                } elseif (in_array($property, $projectProps)) {
                    $cat = 'projects';
                }
                
                $selProps[$cat][$property] = $values;
                //fill titlehelper
                foreach($values as $value){
                    if(isset($value['url'])){
                        $titleHelper->addResource($value['uri']);
                    }
                }
            }
        }
        //add pics to side window (where modules reside)
        foreach($this->_privateConfig->foafProperties->Pics as $picPropUri){
            if(isset($results[(string) ($model->getModelIri())][$picPropUri])){
                foreach($results[(string) ($model->getModelIri())][$picPropUri] as $value){
                    $this->view->placeholder('main.window.innerwindows')->append($value['object'].'<br/>');
                }
            }
        }
        $this->view->selProps = $selProps;
        $this->view->titleHelper = $titleHelper;
        $this->view->urlBase = $this->_config->urlBase;
        $this->view->modelUri = (string) $model;
        foreach($this->_privateConfig->foafPropertyGivenName as $givenProp){
            if(isset($results[(string) ($model->getModelIri())][$givenProp])){
                $this->view->aboutGivenName = $results[(string) ($model->getModelIri())][$givenProp][0]['content'];
            }
        }
        foreach($this->_privateConfig->foafPropertySurname as $surProp){
            if(isset($results[(string) ($model->getModelIri())][$surProp])){
                $this->view->aboutSurName = $results[(string) ($model->getModelIri())][$surProp][0]['content'];
            }
        }
        
        if(isset($results[(string) ($model->getModelIri())][$this->_privateConfig->foafPropertyNickName])){
            $this->view->aboutNickName = $results[(string) ($model->getModelIri())][$this->_privateConfig->foafPropertyNickName][0]['content'];
        }

        } else {
            $this->view->hasError = true;
            $this->_owApp->appendMessage(
                    new OntoWiki_Message('The document does not contain a foaf profile', OntoWiki_Message::ERROR)
            );
        }
        $windowTitle = $translate->_('View FOAF Profile: '.$titleHelper->getTitle((string) $model));
        $this->view->placeholder('main.window.title')->set($windowTitle);
    }
    
    
}

