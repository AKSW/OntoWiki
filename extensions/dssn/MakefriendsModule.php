<?php
/**
 * DSSN module – Make Friends
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_bdays
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class MakefriendsModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Search Contacts / Make new Friends';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->makefriends != true) {
            return false;
        }
        return true;
    }

    /*
     * returns all incoming friending requests from the pingback extension
     */
    private function _getIncomingRequests()
    {
        $dummyfriend   = array (
            'webid'    => 'http://sebastian.tramp.name',   // webid URI
            'name'     => 'Sebastian Tramp',               // name / abbr of the person
            'relation' => 'http://xmlns.com/foaf/0.1/',    // property URI
            'rlabel'   => 'knows',                         // property label
            'time'     => 'Fri Aug 12 12:57:54 CEST 2011', // sortable time
            'tlabel'   => '2 min. ago',                    // display time
        );
        $friends = array();
        //$friends[] = $dummyfriend;

        return $friends;
    }

    function getContents()
    {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'js/makefriend.js');

        $data = new StdClass;
        $data->requests = $this->_getIncomingRequests();

        $content = $this->render('modules/makefriends', $data, 'data');
        return $content;
    }
}


