<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Component controller for the CSV Importer.
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage Csvimport
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class CsvimportController extends OntoWiki_Controller_Component
{
    protected $_dimensions = null;
    protected $_columnMappings = null;
    protected $_targetGraph = null;
    
    public function init()
    {
        // init component
        parent::init();
        
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/csvimport.js');
    }
    
    public function indexAction()
    {
        $this->_saveData();

        // TODO: determine next step and forward accordingly
        $store = $this->_getSessionStore();
        
        if (isset($store->nextAction)) {
            $action = $store->nextAction;
            unset($store->nextAction);
            $this->_forward($action, 'csvimport');
        } else {
            $this->_forward('upload', 'csvimport');
        }
    }
    
    public function uploadAction()
    {        
        if (!$this->_request->isPost()) {
            // TODO: show import dialogue and import file
            $this->view->placeholder('main.window.title')->append('Import CSV Data');
            OntoWiki_Navigation::disableNavigation();

            $this->view->formActionUrl = $this->_config->urlBase . 'csvimport';
            $this->view->formEncoding  = 'multipart/form-data';
            $this->view->formClass     = 'simple-input input-justify-left';
            $this->view->formMethod    = 'post';
            $this->view->formName      = 'import';
            $this->view->referer       = isset($_SERVER['HTTP_REFERER']) ? urlencode($_SERVER['HTTP_REFERER']) : '';

            $this->view->modelUri   = (string)$this->_owApp->selectedModel;
            $this->view->title      = 'Import CSV Data';
            $model = $this->_owApp->selectedModel;
            $this->view->modelTitle = $model->getTitle();

            if ($model->isEditable()) {
                $toolbar = $this->_owApp->toolbar;
                $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Import CSV', 'id' => 'import'))
                        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
                $this->view->placeholder('main.window.toolbar')->set($toolbar);
            } else {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message("No write permissions on model '{$this->view->modelTitle}'", OntoWiki_Message::WARNING)
                );
            }
            
            // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
            // disable connection keep-alive
            $response = $this->getResponse();
            $response->setHeader('Connection', 'close', true);
            $response->sendHeaders();
            return;
        }
        
        // evaluate post data
        $messages = array();
        $post = $this->_request->getPost();
        $errorFlag = false;
        switch (true) {
            case (empty($_FILES['source']['name'])):
                $message = 'No file selected. Please try again.';
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                    );
                    $errorFlag = true;
                    break;
            case ($_FILES['source']['error'] == UPLOAD_ERR_INI_SIZE):
                $message = 'The uploaded files\'s size exceeds the upload_max_filesize directive in php.ini.';
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                    );
                    $errorFlag = true;
                    break;
            case ($_FILES['source']['error'] == UPLOAD_ERR_PARTIAL):
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('The uploaded file was only partially uploaded.', OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case ($_FILES['source']['error'] >= UPLOAD_ERR_NO_FILE):
                $message = 'There was an unknown error during file upload. Please check your PHP configuration.';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
        }
        
        /* handle upload */
        $tempFile = $_FILES['source']['tmp_name'];
        if (is_readable($tempFile)) {
            $store = $this->_getSessionStore();
            $store->importedFile = $tempFile;
            $store->importMode   = $post['importMode'];
            $store->nextAction   = 'mapping';
        }
        
        $this->_forward('index');
    }
    
    public function mappingAction()
    {
        if (!isset($this->_request->dimensions)) {
            $this->view->placeholder('main.window.title')->append('Import CSV Data');
            $this->view->actionUrl = $this->_config->urlBase . 'csvimport';
            OntoWiki_Navigation::disableNavigation();

            $model = $this->_owApp->selectedModel;
            if ($model->isEditable()) {
                $toolbar = $this->_owApp->toolbar;
                $toolbar->appendButton(OntoWiki_Toolbar::ADD, array('name' => 'Add Dimension', 'id' => 'btn-add-dimension'))
                        ->appendButton(OntoWiki_Toolbar::SEPARATOR)
                        ->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Extract Triples', 'id' => 'extract'))
                        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
                $this->view->placeholder('main.window.toolbar')->set($toolbar);
            } else {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message("No write permissions on model '{$this->view->modelTitle}'", OntoWiki_Message::WARNING)
                );
            }

            // TODO: show table and let user define domain mapping
            $store = $this->_getSessionStore();

            if (is_readable($store->importedFile)) {
                require_once 'CsvParser.php';
                $parser = new CsvParser($store->importedFile);
                $data   = array_filter($parser->getParsedFile());
                $this->view->table = $this->view->partial(
                    'partials/table.phtml',  array(
                        'data' => $data, 
                        'tableClass' => 'csvimport'
                    )
                );
            }
            
            $store = $this->_getSessionStore();
            $store->nextAction = 'mapping';
        } else {
            // $json = $this->_request->getPost('dimensions');
            $json = $_POST['dimensions'];
            $data = json_decode($json, true);
            $store = $this->_getSessionStore();
            $store->dimensions = $data;
            $this->_createDimensions($data);
            $this->_helper->viewRenderer->setNoRender();
            unset($store->nextAction);
        }
    }
    
    protected function resultsAction()
    {
        
    }
    
    protected function _getSessionStore()
    {
        $session = new Zend_Session_Namespace('CSV_IMPORT_SESSION');
        return $session;
    }
    
    protected function _getColumnMapping()
    {
        $columnMapping = array(
            array(
                'property' => 'http://xmlns.com/foaf/0.1/name', 
                'label' => 'Name', 
                'col' => 3, 
                'row' => 2, 
                'items' => array(
                    'type' => 'uri', 
                    'class' => 'http://xmlns.com/foaf/0.1/Person', 
                    'start' => array('col' => 3, 'row' => 2), 
                    'end' => array('col' => 3, 'row' => 20)
                ), 
            ), 
            array(
                'property' => 'http://purl.org/dc/elements/1.1/', 
                'label' => 'Titel', 
                'col' => 4, 
                'row' => 2,
                'items' => array(
                    'type' => 'literal', 
                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string', 
                    'start' => array('col' => 4, 'row' => 2), 
                    'end' => array('col' => 4, 'row' => 20)
                )
            )
        );
    }
    
    protected function _getDimensions()
    {
        $dimensions = array(
            'http://example.com/dimension1' => array(
                'label' => 'Age', 
                'elements' => array(
                    'http://example.com/dimension1/0-6' => array(
                        'col' => 2, 
                        'row' => 2, 
                        'label' => '0-6', 
                        'items' => array(
                            'start' => array('col' => 2, 'row' => 3), 
                            'end'   => array('col' => 2, 'row' => 20)
                        )
                    ),
                    'http://example.com/dimension1/7-12' => array(
                        'col' => 3,
                        'row' => 2,
                        'label' => '7-12',
                        'items' => array(
                            'start' => array('col' => 3, 'row' => 3),
                            'end'   => array('col' => 3, 'row' => 20)
                        )
                    )
                )
            ),  
            'http://example.com/dimension2' => array(
                'label' => 'Region', 
                'elements' => array(
                    'http://example.com/dimension2/Africa' => array(
                        'col' => 1, 
                        'row' => 3, 
                        'label' => 'Africa', 
                        'items' => array(
                            'start' => array('col' => 2, 'row' => 3), 
                            'end'   => array('col' => 2, 'row' => 20)
                        )
                    )
                )
            )
        );
        
        return $dimensions;
    }

    protected function _createDimensions($dimensions)
    {
        $elements = array();

        // relations
        $type = 'http://www.w3.org/2000/01/rdf-schema#type';
        $subClassOf = 'http://www.w3.org/2000/01/rdf-schema#subClassOf';
        $scvDimension = 'http://purl.org/NET/scovo#Dimension';
        $title = 'http://purl.org/dc/elements/1.1/title';
        
        foreach ($dimensions as $url => $dim) {
            $element = array();

            // class
            $element[$url] = array(
                $subClassOf => array(
                    array(
                        'type' => 'uri',
                        'value' => $scvDimension
                        )
                    )
                );
            $elements[] = $element;

            // label
            $element[$url] = array(
                $title => array(
                    array(
                        'type' => 'literal',
                        'value' => $dim['label']
                        )
                    )
                );
            $elements[] = $element;
            
            // types
            foreach ($dim['elements'] as $eurl => $elem) {
                $element = array();
                
                // type of new dimension
                $element[$eurl] = array(
                    $type => array(
                        array(
                            'type' => 'uri',
                            'value' => $url
                            )
                        )
                    );
                $elements[] = $element;
                // label
                $element[$eurl] = array(
                    $title => array(
                        array(
                            'type' => 'literal',
                            'value' => $elem['label']
                            )
                        )
                    );
                $elements[] = $element;
            }
        }

        foreach ($elements as $elem) {
            $this->_owApp->selectedModel->addMultipleStatements($elem);
        }

        //echo '<pre>';
        //echo print_r( $elements );
        //echo '</pre>';
    }

    protected function _saveData(){
        require 'CsvParser.php';
        $parser = new CsvParser("test.csv");
        $data = $parser->getParsedFile();

        $dimensions = $this->_getDimensions();
        $dims = array();

        $subDimension = 'http://purl.org/NET/scovo#dimension';
        $value = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value';

        foreach($dimensions as $url => $dim){
            foreach($dim['elements'] as $eurl => $elem){
                $dims[] = array(
                    'uri' => $eurl,
                    'row' => $elem['row'],
                    'col' => $elem['col'],
                    'items' => $elem['items']
                );
            }
        }

        foreach($data as $rowIndex => $row){
            // check for null data
            if(!isset($row) || $row == null) continue;

            // parse row
            foreach($row as $colIndex => $cell){
                // filter empty
                if(strlen($cell) > 0){
                    // create new item dimensions arr
                    $itemDims = array();

                    // fill item dimensions from all dims
                    foreach($dims as $dim){
                        if(
                            $colIndex >= $dim['items']['start']['col'] && $colIndex <= $dim['items']['end']['col'] &&
                            $rowIndex >= $dim['items']['start']['row'] && $rowIndex <= $dim['items']['end']['row']
                        ){
                            if($dim['col'] == $colIndex || $dim['row'] == $rowIndex){
                                $itemDims[$subDimension] = array(
                                        'type' => 'uri',
                                        'value' => $dim['uri']
                                    );
                            }
                        }
                    }

                    // if there is some dimensions
                    if(count($itemDims) > 0){
                        $eurl = "http://example.com/item-c".$colIndex."-r".$rowIndex;
                        $element[$eurl] = array(
                            $itemDims,
                            $value => array(
                                array(
                                    'type' => 'literal',
                                    'value' => $cell
                                )
                            )
                        );

                        // write element
                        $this->_owApp->selectedModel->addMultipleStatements($element);
                    }
                }
            }
        }

        //echo "<pre>";
        //print_r( $dims );
        //echo "</pre>";
    }
}
