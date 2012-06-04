<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Request class
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Request extends Zend_Controller_Request_Http
{    
    /**
     * Returns a parameter from the current request and expands its URI
     * using the local namespace table. It also strips slashes if 
     * magic_quotes_gpc is turned on in PHP.
     *
     * @param string $name the name of the parameter
     * @param boolean $expandNamespace Whether to expand the namespace or not
     * @return mixed the parameter or null if not found
     */
    public function getParam($key, $default = null, $expandNamespace = false)
    {
        // get parameter value from Zend_Request
        $value = parent::getParam($key, $default);
        
        if ($expandNamespace) {
            // expandable parameters cannot be arrays
            if (is_array($value)) {
                $value = current($value);
            }
            
            // expand namespace
            $value = OntoWiki_Utils::expandNamespace($value);
        }
        
        // strip slash quotes if necessary
        if (get_magic_quotes_gpc() and is_string($value)) {
            $value = stripslashes($value);
        }
        
        return $value;
    }
}



