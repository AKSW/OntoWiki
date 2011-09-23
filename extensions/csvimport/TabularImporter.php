<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once("Importer.php");
/**
 * Tabular CSV Importer
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage Csvimport
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class TabularImporter extends Importer
{

    public function parseFile() {
        require_once 'CsvParser.php';
        $separator = $this->parserInstructions['separator'];
        $headlineDetection = $this->parserInstructions['headlineDetection'];
        $parser = new CsvParser($this->uploadedFile, $separator, $headlineDetection);
        $this->parsedFile = array_filter($parser->getParsedFile());

    }


    public function importData() {

        $ontowiki = OntoWiki::getInstance();
        $model = $ontowiki->selectedModel;
        $modelUri = $model->getModelUri();
        $prefixes = $model->getNamespaces();

        #Extraction of Definitions and parsed file
        $fragment = "";
        if(!empty($this->configuration['fragment'])) {
            $fragment = $this->configuration['fragment'];
        }
        $i=0;
        foreach($this->parsedFile as $entity){
            $resourceUri = $modelUri . $fragment . $i++;
            $predicates= array();

            $counter=-1;
            foreach ($entity as $key => $element) {
               $type = 'literal';
               $counter++;
               if (!empty($this->configuration['dimensions'][$counter])) {
                    $elementConf = $this->configuration['dimensions'][$counter];
                    #---------------------------------------------------------------
                    #RDF-Object generieren
                    if(!empty($elementConf['is_url'])) {
                        if(!empty($elementConf['contains_prefix'])) {
                            $grepedPrefix = substr($element, 0, strpos($element, ":"));

                            $namespace = "";
                            foreach ($prefixes as $ns => $prefix) {
                                $namespace = (strtolower($prefix) == strtolower($grepedPrefix)) ? $ns : "" ;
                            }

                            if(!empty($namespace)){
                                $element = str_replace($grepedPrefix.":",$namespace, $element);
                            }
                        }

                        if (parse_url($element) != FALSE) {
                            $type = 'uri';
                        }
                    }
                }

                $object = array(
                    'type' => $type,
                    'value' => $element
                    );

                #---------------------------------------------------------------
                #RDF-predicate generieren
                $predicateUri = $modelUri . "p/" . urlencode($key);
                if(!empty($elementConf['property'])) {
                    $predicateUri = $elementConf['property'];
                }

                if (!empty($element)){
                    $predicates[$predicateUri][] = $object ;
                }

                #---------------------------------------------------------------
                #chaning Resource Uri if necessary
                if(!empty($elementConf['contains_id'])) {
                    $resourceUri = $modelUri . $fragment . $element;
                }

            }
            $statements = array($resourceUri => $predicates);
            $model->addMultipleStatements($statements);
           
        }




/*        $config = array();
        $headers = array_keys($this->parsedFile[0]);
        $i = 0;
        foreach($headers as $element){

            $config[$element] = $this->configuration['dimensions'][$i++];
        }





        $i = 0;
        $url = $this->componentConfig->item->base . "/".hash("md5", serialize($this->parsedFile))."/";
        $elements = array();
        foreach($this->parsedFile as $line){
            foreach($line as $key => $value){
                $element = array();
                $element[$url.$i] = array(
                    $config[$key] => array(
                        array(
                        'type' => 'uri',
                        'value' => $value
                        )
                    )
                );
                $elements[] = $element;
                //echo "someuri".$i." + ".$config[$key]." + ".$value."\n";
            }
            $i++;
        }
        
        $ontowiki = OntoWiki::getInstance();
        foreach ($elements as $elem) {
            //print_r($elem);
            $ontowiki->selectedModel->addMultipleStatements($elem);
        }
*/    }

    public function createConfigurationView($urlBase) {
        $ontowiki = OntoWiki::getInstance();
        $model = $ontowiki->selectedModel;
        $this->view->placeholder('main.window.title')->append('Import CSV Data');
        $this->view->actionUrl = $urlBase . 'csvimport/mapping';
        $this->view->salt = hash("md5", serialize($this->parsedFile));
        $this->view->modelUri = (string)$model;
        OntoWiki_Navigation::disableNavigation();

        $toolbar = $ontowiki->toolbar;
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Extract Triples', 'id' => 'extract'))
            ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        $headers = array_keys($this->parsedFile[0]);
        $data = array();
        foreach ($headers as $element) {
            $data[] = array($element);
        }




        $this->view->table = $this->view->partial('importer/tabular.phtml', array(
                    'data' => $data,
                    'script' => $this->componentConfig->urlBase . 'extensions/csvimport/scripts/autosuggest.js',
                    'tableClass' => 'csvimport',
                    'examples' => $this->parsedFile[0],
                    'baseUri'  => $model->getModelUri(),
                    'prefixes' => $model->getNamespaces()
                ));
    }

}
