<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The main ckan controller provides the register and the import action
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_ckan
 * @subpackage component
 */
class CkanController extends OntoWiki_Controller_Component
{
    /** @var OntoWiki */
    protected $_owApp = null;

    /** @var Zend_Controller_Response_Abstract */
    protected $response = null;

    /** @var POST uri from CKAN */
    protected $registerBaseUrl = "http://thedatahub.org/package/new";
    /**
     * Constructor
     */
    public function init()
    {
        // init controller variables
        $this->_owApp   = OntoWiki::getInstance();
        $this->store    = $this->_owApp->erfurt->getStore();
        $this->_config  = $this->_owApp->config;
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->request  = Zend_Controller_Front::getInstance()->getRequest();
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

        $resourceUri    = (string) $model;
        $resource       = new Erfurt_Rdf_Resource($resourceUri, $model);
        $description    = $resource->getDescription();
        $description    = $description[$resourceUri];
        $infoProperties = $this->_config->descriptionHelper->properties;

        // wanted CKAN parameter
        $parameter = array();
        $parameter['title'] = $model->getTitle();
        $parameter['url']   = $resourceUri;

        // go through the info properties and use the first value
        foreach ($infoProperties as $infoProperty) {
            if (!isset($parameter['description'])) {
                if (isset($description[$infoProperty][0]['value'])) {
                    $parameter['notes'] = $description[$infoProperty][0]['value'];
                }
            }
        }

        $getparams = '?';
        foreach ($parameter as $key => $value) {
            $getparams .= '&' . $key .'='. urlencode($value);
        }

        $redirectUrl = $this->registerBaseUrl . $getparams;
        //var_dump($redirectUrl);

        //var_dump($infoUris);
        //var_dump($description);
        $this->_response->setRedirect($redirectUrl, $code = 302);
    }
}
