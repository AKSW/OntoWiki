<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki Keyboard Shortcuts
 *
 * this is a fake module since it only includes css and js files
 * this was not possible in a plugin (dont know why)
 *
 * @package    ontowiki
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: $
 */
class KeyboardModule extends OntoWiki_Module
{
    public function init()
    {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'jquery.hotkeys.js');
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'keyboard.js');
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'keyboard.css');
    }

    public function shouldShow()
    {
        return false;
    }    
}
