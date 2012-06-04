<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Semantic Sitemap plug-in controller
 *
 * @category   OntoWiki
 * @package    Extensions_Semanticsitemap
 * @author Sebastian Dietzold <sebastian@dietzold.de>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SemanticsitemapController extends OntoWiki_Controller_Component
{
    protected $_store = null;
    protected $_models = null;
    

    /**
    * inits the controller (e.g. for getting the config)
    *
    * @return void
    */
    public function init()
    {
        parent::init();

        // Disable layout (we want only output xml)
        $this->_helper->layout->disableLayout();

        // Needed here because of the automatic rendering should be deactivated
        //$this->_helper->viewRenderer->setNoRender(true);
        
        // Add the path of templates needed by this plugin
        //$this->view->addScriptPath(REAL_BASE.'plugins/SemanticSitemap/templates/');
        
        // get the default store from the registry
        $this->_store = Erfurt_App::getInstance()->getStore(); 

        // fetch the modellist from the store
        $this->_models = $this->_store->getAvailableModels();

        // todo: where to set the content type? currently we get a Quirks mode :-(
        $this->_response->setRawHeader('Content-Type: application/xml');
    }


    /**
    * Generates a semantic sitemap according to Cyganiak et.al. (ESWC2008)
    *
    * @return void
    */
    public function sitemapAction()
    {
        $owApp = OntoWiki::getInstance(); 
        // these dataset items are used by the template
        $datasets = array();
        foreach ($this->_models as $modelUri => $model) {
            $datasets[] = array(
                'datasetURI' => $modelUri,
                'datasetLabel' => $this->_store->getModel($modelUri)->getTitle(),
                'sparqlEndpoint' =>  $owApp->config->urlBase . 'service/sparql',
                'sparqlGraphName' => $modelUri,
                'sparqlEndpointLocation' =>  $owApp->config->urlBase . 'service/sparql',
                'dataDump' =>  $owApp->config->urlBase . 'model/export?output=xml&amp;m=' . urlencode($modelUri)
            );
        }

        // assign view var(s)
        $this->view->datasets = $datasets;
        $this->view->appname = OntoWiki::APPLICATION_NAME;
        $this->view->version = OntoWiki_Version::VERSION;

        // render view
        //$this->_response->setBody($this->view->render('default.php'));
    }

}

?>
