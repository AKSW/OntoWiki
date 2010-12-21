<?php

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: FilesController.php 4090 2009-08-19 22:10:54Z christian.wuerker $
 */
class FilesController extends OntoWiki_Controller_Component
{
    protected $_configModel;
    
    /**
     * Default action. Forwards to get action.
     */
    public function __call($action, $params)
    {
        $this->_forward('get', 'files');
    }
    
    
    public function deleteAction()
    {
        if ($this->_request->isPost()) {
            // delete file resources
            foreach ($this->_request->getPost('selectedFiles') as $fileUri) {
                $fileUri = rawurldecode($fileUri);
                
                $store = $this->_owApp->erfurt->getStore();

                // remove all statements from sysconfig
                $store->deleteMatchingStatements(
                    (string) $this->_getConfigModelUri(),
                    $fileUri ,
                    null ,
                    null
                );
                
                // remove file from file system
                $pathHashed = _OWROOT
                            . $this->_privateConfig->path
                            . DIRECTORY_SEPARATOR
                            . md5($fileUri);
                unlink($pathHashed);
            }
            
            $url = new OntoWiki_Url(array('controller' => 'files', 'action' => 'manage'), array());
            $this->_redirect((string) $url);
        }
    }
    
    public function getAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        // TODO: check acl
        $fileUri      = $this->_config->urlBase . ltrim($this->_request->getPathInfo(), '/');
        $mimeProperty = $this->_privateConfig->mime->property;
        $store        = $this->_owApp->erfurt->getStore();
        
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setProloguePart('SELECT DISTINCT ?mime_type')
              ->addFrom((string) $this->_getConfigModelUri())
              ->setWherePart('
              WHERE {
                  <' . $fileUri . '> <' . $mimeProperty . '> ?mime_type.
              }');
        
        if ($result = $store->sparqlQuery($query, array('use_ac' => false))) {
            $mimeType = $result[0]['mime_type'];
        } else {
            $mimeType = 'text/plain';
        }
        
        $response = $this->getResponse();
        $response->setRawHeader('Content-Type:' . $mimeType);
        $pathHashed = _OWROOT
                    . $this->_privateConfig->path
                    . DIRECTORY_SEPARATOR
                    . md5($fileUri);
        if (is_readable($pathHashed)) {
            $response->setBody(file_get_contents($pathHashed));
        }
    }
    
    public function manageAction()
    {        
        $mimeProperty = $this->_privateConfig->mime->property;
        $fileClass    = $this->_privateConfig->class;
        $fileModel    = $this->_privateConfig->model;
        $store        = $this->_owApp->erfurt->getStore();
        
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setProloguePart('SELECT DISTINCT ?mime_type ?uri')
              ->addFrom((string) $this->_getConfigModelUri())
              ->setWherePart('
              WHERE {
                  ?uri a <' . $fileClass . '>.
                  ?uri <' . $fileModel . '> <' . (string) $this->_owApp->selectedModel . '>.
                  ?uri <' . $mimeProperty . '> ?mime_type.
              }')
              ->setOrderClause('?uri')
              ->setLimit(10); // TODO: paging
        
        if ($result = $store->sparqlQuery($query, array('use_ac' => false))) {
            $files = array();
            foreach ($result as $row) {
                if (is_readable($this->getFullPath($row['uri']))) {
                    array_push($files, $row);
                }
            }
            $this->view->files = $files;
        } else {
            $this->view->files = array();
        }
        
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('File Manager'));
        OntoWiki_Navigation::disableNavigation();
        
        $toolbar = $this->_owApp->toolbar;
        
        $filePath = _OWROOT 
                  . rtrim($this->_privateConfig->path, '/') 
                  . DIRECTORY_SEPARATOR;
        
        $url = new OntoWiki_Url(array('controller' => 'files', 'action' => 'upload'), array());

        if (is_writable($filePath)) {

            $toolbar->appendButton(
                OntoWiki_Toolbar::DELETE, 
                array('name' => 'Delete Files', 'class' => 'submit actionid', 'id' => 'filemanagement-delete')
            );

            $toolbar->appendButton(
                OntoWiki_Toolbar::ADD,
                array('name' => 'Upload File', 'class' => 'upload-file', 'url' => (string) $url)
            );

            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            $msgString = sprintf(
                $this->_owApp->translate->_('Directory "%s" is not writeable. To upload files set it writable.'),
                rtrim($this->_privateConfig->path, '/') . DIRECTORY_SEPARATOR
            );
            $this->_owApp->appendMessage(
                new OntoWiki_Message($msgString, OntoWiki_Message::INFO)
            );
        }
        
        if (!defined('ONTOWIKI_REWRITE')) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Rewrite mode is off. File URIs may not be accessible.', OntoWiki_Message::WARNING)
            );
            return;
        }
         
        $url->action = 'delete';
        $this->view->formActionUrl = (string) $url;
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'filemanagement-delete';
    }
    
    public function uploadAction()
    {
        // default file URI
        $defaultUri = $this->_config->urlBase . 'files/';
        
        // store for sparql queries
        $store        = $this->_owApp->erfurt->getStore();

        // DMS NS var
        $DMS_NS = $this->_privateConfig->DMS_NS;

        // check if DMS needs to be imported
        if ($store->isModelAvailable($DMS_NS) && $this->_privateConfig->import_DMS) {
            $this->_checkDMS();
        }
            
        
        $url = new OntoWiki_Url(array('controller' => 'files', 'action' => 'upload'), array());
        
        // check for POST'ed data
        if ($this->_request->isPost()) {
            if ($_FILES['upload']['error'] == UPLOAD_ERR_OK) {
                // upload ok, move file
                $fileUri  = $this->_request->getPost('file_uri');
                $fileName = $_FILES['upload']['name'];
                $tmpName  = $_FILES['upload']['tmp_name'];
                $mimeType = $_FILES['upload']['type'];
                
                // check for unchanged uri
                if ($fileUri == $defaultUri) {
                    $fileUri = $defaultUri
                             . 'file'
                             . (count(scandir(_OWROOT . $this->_privateConfig->path)) - 2);
                }
                
                // build path
                $pathHashed = _OWROOT
                            . $this->_privateConfig->path
                            . DIRECTORY_SEPARATOR
                            . md5($fileUri);
                
                // move file
                if (move_uploaded_file($tmpName, $pathHashed)) {
                    $mimeProperty = $this->_privateConfig->mime->property;
                    $fileClass    = $this->_privateConfig->class;
                    $fileModel    = $this->_privateConfig->model;
                    
                    // use super class as default
                    $fileClassLocal = 'http://xmlns.com/foaf/0.1/Document';

                    // use mediaType-ontologie if available                    
                    if ($store->isModelAvailable($DMS_NS)) {
                        $allTypes = $store->sparqlQuery(
                            Erfurt_Sparql_SimpleQuery::initWithString(
                                'SELECT * FROM <' . $DMS_NS . '>
                                WHERE {
                                    ?type a <' . EF_OWL_CLASS . '> .
                                    OPTIONAL { ?type <' . $DMS_NS . 'mimeHint> ?mimeHint . }
                                    OPTIONAL { ?type <' . $DMS_NS . 'suffixHint> ?suffixHint . } 
                                } ORDER BY ?type'
                            )
                        );

                        $mimeHintArray = array();
                        $suffixHintArray = array();

                        // check for better suited class
                        foreach ($allTypes as $singleType) {
                            if (!empty($singleType['mimeHint'])) {
                                $mimeHintArray[$singleType['mimeHint']]     = $singleType['type'];
                            }
                            if (!empty($singleType['suffixHint'])) {
                                $suffixHintArray[$singleType['suffixHint']]   = $singleType['type'];
                            }
                        }

                        $suffixType = substr($fileName ,strrpos($fileName,'.'));
                        if (array_key_exists($suffixType, $suffixHintArray)) {
                            $fileClassLocal = $suffixHintArray[$suffixType];
                        }

                        if (array_key_exists($mimeType, $mimeHintArray)) {
                            $fileClassLocal = $mimeHintArray[$mimeType];
                        }
                    }

                    // add file resource as instance in local model
                    $store->addStatement(
                        (string) $this->_owApp->selectedModel ,
                        $fileUri ,
                        EF_RDF_TYPE ,
                        array('value' => $fileClassLocal, 'type' => 'uri')
                    );
                    // add file resource as instance in system model
                    $store->addStatement(
                        (string) $this->_getConfigModelUri(),
                        $fileUri ,
                        EF_RDF_TYPE ,
                        array('value' => $fileClass, 'type' => 'uri'),
                        false
                    );
                    // add file resource mime type
                    $store->addStatement(
                        (string) $this->_getConfigModelUri(),
                        $fileUri ,
                        $mimeProperty ,
                        array('value' => $mimeType, 'type' => 'literal') , 
                        false
                    );
                    // add file resource model
                    $store->addStatement(
                        (string) $this->_getConfigModelUri(), 
                        $fileUri , 
                        $fileModel ,
                        array('value' => (string) $this->_owApp->selectedModel, 'type' => 'uri') ,
                        false
                    );
                    
                    $url->action = 'manage';
                    $this->_redirect((string) $url);
                }
            } else {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error during file upload.', OntoWiki_Message::ERROR)
                );
            }
        }
        
        // $this->_helper->viewRenderer->setNoRender();
        // $this->_helper->layout->disableLayout();
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Upload File'));
        OntoWiki_Navigation::disableNavigation();
        
        $toolbar = $this->_owApp->toolbar;
        $url->action = 'manage';
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Upload File'))
                ->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'File Manager', 'class' => '', 'url' => (string) $url));
        
        $this->view->defaultUri = $defaultUri;
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $url->action = 'upload';
        $this->view->formActionUrl = (string) $url;
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'fileupload';
		$this->view->formEncoding  = 'multipart/form-data';
		
		if (!is_writable($this->_privateConfig->path)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Uploads folder is not writable.', OntoWiki_Message::WARNING)
            );
            return;
        }
        
        // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
	    header('Connection: close');
    }
    
    protected function getFullPath($fileUri)
    {
        $pathHashed = _OWROOT
                    . $this->_privateConfig->path
                    . DIRECTORY_SEPARATOR
                    . md5($fileUri);
        
        return $pathHashed;
    }

    /**
     * method to check import of DMS Schema in current model
     */
    private function _checkDMS() {

        $store        = $this->_owApp->erfurt->getStore();

        // checking if model is imported
        $allImports = $this->_owApp->selectedModel->sparqlQuery(
            Erfurt_Sparql_SimpleQuery::initWithString(
                'SELECT * 
                WHERE {
                    <' . (string) $this->_owApp->selectedModel . '> <' . EF_OWL_IMPORTS . '> ?import .
                }'
            )
        );

        // import if missing
        if (!in_array(array('import' => $this->_privateConfig->DMS_NS), $allImports)) {
            $this->_owApp->selectedModel->addStatement(
                (string) $this->_owApp->selectedModel,
                EF_OWL_IMPORTS, 
                array('value' => $this->_privateConfig->DMS_NS, 'type' => 'uri'), 
                false
            );
        } else {
            // do nothing
        }

    }
    
    protected function _getConfigModelUri()
    {
        if (null === $this->_configModel) {
            $this->_configModel = Erfurt_App::getInstance()->getConfig()->sysont->modelUri;
        }
        
        return $this->_configModel;
    }
}

