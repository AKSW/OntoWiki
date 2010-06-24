<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'OntoWiki/Navigation.php';

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_querybuilder
 * @author     Sebastian Hellmann <hellmann@informatik.uni-leipzig.de>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class QuerybuilderController extends OntoWiki_Controller_Component
{
    var $prefixHandler;
    /**
     * init() Method to init() normal and add tabbed Navigation
     */
    public function init() {
        // doing init() like on superclass (OntoWiki_Controller_Component)
        parent::init();

        // setup the tabbed navigation
        OntoWiki_Navigation :: reset();
        if(class_exists("QuerybuildingHelper")) {
            OntoWiki_Navigation :: register('listquery', array (
                    'controller' => "querybuilding",
                    'action' => "listquery",
                    'name' => "Saved Queries",
                    'position' => 0,
                    'active' => false
            ));
        }
        OntoWiki_Navigation :: register('queryeditor', array (
                'controller' => "querybuilding",
                'action' => "editor",
                'name' => "Query Editor",
                'position' => 1,
                'active' => false
        ));
        OntoWiki_Navigation :: register('querybuilder', array (
                'controller' => "querybuilder",
                'action' => "manage",
                'name' => "Query Builder ",
                'position' => 2,
                'active' => true
        ));

        if(class_exists("GraphicalquerybuilderHelper")) {
            OntoWiki_Navigation :: register('graphicalquerybuilder', array (
                    'controller' => "graphicalquerybuilder",
                    'action' => "display",
                    'name' => "Graphical Query Builder",
                    'position' => 3,
                    'active' => false
            ));
        }
    }

    /**
     * Action to construct Queries, view their results, save them ...
     */
    public function manageAction() {
        // set the active tab navigation
        OntoWiki_Navigation::setActive('querybuilder',true);

        // creates toolbar and adds two buttons
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Reset Querybuilder', 'id' => 'reset'));
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Update Results', 'class'=>'','id' => 'updateresults'));
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        // creates menu structure
        $viewMenu = new OntoWiki_Menu();
        $viewMenu->setEntry('Toggle Debug Code', 'javascript:toggleDebugCode()');
        $viewMenu->setEntry('Toggle SPARQL Code', 'javascript:toggleSparqlCode()');

        $menu = new OntoWiki_Menu();
        $menu->setEntry('View', $viewMenu);
        $this->view->placeholder('main.window.menu')->set($menu->toArray());

        $include_base = $this->_componentUrlBase;
        $this->view->headScript()->appendFile($this->_config->staticUrlBase . 'extensions/components/querybuilding/resources/savepartial.js');
        $this->view->headScript()->appendFile($include_base.'resources/jquery.autocomplete.min.js');
        $this->view->headScript()->appendFile($include_base.'resources/jquery.json-1.3.min.js');
        $this->view->headScript()->appendFile($include_base.'resources/json2.js');
        $this->view->headScript()->appendFile($include_base.'resources/querybuilder.js');

        // adding stylesheet for autocompletion-boxes
        $this->view->headLink()->appendStylesheet($include_base.'css/jquery.autocomplete.css');

        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Query Builder'));

        $tPattern = $this->_request->getParam('patterns');

        if( empty($tPattern) ) {
            $default["qb_triple_0"] = array("s"=>"?subject","p"=>"?predicate","o"=>"?object","otype"=>"uri");
        } else {
            $default = json_decode($tPattern, true);
        }

        $this->view->tPattern = json_encode($default);
        $this->view->headScript()->appendScript($this->_jscript(json_encode($default)));
    }

    public function autocompleteAction() {
        $debug = defined('_OWDEBUG');
        if($debug) {
            echo 'debug mode is: '.(($debug)?'on':'off')."\n";
        }
        /*
    	if(false && $debug){
				
    		echo "<xmp>";
    		//$q = "";
    		$q = "Je";
			$limit = 50;
			$json = "{\"qb_triple_0\": {\"s\": \"?actors\", \"p\": \"?p\", \"o\": \"Je\", \"search\": \"o\"}}";
    	}else{
        */
        $json = ($_REQUEST['json']);
        $q = ($_REQUEST['q']);
        $limit = ($_REQUEST['limit']);


        $config = self::_object2array( $this->_privateConfig);
        require_once('lib/AjaxAutocompletion.php');
        $u = new AjaxAutocompletion($q,$json, $limit,$config, $debug);

        //$u = new AjaxAutocompletion("",$json, $limit,$config, $debug);
        echo $u->getAutocompletionList();
        if($debug) {
            echo "\n Debug code:\n  ".htmlentities($u->getQuery());
        }
        die;

    }

    public function updatetableAction() {
        // setting up some needed variables
        $config = $this->_privateConfig->toArray();

        // stripping automatic escaped chars
        $params = array();
        foreach ($this->_request->getParams() as $key => $param) {
            if ( get_magic_quotes_gpc() ) {
                $params[$key] = stripslashes($param);
            } else {
                $params[$key] = $param;
            }
        }

        $now = microtime(true);
        require_once('lib/AjaxUpdateTable.php');
        $ajaxUpdate     = new AjaxUpdateTable($params['json'], $params['limit'], $config, true);
        $data           = $ajaxUpdate->getResultAsArray();
        $queryString    = (string) $ajaxUpdate->getSPARQLQuery();
        $time = round((microtime(true)-$now)*1000)." msec needed";

        // disabling layout and template as we make no use of these
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        // now rendering in updatetable.phtml
        $this->view->prefixHandler = $ajaxUpdate->getPrefixHandler();
        $this->view->translate      = $this->_owApp->translate;
        $this->view->data = $data;
        $this->view->cssid = "qbresulttable";
        echo $this->view->render('partials/resultset.phtml', array('data' => $data, 'caption'=>'Results','cssid'=> 'qbresulttable'));
    }

    public function updatesparqlAction() {
        $config = self::_object2array( $this->_privateConfig);
        $json = ($_REQUEST['json']);
        $limit = ($_REQUEST['limit']);
        require_once('lib/AjaxUpdateTable.php');
        $u = new AjaxUpdateTable($json, $limit,$config, true);
        echo $u->getSPARQLQuery();
    }

    private function _jscript($patterns) {
        $jscript = "qb_js_tripleinfo = ".$patterns.";\n";


        $patterns = json_decode($patterns, true);
        $arr = array();
        foreach ($patterns as $key=>$value) {
            $arr[] = str_replace("qb_triple_", "", $key);
        }
        sort($arr, SORT_NUMERIC);
        $max = $arr[count($arr)-1]+1;

        //TODO maybe not needed
        $jscript .="resetLink = '";
        $first = true;
        $vars = "";
        foreach ($_REQUEST as $key=>$value) {
            if($first) {
                $first=false;
                $vars = "?".$key."=".$value;
            }else {
                $vars = "&".$key."=".$value;
            }
        }
        $jscript .= $vars."';\n";

        $jscript .= "maxID = ".$max.";\n".
                "function getNextID (){
    	  			retval = maxID;
    	  			maxID++;
    	  			return retval;
    	  			};\n";

        // $conf = "config = {};\n";
        // $conf .= self::_jshelp($this->_privateConfig);
        //.json_encode($this->_privateConfig).";\n";

        //echo $conf; die;
        //print_r($this->_privateConfig);
        //die;
        return $jscript;

    }

    private static function _jshelp($config) {
        $conf = self::_object2array( $config);
        $ret = "";
        foreach($conf as $key=>$value) {
            $ret .= "config['$key'] = '$value';\n";
        }
        return $ret;
    }

    private static  function _object2array($object) {
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
        }
        else {
            $array = $object;
        }
        return $array;
    }


}

