<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module controller.
 *
 * @package    OntoWiki_Controller
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ModuleController extends OntoWiki_Controller_Base
{
    public function indexAction()
    {
        $this->_forward('get');
    }
    
    public function getAction()
    {        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        // mandatory
        if (!isset($this->_request->name)) {
            throw new OntoWiki_Exeption("Missing parameter 'name'.");
        }
        $name = $this->_request->name;
        
        if (isset($this->_request->class)) {
            $class = $this->_request->class;
        } else {
            $class = '';
        }
        
        if (isset($this->_request->id)) {
            $id = $this->_request->id;
        } else {
            $id = '';
        }
        
        $this->_response->setHeader('Content-Type', 'text/html');
        $this->_response->setBody($this->view->module($name, new Zend_Config(array('classes' => $class, 'id' => $id), true)));
    }
}


