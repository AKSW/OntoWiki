<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – comment
 *
 * Allows to post a comment about a resource.
 *
 * @category   OntoWiki
 * @package    Extensions_Community
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class CommentModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Comment';
    }
    
    public function getContents()
    {
        $url = new OntoWiki_Url(array('controller' => 'community', 'action' => 'comment'), array());
        $this->view->actionUrl = (string)$url;
        
        $content = $this->render('comment');
		
		return $content;
    }
    
    public function shouldShow(){
        // do not show if model is not writeable
        if ( (isset($this->_owApp->selectedModel)) &&
                ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel) ) ) {
            return true;
        } else {
            return false;
        }
    }
}

