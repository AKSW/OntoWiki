<?php
/**
 * Controller for OntoWiki Pattern Manager Module
 *
 * @category   OntoWiki
 * @package    extensions_components_patternmanager
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'classes/BasicPattern.php';
require_once 'classes/ComplexPattern.php';
require_once 'classes/PatternEngine.php';

class PatternmanagerController extends OntoWiki_Controller_Component {

    /**
     * Component global init function
     */
    public function init() {
    
        parent::init();
        
        OntoWiki_Navigation::disableNavigation();
        
        $this->view->placeholder('main.window.title')->set('OntoWiki Evolution Pattern Engine');
        
    }
    
    /**
     * Shows an index page as entry portal for evolution pattern management
     */
    public function indexAction () {

        $this->view->componentUrlBase = $this->_componentUrlBase;

        $this->view->url = array();

        $url = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'browse') );
        $this->view->url['browse'] = $url;
        
        $url = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'view') );
        $this->view->url['view'] = $url;
        
        
        
    }
    
    /**
     * 
     */
    public function execAction() {
        
        if (defined('_OWDEBUG')) {
            $start = microtime(true);
        }

        $json = '';
        
        $data = $this->loadPatternFromUri($this->_request->getParam('pattern'));
        
        if ( sizeof($data) > 0 && array_key_exists($this->_privateConfig->hasJson, $data) ) {
            $json   = current($data[$this->_privateConfig->hasJson]);
        } else {
            $json = '{}';
        }

        $complexPattern = new ComplexPattern();
        $complexPattern->parseFromJson($json);
        $unboundVariables = $complexPattern->getVariables(false);
        
        $var = $this->getParam('var');

        if (!empty($var) && is_array($var)) {
            
            foreach ($var as $name => $value) {
                unset($unboundVariables[$name]);
                $complexPattern->bindVariable($name,$value);
            }
            
            $patternEngine = new PatternEngine();
            $patternEngine->setBackend($this->_erfurt);
            $patternEngine->setDefaultGraph($this->_owApp->selectedModel);
            $patternEngine->processPattern($complexPattern);
        }
        
        // measurement for debug
        if (defined('_OWDEBUG')) {
            $end = microtime(true);
            $this->view->microtime = sprintf('%.3f s',$end - $start);
        }

        $this->view->variables = $unboundVariables;
        
        // button to commit pattern
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => $this->_owApp->translate->_('savepattern'))
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $url = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'exec'));

        //$this->view->placeholder('main.window.title')->set($windowTitle);

        $this->view->formActionUrl = (string) $url;
        $this->view->formMethod    = 'post';
        //$this->view->formName      = 'instancelist';
        //$this->view->formName      = 'patternmanager-form';
        $this->view->formEncoding  = 'multipart/form-data';

    }

    /**
     *
     */
    public function browseAction() {
        
        $store = $this->_erfurt->getStore();
        
        $configModel = $this->_privateConfig->configModel;
        $className   = $this->_privateConfig->className;
        $hasJson     = $this->_privateConfig->hasJson;
        
        $query = new Erfurt_Sparql_Query2();
        $query->addFrom($configModel);
        $query->addTriple(
            new Erfurt_Sparql_Query2_Var('pattern'),
            new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
            new Erfurt_Sparql_Query2_IriRef($className)
        );
        $query->addTriple(
            new Erfurt_Sparql_Query2_Var('pattern'),
            new Erfurt_Sparql_Query2_Var('P'),
            new Erfurt_Sparql_Query2_Var('O')
        );
        
        $result  = array();
        $count   = 0;
        $nrArray = array();
        
        foreach ($store->sparqlQuery($query,array(STORE_USE_AC => false)) as $row) {
            
            if ( !array_key_exists($row['pattern'],$nrArray) ) {
                $result[++$count]['uri'] = $row['pattern'];
                $nrArray[$row['pattern']] = $count;
            }
            
            if ( $row['P'] === EF_RDFS_LABEL ) {
                $result[$nrArray[$row['pattern']]]['label'] = $row['O'];
            }

            $execUrl = new OntoWiki_Url(array('controller' => 'patternmanager','action' => 'exec'));
            $execUrl->setParam('pattern', $row['pattern']);
            $result[$nrArray[$row['pattern']]]['exec_url'] = $execUrl;
                
            $viewUrl = new OntoWiki_Url(array('controller' => 'patternmanager','action' => 'view'));
            $viewUrl->setParam('pattern', $row['pattern']);
            $result[$nrArray[$row['pattern']]]['view_url'] = $viewUrl;

        }
        
        $this->view->data = $result;
        
        /*
        $complexPattern = new ComplexPattern();
        
        $json = current($result);
        $json = $json[$hasJson];
        
        $complexPattern->parseFromJson($json);
        
        var_dump($complexPattern);
        */
        
        /*
        $pattern1 = new BasicPattern(
            array(
                'classA' => 'RESOURCE' ,
                'classB' => 'RESOURCE' ,
                'classM' => 'RESOURCE' ,
                'apred'  => 'TEMP' ,
                'aobj'   => 'TEMP' ,
                'bpred'  => 'TEMP' ,
                'bobj'   => 'TEMP'
            ),
            array(
            	'{ classA a <http://www.w3.org/2002/07/owl#Class> . classA apred aobj . }',
                '{ classB a <http://www.w3.org/2002/07/owl#Class> . classB bpred bobj . }'
            ),
            array(
                'INSERT'    => array('classM apred aobj','classM bpred bobj'),
                'DELETE' => array('classA apred aobj','classB bpred bobj')
            )
        );
        
        $pattern2 = new BasicPattern(
            array(
                'classA' => 'RESOURCE' ,
                'classB' => 'RESOURCE' ,
                'classM' => 'RESOURCE' ,
                'instA'  => 'TEMP',
                'instB'  => 'TEMP'
            ),
            array(
            	'{ instA a classA . }',
            	'{ instB a classB . }'
            ),
            array(
                'INSERT'    => array('instA a classM','instB a classM'),
                'DELETE' => array('instA a classA','instB a classB')
            )
        );
        
        $pattern3 = new BasicPattern(
            array(
                'classA' => 'RESOURCE' ,
                'classB' => 'RESOURCE' ,
                'classM' => 'RESOURCE' ,
                'asubj'  => 'TEMP' ,
                'apred'  => 'TEMP' ,
                'bsubj'  => 'TEMP' ,
                'bpred'  => 'TEMP'
            ),
            array(
            	'{ asubj apred classA . }' ,
                '{ bsubj bpred classB . }'
            ),
            array(
                'INSERT'    => array('asubj apred classM', 'bsubj bpred classM'),
                'DELETE' => array('asubj apred classA','bsubj bpred classB')
            )
        );
        
        $patternAll = new ComplexPattern();
        
        $patternAll->appendElement($pattern1);
        $patternAll->appendElement($pattern2);
        $patternAll->appendElement($pattern3);
        
        $patternAll->bindVariable('classA', 'http://local.testcase/class/A/');
        $patternAll->bindVariable('classB', 'http://local.testcase/class/B/');
        $patternAll->bindVariable('classM', 'http://local.testcase/class/A-and-B-merged/');
        
        $patternEngine->processPattern($patternAll);
        */
        
    }
    
    /**
     *
     */
    public function viewAction() {
        
        // javascript functions
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/patternmanager-view.js');
        
        // button to commit pattern
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => $this->_owApp->translate->_('savepattern'))
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $url = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'save'));

        //$this->view->placeholder('main.window.title')->set($windowTitle);

        $this->view->formActionUrl = (string) $url;
        $this->view->formMethod    = 'post';
        //$this->view->formName      = 'instancelist';
        //$this->view->formName      = 'patternmanager-form';
        $this->view->formEncoding  = 'multipart/form-data';
        
        $patternUri = $this->_request->getParam('pattern', null);
        
        if ( $patternUri === null ) {
            $data = array();
        } else {
            $data = $this->loadPatternFromUri($patternUri);
        }
        
        if ( sizeof($data) > 0 && array_key_exists($this->_privateConfig->hasJson, $data) ) {
            $this->view->patternName   = current($data[EF_RDFS_LABEL]);
            $this->view->loadPattern   = current($data[$this->_privateConfig->hasJson]);
        } else {
            $this->view->patternName   = '';
            $this->view->loadPattern   = '{}';
        }
    }
    
    /**
     *  This function takes GET/POST Parameters and tries to create a valid 
     *  evolution pattern from it, which should be made persistent for later
     *  use.
     */
    public function saveAction() {
        
        // load params from request
        $params = $this->_request->getParams();
        
        // parametername prefixes
        // all parameters are in form like prefix-i-j where i,j are numeric indices
        // parameter numeric values should be consecutive integers
        $paramPrefixes = array(
            'varname' ,
            'vartype' ,
            'selectpattern' ,
            'insertpattern' ,
            'deletepattern'
        );
        
        $plainData = array();

        // convert parameters into matrix i , prefix , j (index reordering)
        foreach ($params as $name => $value) {
            if (sizeof(explode('-', $name)) > 1) {
                
                $parts = explode('-',$name);
                // only add to matrix if
                if ( in_array($parts[0],$paramPrefixes) && !empty($parts[1]) ) {
                  switch (sizeof($parts)) {
                      case 2:
                          $plainData[(int)$parts[1]][$parts[0]][] = $value;
                          break;
                      case 3:
                          $plainData[(int) $parts[1]][$parts[0]][(int) $parts[2]] = $value;
                          break;
                      default :
                          // do nothing
                          break;
                  }
                }
            }
        }

        $orderedData = array();
        
        // traversing data structure to prepare JSON conversion
        foreach ($plainData as $key => $pattern) {
            
            if ( array_key_exists('varname', $pattern) && array_key_exists('vartype', $pattern) ) {
                $variables = array_combine($pattern['varname'],$pattern['vartype']);
            } else {
                $variables = array();
            }
            
            if ( array_key_exists('selectpattern', $pattern) ) {
                $selects   = $pattern['selectpattern'];
            } else {
                $selects   = array();
            }
            
            $updates = array();
            
            if ( array_key_exists('insertpattern', $pattern) ) {
                $updates['INSERT'] = $pattern['insertpattern'];
            } else {
                $updates['INSERT'] = array();
            }
            
            if ( array_key_exists('deletepattern', $pattern) ) {
                $updates['DELETE'] = $pattern['deletepattern'];
            } else {
                $updates['DELETE'] = array();
            }
            
            // pack it like in formal definition
            $orderedData[] = array('V' => $variables, 'S' => $selects, 'U' => $updates);
        }

        // encode in json
        $encodedData = json_encode($orderedData);
        
        //$url = new OntoWiki_Url( array(), array() );
        //$url->setParam('json', $encodedData);
        
        $configModel = $this->_privateConfig->configModel;
        $className   = $this->_privateConfig->className;
        $hasJson     = $this->_privateConfig->hasJson;
        $instanceUri = $this->_privateConfig->className . '/' . md5($encodedData . time());
        
        $label = empty($params['label']) ?
            'unnamed Pattern ' . date(DateTime::ISO8601) :
            $params['label'];
        
        $stmts = array(
            $instanceUri => array (
                EF_RDF_TYPE => array (
                    array('value' => $className , 'type' => 'uri')
                ),
                $hasJson => array(
                    array('value' => $encodedData , 'type' => 'literal')
                ),
                EF_RDFS_LABEL => array(
                    array('value' => $label , 'type' => 'literal')
                )
            )
        );
        
        $store = $this->_erfurt->getStore();
        $store->addMultipleStatements( $configModel, $stmts, false);
    }
    
    /**
     * 
     * @param string $uri pattern-uri
     */
    private function loadPatternFromUri($uri) {
        
        $store = $this->_erfurt->getStore();
        
        $configModel = $this->_privateConfig->configModel;
        $className   = $this->_privateConfig->className;
        $hasJson     = $this->_privateConfig->hasJson;
        
        $query = new Erfurt_Sparql_Query2();
        $query->addFrom($configModel);
        
        $query->addTriple(
            new Erfurt_Sparql_Query2_IriRef($uri),
            new Erfurt_Sparql_Query2_Var('P'),
            new Erfurt_Sparql_Query2_Var('O')
        );
        
        $result  = array();
        $count   = 0;
        $nrArray = array();
        
        $data = $store->sparqlQuery($query, array(STORE_RESULTFORMAT => 'extended', STORE_USE_AC => false));

        foreach ($data['bindings'] as $row) {
            switch ($row['P']['value']) {
                case $hasJson:
                    $result[$row['P']['value']][] = $row['O']['value'];
                    break;
                case EF_RDFS_LABEL:
                    $result[$row['P']['value']][] = $row['O']['value'];
                    break;
                default:
                    break;
            }
        }
        
        return $result;
    }

}
