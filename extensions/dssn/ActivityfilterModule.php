<?php
/**
 * DSSN module – Activity Filter
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_activityfilter
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ActivityfilterModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Filter and Search Activities';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->activityfilter != true) {
            return false;
        }
        return true;
    }

    function getContents()
    {
        $this->view->headScript()->appendScript("
            $(document).ready(function(){
                $('.dssn-activityfilter a').click(function(){
                    var name = $(this).parent().parent().attr('id');  
                    var value = encodeURIComponent($(this).attr('value')); 
                    var me = this;
                    window.location = '".$this->_config->urlBase."dssn/news?name='+name+'&value='+value
                })
            })
            ");
        $this->view->activityverb = 'all';
        if (!empty($_SESSION['DSSN_activityverb'])) {
            if($_SESSION['DSSN_activityverb'] !== "all"){
                $splitted= explode("/", $_SESSION['DSSN_activityverb'],2);
                $uri = $splitted[1];
                $this->view->activityverb = $uri;
            } 
        } 
        $this->view->activityobject = 'all';
        if (!empty($_SESSION['DSSN_activityobject'])) {
            if($_SESSION['DSSN_activityobject'] !== "all"){
                $splitted= explode("/", $_SESSION['DSSN_activityobject'],2);
                $uri = $splitted[1];
                $this->view->activityobject = $uri;
            }
        } 
        $content = $this->render('modules/activityfilter', false, 'data');
        return $content;
    }
}


