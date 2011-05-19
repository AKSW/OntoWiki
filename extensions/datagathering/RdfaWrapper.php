<?php
require_once 'Erfurt/Wrapper.php';

/**
 * Initial version of a wrapper for RDFa.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @copyright  Copyright (c) 2010 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class RdfaWrapper extends Erfurt_Wrapper
{
    protected $_cachedData = array();
    
    
    public function getDescription()
    {
        return 'A simple wrapper to extract RDFa data HTML pages';
    }
    
    public function getName()
    {
        return 'RDFa';
    }
    
    public function init($config)
    {
        parent::init($config);
        
    }
    
    public function isAvailable($r, $graphUri)
    {
        return true;
    }
    
    public function isHandled($r, $graphUri)
    {
        return true;
    }
    
    public function run($r, $graphUri)
    {
        include_once('ARC2/ARC2.php');
        
        $uri = $r->getUri();
        $url = $r->getLocator();

        $config = array('auto_extract' => 0);
        $parser = ARC2::getSemHTMLParser();
        @$parser->parse($url);
        @$parser->extractRDF('rdfa');

        $triples = $parser->getTriples();
        if(!is_array($triples) || (count($triples) == 0)) {
             return false;
        }
        
        
        $data = array();
        $data[$uri] = array();
        
        // transform arc2 triple to RDF_PHP structure
        // TODO: blank nodes?
        foreach ($triples as $triple) {
            // but only for the requested resource and not ignored properties
            // TODO: use a generic fetch preset from linkeddata wrapper
            
            if ( ($triple['s'] == $uri) && 
                    (array_search ($triple['p'], $this->_config->ignore->toArray()) === false) ) {
                
                // create resource objects
                if ($triple['o_type'] == 'uri') {
                    $data[$uri][$triple['p']][] = array(
                        'type'  => 'uri',
                        'value' => $triple['o']
                    );
                }
                
                // create literal objects
                if ($triple['o_type'] == 'literal') {
                    $newObject = array(
                        'type'  => 'literal',
                        'value' => $triple['o']
                    );
                    if ($triple['o_lang'] != '') {
                        $newObject['lang'] = $triple['o_lang'];
                    }
                    if ($triple['o_datatype'] != '') {
                        $newObject['datatype'] = $triple['o_datatype'];
                    }
                    $data[$uri][$triple['p']][] = $newObject;
                }
            }
        }

        // set a default class type if configured and no type was extracted
        if ((!isset($data[$uri][EF_RDF_TYPE])) &&
            (isset($this->_config->defaultClass))) {
                $data[$uri][EF_RDF_TYPE][] = array(
                    'type'  => 'uri',
                    'value' => $this->_config->defaultClass
                );
        }
        
        $fullResult['status_description'] = "RDFa data found for URI $uri";
        $fullResult['add'] = $data;
        $fullResult['status_codes'] = array(Erfurt_Wrapper::NO_MODIFICATIONS);
        $fullResult['status_codes'][] = Erfurt_Wrapper::RESULT_HAS_ADD;
        
        return $fullResult;
    }
}
