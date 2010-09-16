<?php

/**
 * OntoWiki module – Translate
 *
 *
 * @category   OntoWiki
 * @package    extensions_modules_translate
 * @author     Michael Martin <martin@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class TranslateModule extends OntoWiki_Module
{
    protected $session = null;
    protected $languages;
    protected $locale;
    protected $properties;

    public function init() {
        $this->session      = $this->_owApp->session;
        $this->languages    = $this->_privateConfig->languages;
        $this->properties    = $this->_privateConfig->properties;
        $this->locale       = $this->_owApp->config->languages->locale;
        $this->titleHelper  = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        $this->view->titleHelper = $this->titleHelper;
        $this->resource     = (string) $this->_owApp->selectedResource;

        }

    public function getContents() {
    	if (null === $this->_owApp->selectedModel) {
            return;
        }
        $titles = $this->getExistingTitles();
        $titles = $this->checkMissingTitles($titles);
        $this->view->titles = $titles;
        return $this->render('translate');
    }

    private function getExistingTitles() {

        $constraints = array();
        foreach ($this->properties as $property)  {
            $constraints[] = "?predicate = <" . $property . "> ";
        }
        $constraint = implode ("||" , $constraints);

        $query = " SELECT ?predicate ?object WHERE {
            <".$this->resource."> ?predicate ?object .
            FILTER (".$constraint.")
        }";

        $results = $this->_owApp->selectedModel->sparqlQuery($query, array('result_format' => "extended"));
        $translations = array();
        foreach ( $results['results']['bindings'] as $key => $result ) {

            $this->titleHelper->addResource($result['predicate']['value']);
            $translations[$result['predicate']['value']][$key]['title'] = $result['object']['value'];
            if (!empty($result['object']['xml:lang'])) {
                $translations[$result['predicate']['value']][$key]['lang'] = $result['object']['xml:lang'];
            }
            else {
                $translations[$result['predicate']['value']][$key]['lang'] = '';
            }

        }
        return $translations;

    }

    private function checkMissingTitles($translations) {

        $rdfLanguages = array();
        foreach ($translations as $property => $titles) {
            foreach ($this->languages as $key => $element) {
                $actualLanguage = $element->rdf;
                $hit = false;
                foreach($titles as $element) {
                    if ($element['lang'] == $actualLanguage) {
                        $hit = true;
                    }
                }
                if (!$hit) {
                    $translations[$property][] = array ('lang' =>  $actualLanguage);
                }

            }
        }
        return $translations;
    }
}


