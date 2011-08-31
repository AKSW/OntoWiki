<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The main ckan controller provides the register and (later) the browser action
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_ckan
 * @subpackage component
 */
class CkanController extends OntoWiki_Controller_Component
{
    /** @var CKAN registration uri base */
    protected $registerBaseUrl = "http://thedatahub.org/package/new";

    /**
     * Constructor
     */
    public function init()
    {
        // this provides many std controller vars and other stuff ...
        parent::init();
        // init controller variables
        $this->store    = $this->_owApp->erfurt->getStore();
        $this->_config  = $this->_owApp->config;
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->request  = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * search GUI to import CKAN packages into ontowiki
     * TODO: finish :)
     */
    public function browserAction()
    {
        $t = $this->_owApp->translate;
        $this->view->placeholder('main.window.title')->set($t->_('CKAN Package Browser'));
        $this->addModuleContext('main.window.ckan.browser');
        OntoWiki_Navigation::disableNavigation();
        echo "not finished yet"; return;

        //$scriptBaseUrl = $this->_owApp->extensionManager->getComponentUrl('ckan').'ckanjs/lib/';
        //$clientScript  = $scriptBaseUrl . 'client.js';
        //$this->view->headScript()->appendFile($clientScript);

        //$newIncludePath = ONTOWIKI_ROOT . '/extensions/ckan/api/';
        //set_include_path(get_include_path() . PATH_SEPARATOR . $newIncludePath);
        //require_once('Ckan_client.php');

        //// Create CKAN object
        //// Takes optional API key parameter. Required for POST and PUT methods.
        //$ckan        = new Ckan_client();
        //$search_term = 'street';
        //$result      = $ckan->search_package($search_term, array('limit' => 5, 'tags' => 'format-rdf'));
        //$packageIds  = $result->results;
        //foreach ($packageIds as $key => $id) {
            //$package = $ckan->get_package_entity($id);
            //$data[$id] = $package;
        //}

        //$this->view->data = $data;
    }

    /**
     * forwards to the CKAN registration page with some prefilled values
     */
    public function registerAction()
    {
        // this action needs no view
        $this->_helper->viewRenderer->setNoRender();
        // disable layout
        $this->_helper->layout()->disableLayout();

        // m (model) is automatically used and selected
        if ((!isset($this->request->m)) && (!$this->_owApp->selectedModel)) {
            throw new OntoWiki_Exception('No model pre-selected model and missing parameter m (model)!');
            exit;
        } else {
            $model = $this->_owApp->selectedModel;
        }

        // get model URI / resource and load description
        $resourceUri    = (string) $model;
        $resource       = new Erfurt_Rdf_Resource($resourceUri, $model);
        $description    = $resource->getDescription();
        $description    = $description[$resourceUri];

        // fill CKAN parameter
        $parameter = array();
        $parameter['title'] = $model->getTitle();
        $parameter['url']   = $resourceUri;

        // go through the model info properties and use the first value as
        // notes value for ckan
        $infoProperties = $this->_config->descriptionHelper->properties;
        foreach ($infoProperties as $infoProperty) {
            if (!isset($parameter['description'])) {
                if (isset($description[$infoProperty][0]['value'])) {
                    $parameter['notes'] = $description[$infoProperty][0]['value'];
                }
            }
        }

        // build GET URI from the parameter array to redirect
        $getparams = '?';
        foreach ($parameter as $key => $value) {
            $getparams .= '&' . $key .'='. urlencode($value);
        }
        $redirectUrl = $this->registerBaseUrl . $getparams;

        // redirect to CKANs dataset registration page
        $this->_response->setRedirect($redirectUrl, $code = 302);
    }
}

