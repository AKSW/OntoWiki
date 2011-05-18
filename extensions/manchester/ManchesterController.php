<?php
/**
 * Manchester Syntax Controller
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_manchester
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ManchesterController extends OntoWiki_Controller_Component {


    public function init() {
        parent::init();
    }

    /*
     * post a manchester string to save/edit the class
     */
    public function postAction() {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $response  = $this->getResponse();
        $request   = $this->_request;
        $output    = false;

        try {
            $model  = $this->_owApp->selectedModel;
            $store  = $this->_owApp->erfurt->getStore();
            //$store->addMultipleStatements((string) $model, $activity->toRDF());

            $classname = OntoWiki::getInstance()->selectedResource;
            //$resource = $response->r;
            $manchester = $request->manchester;

            $structuredOwl = $this->initParser($manchester);

            $triples = $structuredOwl->toTriples();

            $output   = array (
                'message' => 'class saved',
                'class'   => 'success',
                'out' => $triples
            );
        } catch (Exception $e) {
            // encode the exception for http response
            $output = array (
                'message' => $e->getMessage(),
                'class'   => 'error'
            );
            $response->setRawHeader('HTTP/1.1 500 Internal Server Error');
        }

        // send the response
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($output));
        $response->sendResponse();
        exit;
    }

    private function initParser($inputQuery) {
        require_once 'antlr/Php/antlr.php';
        $input = new ANTLRStringStream($inputQuery);
        $lexer = new Erfurt_Syntax_ManchesterLexer($input);
        $tokens = new CommonTokenStream($lexer);
        $parser = new Erfurt_Syntax_ManchesterParser($tokens);

        // call the correct parser method (currently it's classFrame)
        return $parser->classFrame();
    }


}
