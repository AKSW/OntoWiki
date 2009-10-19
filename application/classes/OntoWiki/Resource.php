<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id:$
 */

require_once 'Erfurt/Rdfs/Resource.php';

/**
 * OntoWiki resource class
 *
 * Extends Erfurt_Rdf_Resource with a getTitle method OntoWiki uses
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Resource extends Erfurt_Rdfs_Resource
{
    /**
     * Human-readable representation of this resource.
     */
    protected $_title = null;
    
    public function __construct($uri, $graph)
    {
        parent::__construct($uri, $graph);
    }
    
    /**
     * Returns a human-readable representation of this resource or false
     * if no suitable value has been found.
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (null === $this->_title) {
            require_once 'OntoWiki/Model/TitleHelper.php';
            $titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
            $titleHelper->addResource($this->getUri());
            $this->_title = $titleHelper->getTitle($this->getUri());
        }
        
        return $this->_title;
    }
}



