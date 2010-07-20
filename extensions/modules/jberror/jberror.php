<?php
// vim: sw=4:sts=4:expandtab
require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – jberror
 *
 * Tries to find the mysqli_stmt bug
 * http://code.google.com/p/ontowiki/issues/detail?id=880
 *
 * @package    ontowiki
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */
class JberrorModule extends OntoWiki_Module
{
    public function init()
    {
    }

    public function getContents()
    {        
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = "instances";


        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = "instances";
        if($listHelper->listExists($listName)){
            $list = $listHelper->getList($listName);
            $listHelper->addList($listName, $list, $this->view);
            $this->view->message = $listName;
        } else {
            $this->view->message = "anderer Fall eingetreten";
        }
            return $this->render('jberror');
    }

    public function shouldShow()
    {
        return true;
    }


    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


