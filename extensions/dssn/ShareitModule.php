<?php
/**
 * DSSN module – Share It!
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_bdays
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ShareitModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Share It!';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->shareit != true) {
            return false;
        } else {
            return true;
        }
    }

    function getContents()
    {
        // prepare template data
        $data    = new stdClass();
        $formUrl = new OntoWiki_Url( array('controller' => 'dssn', 'action' => 'add'), array() );
        $data->formUrl = (string) $formUrl;

        $content = array(
            'Status' => $this->render('modules/shareit-status', $data, 'data'),
            'Foto'   => $this->render('modules/shareit-foto',   $data, 'data'),
            'Link'   => $this->render('modules/shareit-link',   $data, 'data'),
            'Video'  => $this->render('modules/shareit-video',  $data, 'data')
        );
        return $content;
    }
}


