<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Controller for OntoWiki Basicimporter Extension
 *
 * @category OntoWiki
 * @package  Extensions_Basicimporter
 * @author   Sebastian Tramp <mail@sebastian.tramp.name>
 */
class BasicimporterController extends OntoWiki_Controller_Component
{
    private $_model = null;
    private $_post = null;

    /**
     * init() Method to init() normal and add tabbed Navigation
     */
    public function init()
    {
        parent::init();

        OntoWiki::getInstance()->getNavigation()->disableNavigation();

        // provide basic view data
        $action = $this->_request->getActionName();
        $this->view->placeholder('main.window.title')->set('Import Data');
        $this->view->formActionUrl    = $this->_config->urlBase . 'basicimporter/' . $action;
        $this->view->formEncoding     = 'multipart/form-data';
        $this->view->formClass        = 'simple-input input-justify-left';
        $this->view->formMethod       = 'post';
        $this->view->formName         = 'importdata';
        $this->view->supportedFormats = $this->_erfurt->getStore()->getSupportedImportFormats();

        if (!$this->isSelectedModelEditable()) {
            return;
        } else {
            $this->_model = $this->_owApp->selectedModel;
        }

        // add a standard toolbar
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => 'Import Data', 'id' => 'importdata')
        )->appendButton(
            OntoWiki_Toolbar::RESET,
            array('name' => 'Cancel', 'id' => 'importdata')
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        if ($this->_request->isPost()) {
            $this->_post = $this->_request->getPost();
        }
    }

    public function rdfpasterAction()
    {
        $this->view->placeholder('main.window.title')->set('Paste RDF Content');

        if ($this->_request->isPost()) {
            $post = $this->_request->getPost();
            $filetype = $post['filetype-paste'];
            $file = tempnam(sys_get_temp_dir(), 'ow');
            $temp = fopen($file, 'wb');
            fwrite($temp, $this->getParam('paste'));
            fclose($temp);
            $locator  = Erfurt_Syntax_RdfParser::LOCATOR_FILE;

            try {
                $this->_import($file, $filetype, $locator);
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->_owApp->appendErrorMessage($message);
                return;
            }

            $this->_owApp->appendSuccessMessage('Data successfully imported.');
        }
    }

    public function rdfwebimportAction()
    {
        $this->view->placeholder('main.window.title')->set('Import RDF from the Web');

        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();
            $url      = $postData['location'] != '' ? $postData['location'] : (string)$this->_model;
            $filetype = 'rdfxml';
            $locator  = Erfurt_Syntax_RdfParser::LOCATOR_URL;

            try {
                $this->_import($url, $filetype, $locator);
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->_owApp->appendErrorMessage($message);
                return;
            }

            $this->_owApp->appendSuccessMessage('Data from ' . $url . ' successfully imported.');
        }
    }

    public function rdfuploadAction()
    {
        $this->view->placeholder('main.window.title')->set('Upload RDF Dumps');

        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();
            $upload = new Zend_File_Transfer();
            $filesArray = $upload->getFileInfo();

            $message = '';
            switch (true) {
                case empty($filesArray):
                    $message = 'upload went wrong. check post_max_size in your php.ini.';
                    break;
                case ($filesArray['source']['error'] == UPLOAD_ERR_INI_SIZE):
                    $message = 'The uploaded files\'s size exceeds the upload_max_filesize directive in php.ini.';
                    break;
                case ($filesArray['source']['error'] == UPLOAD_ERR_PARTIAL):
                    $message = 'The file was only partially uploaded.';
                    break;
                case ($filesArray['source']['error'] >= UPLOAD_ERR_NO_FILE):
                    $message = 'Please select a file to upload';
                    break;
            }

            if ($message != '') {
                $this->_owApp->appendErrorMessage($message);
                return;
            }

            $file = $filesArray['source']['tmp_name'];
            // setting permissions to read the tempfile for everybody
            // (e.g. if db and webserver owned by different users)
            chmod($file, 0644);
            $locator  = Erfurt_Syntax_RdfParser::LOCATOR_FILE;
            $filetype = 'auto';
            // guess file mime type
            if ($postData['filetype-upload'] != 'auto') {
                $filetype = $postData['filetype-upload'];
            } else {
                // guess file type extension
                $extension = strtolower(strrchr($filesArray['source']['name'], '.'));
                if ($extension == '.rdf' || $extension == '.owl') {
                    $filetype = 'rdfxml';
                } else if ($extension == '.n3') {
                    $filetype = 'ttl';
                } else if ($extension == '.json') {
                    $filetype = 'rdfjson';
                } else if ($extension == '.ttl') {
                    $filetype = 'ttl';
                } else if ($extension == '.nt') {
                    $filetype = 'ttl';
                }
            }

            try {
                $this->_import($file, $filetype, $locator);
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->_owApp->appendErrorMessage($message);
                return;
            }

            $this->_owApp->appendSuccessMessage('Data successfully imported.');
        }
    }

    private function _import($fileOrUrl, $filetype, $locator)
    {
        $modelIri = (string)$this->_model;

        try {
            $this->_erfurt->getStore()->importRdf($modelIri, $fileOrUrl, $filetype, $locator);
        } catch (Erfurt_Exception $e) {
            // re-throw
            throw new OntoWiki_Controller_Exception(
                'Could not import given model: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
