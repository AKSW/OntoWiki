<?php
/**
 * Controller for OntoWiki Pattern Manager Component
 *
 * @category   OntoWiki
 * @package    extensions_components_patternmanager
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'classes/PatternEngine.php';

class PatternmanagerController extends OntoWiki_Controller_Component {

    /**
     * Attribute to store reference to Pattern Engine
     * @var PatternEngine
     */
    private $_engine = null;

    /**
     *
     * Constant for maximum of recommended patterns (limit-like)
     * 
     * @var int
     */
    const MAX_RECOMMEND_PATTERN = 20;

    /**
     * Component global init function
     */
    public function init() {

        parent::init();

        OntoWiki_Navigation::disableNavigation();

        $this->view->placeholder('main.window.title')->set('OntoWiki Evolution Pattern Engine');

        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/jquery.autocomplete.js');

        $this->view->headLink()->appendStylesheet($this->_componentUrlBase .'css/patternmanager.css');
        //$this->view->headLink()->appendStylesheet($this->_componentUrlBase .'css/jquery.autocomplete.css');

        $this->_engine = new PatternEngine();
        $this->_engine->setConfig($this->_privateConfig);
        $this->_engine->setBackend($this->_erfurt);
        
    }

    /**
     * Shows an index page as entry portal for evolution pattern management
     */
    public function indexAction () {

        $url = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'browse') );
        $this->view->url['browse'] = $url;

        $this->_redirect($url);

    }
    
    /**
     * Exports pattern in a specific format
     */
    public function exportAction() {
        
        // for json export no templates and layout
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $pattern = $this->getRequest()->getParam('pattern');
        
        if (!empty($pattern) && is_string($pattern) && Erfurt_Uri::check($pattern)) {
            $pattern = $this->_engine->loadFromStore($pattern);
            
            $hash = substr(md5($pattern->getLabel().$pattern->getDescription()),0,8);
            $filename = 'pattern_export_' . date('Y-m-d_H\hi') . '_' . $hash . '.json';   
            $contentType = 'application/json';
            $response = $this->getResponse();
            $response->setHeader('Content-Type', $contentType, true);
            $response->setHeader('Content-Disposition', ('filename="'.$filename.'"'));
            
            echo $pattern->toArray(true);
            
        } else {
            $msg = array('error'=>'invalid input in pattern variable');
            echo json_encode($msg);
        }
        
    }

    /**
     *  Launcher action to generate pattern selections for given input parameters and
     *  guide to pattern execution
     */
    public function launcherAction() {
        
        if ($this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_EXEC_STR)) {
	        
	        // javascript functions
	        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/patternmanager-launcher.js');
	    	
			$title = 'Evolution Patternmanager > Launcher ';
	        $this->view->placeholder('main.window.title')->set($title);
	
	        $step = (int) $this->_request->getParam('step',0);
	        $this->view->step = $step;
	        $patternInput = json_decode($this->getRequest()->getParam('pattern_input'),true);
	        $patternSelected = $this->getRequest()->getParam('pattern',null);
	
	        if ($step === 0 &&  $patternInput !== null) {
	
	        	$patternUriArray = array();
	
				$fallbackTable = array(
		        	PatternVariable::R_CLASS 	=> PatternVariable::RESOURCE,
	        		PatternVariable::R_PROPERTY	=> PatternVariable::RESOURCE,
		            PatternVariable::DATATYPE 	=> PatternVariable::RESOURCE,
		        	PatternVariable::LANG		=> PatternVariable::LITERAL,
		            PatternVariable::REGEXP     => PatternVariable::LITERAL
		        );
	
		        $primarySig = array();
		        $fallbackSig = array();
	
		        foreach ($patternInput as $key => $p) {
		            if (is_string($p)) {
		            	$type = $this->_checkVarType($p);
		                $primarySig[$key] = $type;
		                if (array_key_exists($type,$fallbackTable)) {
		                    $fallbackSig[$key] = $fallbackTable[$type];
		                }
		            } else if (is_array($p)) {
		                if (array_key_exists('value', $p) && array_key_exists('hint', $p)) {
		                	$type = $this->_checkVarType($p['value'],$p['hint']);
		                    $primarySig[$key] = $type;
		                    $fallbackSig[$key] = $fallbackTable[$type];
		                }
		            } else {
		                throw new RuntimeException('disallowed parameter in patternmanger launcher input');
		            }
		        }
	
		        // look for patterns for primary signature
		        $filter = '';
		        $queryextra = '';
	
		        for ($i = 0; $i < sizeof($primarySig); $i++) {
		            $queryextra .= PHP_EOL . '?bp evopat:hasPatternVariable ?var' . $i . ' . '
		                        .  PHP_EOL .'?var' . $i . ' a ?vartype' . $i . ' . ';
	
		            $filter .= 'sameTerm(?vartype' . $i . ', evopat:PatternVariable_' . $primarySig[$i] . ') && ';
		            for ($j = $i+1; $j < sizeof($primarySig); $j++) {
		                $filter .= '?var' . $i . ' != ?var' . $j . ' && ';
		            }
		        }
	
		        $filter .= ' 1';
		        
		        $query = 'PREFIX evopat: <' . $this->_privateConfig->storeModel . '> 
		        	SELECT DISTINCT ?cp ?cm  WHERE {
		        		?cp evopat:hasSubPattern ?sp .
						?cp <' . EF_RDFS_COMMENT . '>  ?cm .
						?sp evopat:hasBasicPattern ?bp .
						' . $queryextra . '
						FILTER (
							' . $filter . '
						)
					} LIMIT 20';
	
		        $matches = $this->_erfurt->getStore()->sparqlQuery($query);
	
		        foreach ($matches as $row) {
		            if ( !in_array($row['cp'], $patternUriArray) ) {
		            	$patternUriArray[] = $row['cp'];
		            }
		        }
	
		        $countPrimary = sizeof($patternUriArray);
	
				// look for patterns for fallback signature
		        $filter = '';
		        $queryextra = '';
	
		        for ($i = 0; $i < sizeof($fallbackSig); $i++) {
		            $queryextra .= PHP_EOL . '?bp evopat:hasPatternVariable ?var' . $i . ' . '
		                        .  PHP_EOL .'?var' . $i . ' a ?vartype' . $i . ' . ';
	
		            $filter .= 'sameTerm(?vartype' . $i . ', evopat:PatternVariable_' . $fallbackSig[$i] . ') && ';
		            for ($j = $i+1; $j < sizeof($fallbackSig); $j++) {
		                $filter .= '?var' . $i . ' != ?var' . $j . ' && ';
		            }
		        }
	
		        $filter .= ' 1';
	
		        $query = 'PREFIX evopat: <' . $this->_privateConfig->storeModel . '> 
		        	SELECT DISTINCT ?cp ?cm  WHERE {
		        		?cp evopat:hasSubPattern ?sp .
						?cp <' . EF_RDFS_COMMENT . '>  ?cm .
						?sp evopat:hasBasicPattern ?bp .
						' . $queryextra . '
						FILTER (
							' . $filter . '
						)
					} LIMIT 20';
	
		        if (sizeof($fallbackSig) > 0) {
		            $matches = $this->_erfurt->getStore()->sparqlQuery($query);
		        } else {
		            $matches = array();
		        }

		        foreach ($matches as $row) {
					if ( !in_array($row['cp'],$patternUriArray) ) {
		            	$patternUriArray[] = $row['cp'];
		            }
		        }
	
		        $patternList = array();
	
		        for ($i = 0; $i < sizeof($patternUriArray); $i++) {
		        	$pattern = $this->_engine->loadFromStore($patternUriArray[$i]);
		        	$patternList[$i]['desc']  = $pattern->getDescription();
		        	$patternList[$i]['label'] = $pattern->getLabel();
		        	$patternList[$i]['vars'] = array();
	
		        	$url = new OntoWiki_Url(array('controller' => 'patternmanager','action' => 'launcher'),array());
		        	$url->setParam('step', '1');
		        	$url->setParam('pattern', $patternUriArray[$i]);
		        	$pSig = array();
	
		        	foreach ($primarySig as $id => $type) {
		        		foreach ($pattern->getVariables() as $var) {
		        			if ($var['type'] === $type ||
		        			   ( sizeof($fallbackSig) > 0 && isset($fallbackTable[$type]) && $var['type'] === $fallbackTable[$type] )
		        			) {
		        				if ($var['type'] === $type) {
		        					$pSig[$patternInput[$id]] = $type;
		        				} else {
		        					$pSig[$patternInput[$id]] = $fallbackTable[$type];
		        				}
			        			if (!array_key_exists($var['name'],$patternList[$i]['vars']) ) {
		        					$var['val'][$id] = $patternInput[$id];
		        					$patternList[$i]['vars'][$var['name']] = $var;
			        			} else {
			        				$patternList[$i]['vars'][$var['name']]['val'][$id] = $patternInput[$id];
			        			}
		        			}
		        		}
		        	}
		        	$url->setParam('pattern_input', json_encode($pSig));
		        	$patternList[$i]['launcher_select'] = (string) $url;
		        }
	
		        $this->view->patternList = $patternList;
	
	
	        } elseif ($step === 1 && $patternSelected !== null && $patternInput !== null)  {
	
	        	$pattern = $this->_engine->loadFromStore($patternSelected);
	
	        	$types = array();
	        	foreach ($patternInput as $key => $input) {
	        		$types[$input][] = $key;
	        	}
	
	            foreach ($types as $t => $v) {
	        	    $types[$t] = $this->_permute($v,sizeof($v));
	            }
	
	            $binding = array();
	            
	            
	            // calculate bindings for single input
	            if (sizeof($types) == 1) {
	                $i = 0;
	                foreach( $pattern->getVariables() as $var) {
	                    $i++;
	                    if (array_key_exists($var['type'],$types)) {
	                         $binding[$i][$var['name']] = $var;
	                         $binding[$i][$var['name']]['val'] = current(current(current($types)));
	                    }
	                }
	                
	            } else {
	                // calculate bindings for more than one possible order of variables (permutations)
    	            foreach ($pattern->getVariables() as $var) {
    	                if (array_key_exists($var['type'],$types)) {
    	                    foreach ($types[$var['type']] as $i => $permutation) {
    	                        if (!empty($types[$var['type']][$i])) {
    	                            $binding[$i][$var['name']] = $var;
    	                            $binding[$i][$var['name']]['val'] = array_shift($types[$var['type']][$i]);
    	                        }
    	                    }
    	                }
    	            }
    	            
	            }
	            
	            $execLinks = array();

                foreach ($binding as $i => $bind) {
	                $binding[$i] = array_merge($pattern->getVariables(),$bind);
	                $url = new Ontowiki_Url(array('controller'=>'patternmanager', 'action' => 'exec'),array('pattern'));
	                $preboundVariables = array();
	                foreach ($bind as $var) {
	                    if (array_key_exists('val',$var)) {
	                        $preboundVariables[$var['name']] = $var['val'];
	                    }
	                }
	                $url->setParam('prebound_variables',json_encode($preboundVariables));
	                $execLinks[$i] = (string) $url;
	            }
	
	            $this->view->execLinks = $execLinks;
	            $this->view->binding = $binding;
	            $this->view->pattern = $pattern;
	            
	            // redirect directly to execution view if only one single binding is available
	            if (sizeof($binding) == 1) {
	                $this->_redirect(current($execLinks));
	            }
	        }
        } else {
            $msg_located = $this->view->_('action %s not allowed');
            $msgObject = new OntoWiki_Message(
            	sprintf($msg_located,PatternEngineAc::RIGHT_EXEC_STR),
                OntoWiki_Message::ERROR
            );
            $this->_owApp->appendMessage($msgObject);
        }
    }

    /**
     * Permutation helping function
     */
    private function _permute($arr, $n) {
	    if ( $n < 2 || !is_array( $arr ) || empty( $arr ) ) {
	        return array($arr);
	    }
	    $result = array();
	    foreach ( $arr as $k => $value ) {
	        $copy = $arr;
	        unset( $copy[$k] );
	        $sub = $this->_permute( $copy, $n-1 );
	        foreach ( $sub as $subvalue ) {
	            $result[] = array_merge(array($value) ,$subvalue);
	        }
	    }
	    return $result;
    }

    /**
     *
     * Checking variable type for an explicit value in $val
     * @param string $val
     * @param string $hint
     */
    private function _checkVarType($val, $hint = null) {
        
        if ($hint !== null) {
            return $hint;
        }
        
        $store = $this->_erfurt->getStore();
        $models = $store->getAvailableModels();

        if ( array_key_exists($val,$models) ) {
            return 'GRAPH';
        } else if (strpos($val,'http://www.w3.org/2001/XMLSchema#') === 0) {
            return PatternVariable::DATATYPE;
        } else if (preg_match('/^[a-z]{2}$/',$val)) {
            return PatternVariable::LANG;
        } else {
            if ( Erfurt_Uri::check($val) ) {
                $query = 'SELECT * WHERE {<' . $val . '> a ?type . } LIMIT 1';
                $res = $store->sparqlQuery($query);
                if (!empty($res)) {
                    switch ($res[0]['type']) {
                        case EF_RDFS_CLASS:
                        case EF_OWL_CLASS:
                            return PatternVariable::R_CLASS;
                        case EF_OWL_ANNOTATION_PROPERTY:
                        case EF_OWL_DATATYPE_PROPERTY:
                        case EF_OWL_OBJECT_PROPERTY:
                        case EF_OWL_FUNCTIONAL_PROPERTY:
                        case EF_OWL_INVERSEFUNCTIONAL_PROPERTY:
                            return PatternVariable::R_PROPERTY;
                    }
                }
                return PatternVariable::RESOURCE;
            } else {
                return PatternVariable::LITERAL;
            }
        }


    }

    /**
     * Opens a pattern for execution (executes pattern if mode is set to 1)
     * HTTP Parameters:
     * - mode 				: pattern execution mode (0,1)
     * - pattern 			: pattern URI 
     * - prebound_variables	: json object of prebound variables (not in execution mode)
     * - var				: variables to be bound (in execution mode)
     */
    public function execAction() {
        
        $this->view->variables = array();
        
        if ($this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_EXEC_STR)) {
            
	        // javascript functions
	        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/patternmanager-exec.js');
	
	        if (defined('_OWDEBUG')) {
	            $start = microtime(true);
	        }
	
	        $this->_engine->setDefaultGraph($this->_owApp->selectedModel);
	
	        $complexPattern = $this->_engine->loadFromStore($this->getRequest()->getParam('pattern'));
	        
	        $this->view->complexPattern = $complexPattern;
	
	        $unboundVariables = $complexPattern->getVariables(false);
	        
	        try {
	            $preboundVariables = Zend_Json::decode(
	                $this->getRequest()->getParam('prebound_variables','[]'),
	                Zend_Json::TYPE_ARRAY
                );
	        } catch (Zend_Json_Exception $e) {
	            $preboundVariables = array();
	        }

	        $var = $this->getRequest()->getParam('var',array());
	        $mode = $this->getRequest()->getParam('mode');
	
	        if ($mode == 1) {
	            foreach ($var as $name => $value) {
	                unset($unboundVariables[$name]);
	                $complexPattern->bindVariable($name,$value);
	            }
	
	            $this->_engine->processPattern($complexPattern);
	            
	        }

	        foreach ($unboundVariables as $i => $var) {
	            if (array_key_exists($var['name'],$preboundVariables)) {
	                $unboundVariables[$i]['prebound'] = $preboundVariables[$var['name']];
	            } else {
	                $unboundVariables[$i]['prebound'] = false;
	            }
	        }
	
	        // measurement for debug
	        if (defined('_OWDEBUG') && $mode == 1) {
	            $end = microtime(true);
	            $this->view->microtime = sprintf('%.3f s',$end - $start);
	        }
	
	        $this->view->variables = $unboundVariables;
	
	        // button to commit pattern
	        $toolbar = $this->_owApp->toolbar;
	        $toolbar->appendButton(
	            OntoWiki_Toolbar::SUBMIT,
	            array('name' => $this->_owApp->translate->_('execute pattern'))
	        );
	        $this->view->placeholder('main.window.toolbar')->set($toolbar);
	
	        $url = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'exec'),array());
	        $url->setParam('mode','1');
	        $url->setParam('pattern',$this->getRequest()->getParam('pattern'));
	
	
	        $this->view->formActionUrl = (string) $url;
	        $this->view->formMethod    = 'post';
	        //$this->view->formName      = 'instancelist';
	        //$this->view->formName      = 'patternmanager-form';
	        $this->view->formEncoding  = 'multipart/form-data';
        } else {
            // message for action not allowed 
            $msgLocated = $this->view->_('action %s not allowed');
            $msgObject = new OntoWiki_Message(
            	sprintf($msgLocated,PatternEngineAc::RIGHT_EXEC_STR),
                OntoWiki_Message::ERROR
            );
            $this->_owApp->appendMessage($msgObject);
        }

        $title = 'Evolution Patternmanager > Execution ';
        //$title = '<a>' . $this->_owApp->translate->_('Evolution Patternmanager') . '</a>' . ' &gt; '
        //       . '<a>' . $this->_owApp->translate->_('Execution') . '</a>';

        $this->view->placeholder('main.window.title')->set($title);

    }

    /**
     * List of complex patterns
     * HTTP Parameters:
     * - none
     */
    public function browseAction() {
        
        // for explore tags module
        $this->addModuleContext('main.window.instances');

        $store = $this->_owApp->erfurt->getStore();
        $graph = $store->getModel($this->_privateConfig->storeModel);
        
        // set ac on view
        $this->view->ac = $this->_engine->getAc();
        
        $toolbar = $this->_owApp->toolbar;
        
        // button to create pattern (if action allowed)
        if ( $this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_EDIT_STR) ) {
	        $toolbar->appendButton(
	            null,
	            array(
	            	'name' => $this->_owApp->translate->_('new pattern'),
	                'url'  => (string) (new OntoWiki_Url( array('controller' => 'patternmanager', 'action' => 'view' ))),
	                'image' => 'add',
	            )
	        );
        }

        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        //Loading data for list of saved queries
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = "patternmanager-list";
        if($listHelper->listExists($listName)){
            $list = $listHelper->getList($listName);
            $listHelper->addList($listName, $list, $this->view, "list_pattern_main");
        } else {
            $list = new OntoWiki_Model_Instances($store, $graph, array());
            $list->addTypeFilter($this->_privateConfig->rdf->ComplexPattern);
            $listHelper->addListPermanently($listName, $list, $this->view, "list_pattern_main");
        }
        
       

        $title = 'Evolution Patternmanager > Browser ';
        //$title = '<a>' . $this->_owApp->translate->_('Evolution Patternmanager') . '</a>' . ' &gt; '
        //       . '<a>' . $this->_owApp->translate->_('Browse') . '</a>';

        // javascript functions
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/patternmanager-browse.js');
        
        $this->view->schema = array('level' => 'http://ns.ontowiki.net/Evolution/level');
        $this->view->placeholder('main.window.title')->set($title);

    }

    /**
     * Pattern view and edit page
     * 
     * HTTP Parameters:
     * - pattern			String; Pattern URI to open pattern from
     * - json_pattern		String; Pattern JSON to deserialize pattern from
     * 
     * SESSION Access:
     * - patternmanager		Array; holding data about last pattern
     * 						with serialization and errors
     * 						(generated by saveAction)
     */
    public function viewAction() {
        
        if ($this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_VIEW_STR)) {

	        // javascript functions
	        $this->view->headScript()->appendFile($this->_componentUrlBase . 'scripts/patternmanager-view.js');
	        
            $toolbar = $this->_owApp->toolbar;
            
            $toolbar->appendButton(
		            null,
		            array('name' => $this->_owApp->translate->_('back to list'),
                          'url'  => (string) (new OntoWiki_Url(
                                            array('controller' => 'patternmanager', 'action' => 'browse'),
                                            array()
                                        )
                                     ),
                          'id'   => 'patternmanager_view_back_button',
                          'image' => 'go',
                    )
		        );

	        // button to commit pattern (if action allowed)
	        if ($this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_EDIT_STR)) {
	            		
		        $toolbar->appendButton(
		            null,
		            array(
		            	'name'  => $this->_owApp->translate->_('edit pattern'),
		                'image' => 'edit',
		                'id'	=> 'patternmanager_view_edit_button',
		            )
		        );
	            
		        $toolbar->appendButton(
		            null,
		            array(
		            	'name' => $this->_owApp->translate->_('save pattern'),
						'image' => 'go2',
		                'id'	=> 'patternmanager_view_save_button',
		            )
		        );

		        $toolbar->appendButton(
		            null,
		            array('name' => $this->_owApp->translate->_('export as').' JSON',
                          'url'  => (string) (new OntoWiki_Url(
                                            array('controller' => 'patternmanager', 'action' => 'export'),
                                            array('pattern')
                                        )
                                     ),
                          'id'   => 'patternmanager_view_exportJSON_button',
                          'image' => 'save',
                    )
		        );
		        
		        $toolbar->appendButton(
		            null,
		            array (
		                'name' => $this->_owApp->translate->_('discard changes'),
		                'id' => 'patternmanager_view_cancel_button',    
		            	'image' => 'cancel',
		            )
	            );
		        
		        $url = new OntoWiki_Url(
		            array('controller' => 'patternmanager', 'action' => 'save'),
		            array()
		        );
		        
		        $this->view->formActionUrl = (string) $url;
		        $this->view->formMethod    = 'post';
	            //$this->view->formName      = 'patternmanager_view_form';
	            $this->view->formEncoding  = 'multipart/form-data';
            }
            
			$this->view->placeholder('main.window.toolbar')->set($toolbar);
	        //$this->view->placeholder('main.window.title')->set($windowTitle);
	
	        $this->view->jsonPattern = '{}';
	
	        $pUri  = $this->_request->getParam('pattern', null);
            
            // load data from session and delete it afterwards
            if ( isset($this->_session->patternmanager) ) {
                if ( isset($this->_session->patternmanager['json']) ) {
                    $pJson = str_replace('\'', '\\u0027', $this->_session->patternmanager['json']);
                }
                
                if ( isset($this->_session->patternmanager['error']) ) {
                    $error = $this->_session->patternmanager['error'];
                    $this->view->error = $error;
                }
                
                unset($this->_session->patternmanager);
            } else {
                $pJson = str_replace('\'', '\\u0027', $this->getRequest()->getParam('json_pattern'));
            }
            
	        if ( !empty($pUri) ) {
	            $loaded = $this->_engine->loadFromStore($pUri);
	            $toolbar->appendButton(
		            null,
		            array('name' => $this->_owApp->translate->_('execute pattern'),
                          'url'  => (string) (new OntoWiki_Url(
                                            array('controller' => 'patternmanager', 'action' => 'exec'),
                                            array('pattern')
                                        )
                                     ),
                          'id'   => 'patternmanager_view_toExec_button',
                          'image' => 'go',
                    )
		        );
		        
		        	            
	            // assign annotations
	            $annotations = array();
	            foreach ($this->_privateConfig->rdf->annotate as $key => $el) {
	                $annotations[$key] = $loaded->getAnnotation($key);
	            }
	            $this->view->annotations = $annotations;
		        
		        $this->view->patternUri = $pUri;
	            $this->view->jsonPattern = $loaded->toArray(true);
	            
	        } else {
	            $this->view->patternUri = '';
	        }
	
	        if ( !empty($pJson) ) {
	            $this->view->jsonPattern = $pJson;
	        }
	        
	        $this->view->exportUrl = (string) (new OntoWiki_Url(
		        array('controller' => 'patternmanager', 'action' => 'export'),
		        array('pattern')
		    ));
		    
        } else {
            $msg_located = $this->view->_('action %s not allowed');
            $msgObject = new OntoWiki_Message(
            	sprintf($msg_located,PatternEngineAc::RIGHT_VIEW_STR),
                OntoWiki_Message::ERROR
            );
            $this->_owApp->appendMessage($msgObject);
        }

        $title = 'Evolution Patternmanager > Edit ';
        //$title = '<a>' . $this->_owApp->translate->_('Evolution Patternmanager') . '</a>' . ' &gt; '
        //       . '<a>' . $this->_owApp->translate->_('Edit') . '</a>';

        $this->view->placeholder('main.window.title')->set($title);

    }

    /**
     *  This function takes GET/POST Parameters and tries to create a valid
     *  evolution pattern from it, which should be made persistent for later
     *  use.
     */
    public function saveAction() {
        
        if ($this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_EDIT_STR)) {

            // load params from request
            $params = $this->_request->getParams();

	        // parametername prefixes
	        // all parameters are in form like prefix-i-j where i,j are numeric indices
	        // parameter numeric values should be consecutive integers
	        $paramPrefixes = array(
	            'patterndesc' ,
	            'patternlabel' ,
	            'varname' ,
	            'vartype' ,
	            'vardesc' ,
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
	                          $plainData[(int)$parts[1]][$parts[0]][] = trim($value);
	                          break;
	                      case 3:
	                          $plainData[(int) $parts[1]][$parts[0]][(int) $parts[2]] = trim($value);
	                          break;
	                      default :
	                          // do nothing
	                          break;
	                  }
	                }
	            }
	        }
	
	        $orderedData = array();
	
	        //init complex pattern instance
	        $complexPattern = new ComplexPattern();
	        $complexPattern->setEngine($this->_engine);
	
	        $error = array();
	
	        // traversing data structure to prepare JSON conversion
	        foreach ($plainData as $pNr => $pattern) {
	
	            $basicPattern = new BasicPattern();
	
	            $variables = array();
	
	            if ( array_key_exists('varname', $pattern) ) {
		            foreach ($pattern['varname'] as $i => $name) {
		                $variables[$i] = array(
		                	'name' => $name ,
		                	'type' => $pattern['vartype'][$i],
		                	'desc' => $pattern['vardesc'][$i]
		                );
		                // variable names should only be alphanumeric and extra '-' and '_'
		                if (preg_match('/^([A-Z]|[a-z]|[0-9]|[_-])+$/',$name) === 0 ) {
		                    $error[] = 'varname-' . $pNr . '-' . $i;
		                }
		            }
	            }
	
	            if ( array_key_exists('selectpattern', $pattern) ) {
	                foreach ($pattern['selectpattern'] as $s => $select) {
	                    $selects[$pNr] = $select;
	                    if (preg_match (
	                    		'/^(((\s*FROM\s+\S+)+\s+WHERE\s+)|(\s*WHERE\s+)|\s*)\{.+\}$/',
	                            $select
	                        ) === 0 )  {
	                        $error[] = 'selectpattern-' . $pNr . '-' . $s;
	                    }
	                }
	            } else {
	                $selects   = array();
	            }
	
	            $updates = array();
	
	            if ( array_key_exists('insertpattern', $pattern) ) {
	                foreach ($pattern['insertpattern'] as $n => $pat) {
	                    $updates['INSERT'][$n] = $pattern['insertpattern'][$n];
	                    if (preg_match ('/^\s*\S+\s+\S+\s+\S+((\s*)|(\s+\S+\S*))$/',$pat) === 0) {
	                        $error[] = 'insertpattern-' . $pNr . '-' . $n;
	                    }
	                }
	
	            } else {
	                $updates['INSERT'] = array();
	            }
	
	            if ( array_key_exists('deletepattern', $pattern) ) {
	                foreach ($pattern['deletepattern'] as $n => $pat) {
	                    $updates['DELETE'][$n] = $pattern['deletepattern'][$n];
	                    if (preg_match ('/^\s*\S+\s+\S+\s+\S+((\s*)|(\s+\S+\S*))$/',$pat) === 0) {
	                        $error[] = 'deletepattern-' . $pNr . '-' . $n;
	                    }
	                }
	            } else {
	                $updates['DELETE'] = array();
	            }
	
	            foreach ($selects as $select) {
	                $basicPattern->setSelectQuery($select);
	            }
	
	            foreach ($updates['DELETE'] as $update) {
	                $basicPattern->addUpdateQuery($update,'delete');
	            }
	
	            foreach ($updates['INSERT'] as $update) {
	                $basicPattern->addUpdateQuery($update,'insert');
	            }
	
	            $description = empty($pattern['patterndesc'][0]) ?
	    			'unspecified Basic Pattern ' . date(DateTime::ISO8601) :
	                $pattern['patterndesc'][0];
	
	            $label = '';
	
	            if ( empty($pattern['patternlabel'][0]) ) {
	                $error[] = 'patternlabel-' . $pNr;
	            } else {
	                $label = $pattern['patternlabel'][0];
	            }
	
	            $basicPattern->setLabel($label);
	            $basicPattern->setDescription($description);
	
	            foreach ($variables as $var) {
	                $basicPattern->addVariable($var['name'], $var['type'], $var['desc']);
	            }
	
	            $complexPattern->appendElement($basicPattern);
	
	        }
	
	        $description = empty($params['desc']) ?
	    		'unspecified Pattern ' . date(DateTime::ISO8601) :
	            $params['desc'];
	
	        $label = empty($params['label']) ?
	    		'unspecified Pattern ' . date(DateTime::ISO8601) :
	            $params['label'];
	
	        $complexPattern->setLabel($label);
	        $complexPattern->setDescription($description);
	
	        if (empty($error)) {
	            
	            if ( isset($params['patternUri']) && strlen($params['patternUri']) > 0) {
	                $this->_engine->deleteFromStore($params['patternUri']);
	            }
	            
	            $res = $this->_engine->saveToStore($complexPattern);
	            
	            if ( isset($res['uri']) ) {
	                $this->view->patternUri = $res['uri'];
	            }
	            
	        } else {
	
	            $url = new OntoWiki_Url(
	                array(
	                    'controller' => 'patternmanager',
	                	'action' => 'view'
	                ),
	                array()
	            );
	            $url->setParam('action','view');
	            $this->_session->patternmanager = array();
	            $this->_session->patternmanager['json'] = $complexPattern->toArray(true);
	            $this->_session->patternmanager['error'] = Zend_Json::encode($error);
	            $this->_redirect($url);
	        }
	        
        } else {
            $msg_located = $this->view->_('action %s not allowed');
            $msgObject = new OntoWiki_Message(
            	sprintf($msg_located,PatternEngineAc::RIGHT_EDIT_STR),
                OntoWiki_Message::ERROR
            );
            $this->_owApp->appendMessage($msgObject);
        }

        $title = 'Evolution Patternmanager > Save ';
        //$title = '<a>' . $this->_owApp->translate->_('Evolution Patternmanager') . '</a>' . ' &gt; '
        //       . '<a>' . $this->_owApp->translate->_('Browse') . '</a>';

        $this->view->placeholder('main.window.title')->set($title);
    }

    /**
     *
     */
    public function autocompleteAction() {
        
        // no evolution engine ac required

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $model = $this->_owApp->selectedModel;

        $query = $this->_request->getParam('q','');
        $vartype = $this->_request->getParam('vartype');
        $limit = (int) $this->_request->getParam('limit',10);


        $allowedInputType = array(
            PatternVariable::RESOURCE => '/([a-z]|[0-9]|[A-Z])+/',
            PatternVariable::LITERAL => '/\S+/',
            'BasicPattern' => '/.*/'
        );

        $sparqlQuery = 'SELECT DISTINCT ?entity ' . PHP_EOL . 'FROM <' . (string) $model . '> WHERE ' . PHP_EOL;

        $error = false;
        $ret = array();

        if ( array_key_exists($vartype,$allowedInputType) ) {

            if ( !preg_match ($allowedInputType[$vartype], $query ) ) {
                $error = true;
            } else  {
                $error = false;
            }

        }

        if (!$error) {

	        switch ($vartype) {
	            case 'BasicPattern':
	                $sparqlQuery =  'SELECT DISTINCT ?entity ?label FROM <' . $this->_privateConfig->storeModel . '> {
			        	?entity a ?t . ?entity <' . EF_RDFS_LABEL . '> ?label .
			        	FILTER (
			        		REGEX(?label,"' . addcslashes($query,'"') . '", "i") &&
			        		REGEX( STR(?t), "' .  addcslashes($vartype,'"') . '", "i")
			        	)
			    		} LIMIT ' . $limit;
	        	        try {
				            $res = $this->_erfurt->getStore()->sparqlQuery($sparqlQuery);
				        } catch (Exception $e) {
				            $error = true;
				            $res = array();
				        }
				        foreach ($res as $row) {
				            $ret[] = json_encode( array($row['entity'],$row['label']));
				        }
	                break;
	            case PatternVariable::LITERAL:
			        $sparqlQuery =  'SELECT DISTINCT ?entity WHERE {
			        	?s ?p ?entity .
			        	FILTER (
			        		isLiteral(?entity) &&
			        		REGEX(?entity, "' . addcslashes($query,'"') . '","i")
			        	)
			    		} LIMIT ' . $limit;
        	        try {
			            $res = $this->_erfurt->getStore()->sparqlQuery($sparqlQuery);
			        } catch (Exception $e) {
			            $error = true;
			            $res = array();
			        }
			        foreach ($res as $value) {
			            $ret[] = $value['entity'];
			        }
			        break;
	            case PatternVariable::R_CLASS:
                    $sparqlQuery =  'SELECT DISTINCT ?entity FROM <' .  (string) $model . '> WHERE {
			        	?entity ?p ?o .
			        	?entity <' . EF_RDFS_LABEL . '> ?label .
			        	?entity <' . EF_RDF_TYPE . '> ?type .
			        	FILTER (
			        		(
			        			REGEX( ?label, "' . addcslashes($query,'"') . '","i") ||
			        			REGEX( STR(?entity), "' . addcslashes($query,'"') . '","i")
			        		) && (
			        			SAMETERM(?type,  <' . EF_OWL_CLASS .  '>) ||
			        			SAMETERM(?type,  <' . EF_RDFS_CLASS . '>)
			        		)
			        	)
			    		} LIMIT ' . $limit;

        	        try {
			            $res = $this->_erfurt->getStore()->sparqlQuery($sparqlQuery);
			        } catch (Exception $e) {
			            $error = true;
			            $res = array();
			        }
			        foreach ($res as $value) {
			            $ret[] = $value['entity'];
			        }
	                break;
	            case PatternVariable::R_PROPERTY:
                    $sparqlQuery =  'SELECT DISTINCT ?entity FROM <' .  (string) $model . '> WHERE {
			        	?x ?entity ?o .
			        	OPTIONAL { ?entity <' . EF_RDFS_LABEL . '> ?label . }
			        	FILTER (
			        		REGEX( ?label, "' . addcslashes($query,'"') . '","i") ||
			        		REGEX( STR(?entity), "' . addcslashes($query,'"') . '","i")
			        	)
			    		} LIMIT ' . $limit;
        	        try {
			            $res = $this->_erfurt->getStore()->sparqlQuery($sparqlQuery);
			        } catch (Exception $e) {
			            $error = true;
			            $res = array();
			        }
			        foreach ($res as $value) {
			            $ret[] = $value['entity'];
			        }
	                break;
	            case PatternVariable::RESOURCE :
                    $sparqlQuery =  'SELECT DISTINCT ?entity FROM <' .  (string) $model . '> WHERE {
			        	?entity ?p ?o .
			        	?entity <' . EF_RDFS_LABEL . '> ?label
			        	FILTER (
			        		REGEX( ?label, "' . addcslashes($query,'"') . '","i") ||
			        		REGEX( STR(?entity), "' . addcslashes($query,'"') . '","i")
			        	)
			    		} LIMIT ' . $limit;
        	        try {
			            $res = $this->_erfurt->getStore()->sparqlQuery($sparqlQuery);
			        } catch (Exception $e) {
			            $error = true;
			            $res = array();
			        }
			        foreach ($res as $value) {
			            $ret[] = $value['entity'];
			        }
	                break;
	            case 'GRAPH':
	                $models = array_keys($this->_erfurt->getStore()->getAvailableModels());
	                for ($i = 0; $i < sizeof($models) && sizeof($ret) < $limit; $i++) {

	                    if (preg_match('/' . preg_quote($query,'/') . '/i',$models[$i]) !== 0 ) {
	                        $ret[] = $models[$i];
	                    }
	                }
	                break;
	            default:
	                break;
	        }

        }

        echo implode(PHP_EOL, $ret);
    }

    /**
     * load a basic pattern as json
     */
    public function loadpatternAction() {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        if ($this->_engine->getAc()->isActionAllowed(PatternEngineAc::RIGHT_VIEW_STR)) {
	
	        $uri = $this->_request->getParam('uri','http://null/uri');
	
	        $schema = array (
		    	'PatternVariable'        => 'http://ns.ontowiki.net/Evolution/PatternVariable' ,
		        'SelectQuery'            => 'http://ns.ontowiki.net/Evolution/SelectQuery' ,
		        'UpdateQuery_Insert'     => 'http://ns.ontowiki.net/Evolution/UpdateQuery_Insert' ,
		        'UpdateQuery_Delete'     => 'http://ns.ontowiki.net/Evolution/UpdateQuery_Delete' ,
		        'SubPattern'		     => 'http://ns.ontowiki.net/Evolution/SubPattern' ,
		        'ComplexPattern'         => 'http://ns.ontowiki.net/Evolution/ComplexPattern' ,
		        'BasicPattern'           => 'http://ns.ontowiki.net/Evolution/BasicPattern' ,
		        'hasPatternVariable'     => 'http://ns.ontowiki.net/Evolution/hasPatternVariable' ,
		        'hasUpdateQuery'         => 'http://ns.ontowiki.net/Evolution/hasUpdateQuery' ,
		        'hasBasicPattern'        => 'http://ns.ontowiki.net/Evolution/hasBasicPattern' ,
		        'hasSubPattern'          => 'http://ns.ontowiki.net/Evolution/hasSubPattern' ,
		        'hasSelectQuery'	     => 'http://ns.ontowiki.net/Evolution/hasSelectQuery' ,
		        'updatePatternObject'    => 'http://ns.ontowiki.net/Evolution/updatePatternObject' ,
		        'updatePatternPredicate' => 'http://ns.ontowiki.net/Evolution/updatePatternPredicate' ,
		        'updatePatternSubject'   => 'http://ns.ontowiki.net/Evolution/updatePatternSubject' ,
	        );
	
			$sparqlQuery =  'SELECT DISTINCT ?s ?p ?o FROM <' . $this->_privateConfig->storeModel . '> {
				{
				<' . $uri . '> a <' . $schema['BasicPattern'] . '> .
				?s ?p ?o . FILTER (sameTerm(?s,<' . $uri . '>)) .
	    		} UNION {
	    		<' . $uri . '> a <' . $schema['BasicPattern'] . '> .
	    		<' . $uri . '> <' . $schema['hasPatternVariable'] . '> ?s .
	    		?s ?p ?o .
	    		} UNION {
				<' . $uri . '> a <' . $schema['BasicPattern'] . '> .
	    		<' . $uri . '> <' . $schema['hasSelectQuery'] . '> ?s .
	    		?s ?p ?o .
	    		} UNION {
				<' . $uri . '> a <' . $schema['BasicPattern'] . '> .
	    		<' . $uri . '> <' . $schema['hasUpdateQuery'] . '> ?s .
	    		?s ?p ?o .
	    		}
				}';
	
	        $result = $this->_erfurt->getStore()->sparqlQuery($sparqlQuery, array(STORE_USE_AC => false));
	
	        $types = array();
	        $stmt = array();
	        $variable = array();
	        $select = array();
	        $update  = array('insert' => array(), 'delete' => array());
	
	        foreach ($result as $row) {
	            if ($row['p'] === EF_RDF_TYPE) {
	                $types[$row['s']] = $row['o'];
	            } else {
		            $stmt[$row['s']][$row['p']][] = $row['o'];
	            }
	        }
	
	        $basicPattern = new BasicPattern();
	
	        foreach ($stmt as $s => $data) {
	            if ($types[$s] === $schema['BasicPattern']) {
	                if (array_key_exists(EF_RDFS_LABEL,$data)) {
	                    $basicPattern->setLabel($data[EF_RDFS_LABEL][0]);
	                } else {
	                   $basicPattern->setLabel('');
	                }
	                if (array_key_exists(EF_RDFS_COMMENT,$data)) {
	                    $basicPattern->setDescription($data[EF_RDFS_COMMENT][0]);
	                } else {
	                   $basicPattern->setDescription('');
	                }
	            }
	            if (strpos($types[$s],$schema['PatternVariable']) === 0  ) {
	                $type = substr($types[$s],strlen($schema['PatternVariable']) + 1 );
	                if (array_key_exists(EF_RDFS_LABEL,$data) && array_key_exists(EF_RDFS_LABEL, $data) && $type) {
	                    $basicPattern->addVariable($data[EF_RDFS_LABEL][0], $type, $data[EF_RDFS_COMMENT][0]);
	                }
	            }
	
	            if ( $types[$s] === $schema['SelectQuery'] ) {
	                if ( array_key_exists(EF_RDFS_LABEL, $data)) {
	                    $basicPattern->setSelectQuery($data[EF_RDFS_LABEL][0]);
	                }
	            }
	
	            if ( $types[$s] === $schema['UpdateQuery_Insert'] ) {
	                if (
	                    array_key_exists($schema['updatePatternObject'],$data) &&
	                    array_key_exists($schema['updatePatternPredicate'], $data) &&
	                    array_key_exists($schema['updatePatternSubject'], $data)
	                ) {
	                    $pattern = $data[$schema['updatePatternSubject']][0] . ' ' .
	                        $data[$schema['updatePatternPredicate']][0] . ' ' .
	                        $data[$schema['updatePatternObject']][0];
	                    $basicPattern->addUpdateQuery($pattern, 'insert');
	                }
	            }
	
	
	            if ( $types[$s] === $schema['UpdateQuery_Delete'] ) {
	                if (
	                    array_key_exists($schema['updatePatternObject'],$data) &&
	                    array_key_exists($schema['updatePatternPredicate'], $data) &&
	                    array_key_exists($schema['updatePatternSubject'], $data)
	                ) {
	                    $pattern = $data[$schema['updatePatternSubject']][0] . ' ' .
	                        $data[$schema['updatePatternPredicate']][0] . ' ' .
	                        $data[$schema['updatePatternObject']][0];
	                    $basicPattern->addUpdateQuery($pattern, 'delete');
	                }
	            }
	        }
	
	        echo $basicPattern->toArray(true);
        } else {
            $message = array('error' => 'Action: ' . PatternEngineAc::RIGHT_VIEW_STR . ' not allowed.');
            echo json_encode($message);
        }

    }

}
