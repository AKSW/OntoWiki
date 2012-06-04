<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'OntoWiki/Module.php';
require_once 'OntoWiki/Url.php';
require_once 'OntoWiki/Utils.php';

/**
 * @category   OntoWiki
 * @package    Extensions_Savedqueries
 */
class SavedqueriesModule extends OntoWiki_Module
{
	const SERVICE_URL = 'savedQueries';
	
	public function getTitle() {
        return (string) $this->_privateConfig->title;
	}
	
	/**
	 * Returns the content for the model list.
	 */
	public function getContents() {
        $storeGraph = $this->_owApp->selectedModel;
        if (!$storeGraph) {
            return "";
        }
        $query = "SELECT * WHERE {
                ?query a <".$this->_privateConfig->queryClass."> .
                ?query <".$this->_privateConfig->queryLabel."> ?label .
                ?query <".$this->_privateConfig->queryId."> ?id .
                ?query <".$this->_privateConfig->queryDesc."> ?description .
                ?query <".$this->_privateConfig->queryCode."> ?code
        }";

        $elements = $storeGraph->sparqlQuery($query);
        $queries = array();
        foreach ($elements as $element) {
            $query = array();
            $query['label'] = $element['label'];
            $query['link'] = $this->_config->urlBase."savedqueries/init?&query=".(urlencode($element['code']))."&label=".(urlencode($query['label'])); //Link noch bauen
            $query['description'] = $element['description']; //Link noch bauen
            $queries[] = $query;
        }
        $this->view->queries      = $queries;
		$content = $this->render('savedqueries');
		
		return $content;
	}

	public function getStateId() {
	    $session = OntoWiki::getInstance()->session;
	    
        $id = $this->_owApp->selectedModel->getModelIri()
            . $this->_owApp->selectedClass
            . print_r($session->hierarchyOpen, true);
        
        return $id;
    }
    
    public function shouldShow()
    {
        if ($this->_owApp->selectedModel) {
            return true;
        }
        
        return false;
    }
    
    public function allowCaching()
    {
        // no caching
        // return false;
        return true;
    }

}


