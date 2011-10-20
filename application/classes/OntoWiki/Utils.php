<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki utility class.
 *
 * @category OntoWiki
 * @package Utils
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Utils
{
    /**
     * Pseudo-prefix for the current graph
     */
    const DEFAULT_BASE = '__default';
    
    /**
     * Separator between namespace prefix and local part
     */
    const PREFIX_SEPARATOR = ':';
    
    /**
     * An array of namespace prefixes and IRIs.
     * @var array
     */
    private static $_namespaces = null;
    
    /**
     * Returns a compact URI (cURI) as required by RDFa syntax specification 
     * {@link http://www.w3.org/MarkUp/2008/ED-rdfa-syntax-20080125/#s_curieprocessing}.
     * Adds an ad-hoc namespace prefix if it cannot find one for the URI, resulting
     * in a value that is guaranteed to be a cURI.
     * 
     * @param string $uri the URI to be converted
     * @return string|null cURI or null if no cURI could be created
     */
    public static function compactUri($uri, $saveMode = false)
    {
        $namespaces    = self::_getNamespaces();
        $selectedModel = OntoWiki::getInstance()->selectedModel;
        $compactUri    = null;
        $prefix        = null;
        
        // split URI in namespace and local part at "/" or "#"
        $matches = array();
        preg_match('/^(.+[#\/])(.+[^#\/])$/', $uri, $matches);
        
        if (count($matches) == 3) {
            $namespace = $matches[1];
            $localPart = $matches[2];
            
            if (!empty($localPart) && $selectedModel) {
                try {
                    $prefix = $selectedModel->getNamespacePrefix($namespace);
                } catch (Erfurt_Exception $e) {
                    // just to be save
                    $prefix = null;
                }
            }
        }
        
        // usable prefix found?
        if (null !== $prefix) {
            $compactUri = $prefix
                        . self::PREFIX_SEPARATOR
                        . $localPart;
        } else {
            if ($saveMode) {
                throw new OntoWiki_Utils_Exception("Unable to compact URI <$uri>.");
            } else {
                // return URI unmodified
                $compactUri = $uri;
            }
        }
        
        return $compactUri;
    }
    
    /**
     * Replaces a namespace URI with its prefix if found in the local 
     * prefix table.
     *
     * @deprecated Use compactUri instead
     * @param string $uri The URI
     * @param boolean $prependBase Whether to prepend the graph base URI
     * @return string
     */
    public static function contractNamespace($uri, $prependBase = false)
    {
        $namespaces = self::_getNamespaces();
        
        $matches = array();
        preg_match('/^(.+[#\/])(.+[^#\/])$/', $uri, $matches);
        
        $matchesCount = count($matches);
        if ($matchesCount >= 3) {
            $selectedModel = OntoWiki::getInstance()->selectedModel;
            if ($selectedModel && $matches[1] == $selectedModel->getBaseIri()) {
                if ($prependBase) {
                    return self::DEFAULT_BASE . ':' . $matches[2];
                }
                return $matches[2];
            } else if (array_key_exists($matches[1], $namespaces)) {
                return $namespaces[$matches[1]] . ':' . $matches[2];
            }
        }
        
        return $uri;
    }
    
    /**
	 * Calculates the Difference between two timestamps
	 *
	 * @param string/int $start_timestamp
	 * @param integer $end_timestamp
	 * @param integer $unit (default 0)
	 * @return string
	 */
	public static function dateDifference($start_timestamp, $end_timestamp = false, $unit = 0)
	{
	    $translate  = OntoWiki::getInstance()->translate;
	    	    
            $days_seconds_star 	= (23 * 56 * 60) + 4.091; // Star Day
            $days_seconds_sun 	= 24 * 60 * 60; // Sun Day
            if($unit == 0){
                $dayInSeconds = $days_seconds_sun;
            } else {
                $dayInSeconds = $days_seconds_star;
            }

            if (is_int($start_timestamp)) {
                    if ($end_timestamp) {
                            $difference_seconds = $end_timestamp - $start_timestamp;
                    } else {
                            $end_timestamp = time();
                            $difference_seconds = $end_timestamp - $start_timestamp;
                    }
            } else if (is_string($start_timestamp)) {
                    if ($end_timestamp) {
                            if ($t = strtotime($start_timestamp)) {
                                            $difference_seconds = $end_timestamp - $t ;
                            } else {
                                    throw new Exception('unexpected format of timestamp.');
                            }
                    } else {
                            $end_timestamp = time();
                            if ($t = strtotime($start_timestamp)) {
                                            $difference_seconds = $end_timestamp - $t;
                            } else {
                                    throw new Exception('unexpected format of timestamp.');
                            }
                    }
            } else {
                    throw new Exception('unexpected type of timestamp. expected string (date) or int (timestamp), got '. gettype($start_timestamp) .' instead');
            }
            
            //if start is in the past, we use negative differences
            $difference_seconds *= -1;

            // show e.g. 'moments ago' if time is less than one minute
            if (abs($difference_seconds) < 60) {
                if($difference_seconds < 0){
                    return $translate->_('moments ago');
                } else {
                    return $translate->_('in moments');
                }
                
            } else {
                $difference_minutes = round(($difference_seconds / 60));

                // show e.g. 'approx. x minutes ago' if time is less than one hour
                if (abs($difference_minutes) == 1) {
                    if($difference_seconds < 0){
                        return $translate->_('approx. 1 minute ago');
                    } else {
                        return $translate->_('in approx. 1 minute');
                    }
                } else if (abs($difference_minutes) < 60) {
                    if($difference_minutes < 0){
                        return sprintf($translate->_('approx. %d minutes ago'), abs($difference_minutes));
                    } else {
                        return sprintf($translate->_('in approx. %d minutes'), abs($difference_minutes));
                    }
                } else {
                    $difference_hours = round(($difference_seconds / 3600));

                    // show e.g. 'approx. x hours
                    if (abs($difference_hours) == 1) {
                        if($difference_hours < 0){
                            return $translate->_('approx. 1 hour ago');
                        } else {
                            return $translate->_('in approx. 1 hour');
                        }
                    } else if (abs($difference_hours) <= 48) {
                        if($difference_hours < 0){
                            return sprintf($translate->_('approx. %d hours ago'), abs($difference_hours));
                        } else {
                            return sprintf($translate->_('in approx. %d hours'), abs($difference_hours));
                        }
                    } else {
                        $difference_days = round(($difference_seconds / $dayInSeconds));

                        // else return e.g. 'approx. x days ago'
                        if($difference_days < 0){
                            return sprintf($translate->_('approx. %d days ago'), abs($difference_days));
                        } else {
                            return sprintf($translate->_('in approx. %d days'), abs($difference_days));
                        }
                    }
                }
            }
	}
    
    /**
     * Expands a namespace prefix in a quialified name to a full URI if found
     * in the local namespace table.
     *
     * @param string $qName The qualified name, e.g. 'foaf:Person'
     * @return string
     */
    public static function expandNamespace($qName)
    {
        $namespaces = self::_getNamespaces();
        
        if (trim($qName) != '') {
            $parts = explode(':', $qName);
            $namespaces = array_flip($namespaces);
            
            $prefix    = isset($parts[0]) ? $parts[0] : '';
            $localPart = isset($parts[1]) ? $parts[1] : '';

            if (array_key_exists($prefix, $namespaces) && !empty($localPart)) {
                $qName = $namespaces[$prefix];
                array_shift($parts);
                $qName .= implode('', $parts);
            } else {
                // TODO: check store, better URI check, use model base URI
                $owApp = OntoWiki::getInstance();
                
                $erfurtConfig = Erfurt_App::getInstance()->getConfig();
                $uriSchemas   = array_flip($erfurtConfig->uri->schemata->toArray());
                
                if (array_key_exists($prefix, (array)$uriSchemas)) {
                    // prefix is an allowed URI schema
                    return $qName;
                } else if ($owApp->selectedModel instanceof Erfurt_Rdf_Model) {
                    $qName = $owApp->selectedModel->getBaseIri() . $qName;
                }
            }

            return $qName;
        }
    }
    
    /**
     * Returns the local part of a URI.
     *
     * @param string $uri
     * @return string
     */
    public static function getUriLocalPart($uri)
    {
        $namespaces = self::_getNamespaces();
        $localPart   = $uri;
              
        $matches = array();
        preg_match('/^(.+[#\/])(.+[^#\/])$/', $uri, $matches);
        
        if (count($matches) == 3) {
            if (trim($matches[2]) != '') {
                $localPart = $matches[2];
            }
        }
  
        return $localPart;
    }
    
    /**
     * Matches an array of mime types against the Accept header in a request.
     *
     * @param Zend_Controller_Request_Abstract $request the request
     * @param array $supportedMimetypes The mime types to match against
     * @return string
     */
    public static function matchMimetypeFromRequest(Zend_Controller_Request_Abstract $request, array $supportedMimetypes)
    {
        // get accept header
        $acceptHeader = strtolower($request->getHeader('Accept'));
        
        require_once 'Mimeparse.php';
        try {
            $match = @Mimeparse::best_match($supportedMimetypes, $acceptHeader);
        } catch (Exception $e) {
            $match = '';
        }
        
        return $match;
    }
    
    /**
     * Shortens a string by splitting it in the middle and concatenating
     * both parts with an ellipse (…)
     *
     * @param string $string The string to be split
     * @param int $maxLength The maximum length of the resulting string
     * @return string
     */
    public static function shorten($string, $maxLength = 20)
    {
        if (($maxLength == 0) || (strlen($string) < $maxLength)) {
            return $string;
        }
        
        $offset = floor($maxLength / 2);
        $short  = rtrim(substr($string, 0, $offset), '.') 
                . '&hellip;' 
                . ltrim(substr($string, -$offset, $offset), '.');

        return $short;
    }
    
    /**
     * Loads the namespaces from the currently selected model
     */
    private static function _reloadNamespaces()
    {
        $model = OntoWiki::getInstance()->selectedModel;
        if ($model instanceof Erfurt_Rdfs_Model) {
            self::$_namespaces = $model->getNamespaces();
        } else {
            self::$_namespaces = array();
        }
    }
    
    /**
     * Loads the local namespace table from the currently active graph.
     *
     * @return array
     */
    private static function _getNamespaces()
    {
        if (null === self::$_namespaces) {
            self::_reloadNamespaces();
        }
        
        return self::$_namespaces;
    }
}

