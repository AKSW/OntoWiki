<?php
/**
 * OntoWiki module – bookmarklet
 *
 * show a bookmarklet link on model info
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_bookmarklet
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class BookmarkletModule extends OntoWiki_Module {

    public function init() {

    }

    public function getTitle() {
        return 'Bookmarklet';
    }

    public function getContents() {
        $this->view->infomessage = 'Use this Bookmarklet to add content to this Knowledge Base.';
        return $this->render('bookmarklet');
    }

}


