<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki exception base class
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Http
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Http_Exception extends OntoWiki_Exception
{
    const ERROR_CODE_BASE = 3000;
    
    protected $_responseCode = 0;
    
    public function __construct($responseCode, $message = null)
    {
        parent::__construct(
            ((null !== $message) ? $message : Zend_Http_Response::responseCodeAsText($responseCode)), 
            self::ERROR_CODE_BASE + (int)$responseCode
        );
        
        $this->_responseCode = $responseCode;
    }
    
    public function getResponseMessage()
    {
        return $this->getMessage();
    }   
    
    public function getResponseCode()
    {
        return $this->_responseCode;
    }
}
