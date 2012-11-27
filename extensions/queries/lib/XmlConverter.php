<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */


/**
 * Converts Virtuoso-specific SPARQL results XML format into an 
 * array representation that can be further processed.
 *
 * The array structure conforms to what you would get by 
 * applying json_decode to a JSON-encoded SPARQL result set
 * ({@link http://www.w3.org/TR/rdf-sparql-json-res/}).
 *
 * @category  OntoWiki
 * @package   Extensions_Queries_Lib
 * @author    Christian Würker <christian.wuerker@ceus-media.de
 * @author    Norman Heino <norman.heino@gmail.com>
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class XmlConverter
{
    /**
     * @var array
     */
    protected $_namespaces = array(
        'xml' => 'http://www.w3.org/XML/1998/namespace'
    );
    
    /**
     * Detects namespaces and prefixes from a DOM document
     *
     * @param DOMDocument $document
     */
    public function detectNamespacesFromDocument(DOMDocument $document)
    {
        $nodes = $document->getElementsByTagNameNS('*', '*');
        
        foreach ($nodes as $node) {
            if (!isset($this->_namespaces[$node->prefix])) {
                $this->_namespaces[$node->prefix] = $node->namespaceURI;
            }
        }
    }
    
    /**
     * Converts an XML result set to an array
     */
    public function toArray($xmlSparqlResults)
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->loadXml(utf8_encode($xmlSparqlResults));
        
        $this->detectNamespacesFromDocument($document);
        
        $vars     = array();
        $bindings = array();
        $root   = $document->firstChild;
        $set    = $root->firstChild;
        
        foreach ($set->childNodes as $node) {
            if (!$node->hasChildNodes()) {
                continue;
            }
            
            $row = array();
            foreach ($node->childNodes as $binding) {
                $attrKey    = $binding->getAttributeNodeNS($this->_namespaces['rs'], 'name');
                $nodeValue  = $binding->firstChild;
                $dataKey    = $attrKey->value;
                
                if (!in_array($dataKey, $vars)) {
                    array_push($vars, $dataKey);
                }
                
                $attributes = array();
                foreach ($nodeValue->attributes as $attribute) {
                    $attributes[$attribute->name] = $attribute->value;
                }
                
                switch (true) {
                    case array_key_exists('resource', $attributes):
                        $row[$dataKey] = array(
                            'value'    => $nodeValue->getAttributeNodeNS($this->_namespaces['rdf'], 'resource')->value, 
                            'type'     => 'uri'
                        );
                    break;
                    case array_key_exists('nodeID', $attributes):
                        $row[$dataKey] = array(
                            'value'    => $nodeValue->getAttributeNodeNS($this->_namespaces['rdf'], 'nodeID')->value, 
                            'type'     => 'bnode'
                        );
                    break;
                    default:
                        // literal
                        $lang     = $nodeValue->getAttributeNodeNS($this->_namespaces['xml'], 'lang');
                        $datatype = $nodeValue->getAttributeNodeNS($this->_namespaces['rdf'], 'datatype');
                        
                        $row[$dataKey] = array(
                            'value'    => trim($nodeValue->textContent), 
                            'type'     => $datatype ? 'typed-literal' : 'literal'
                        );
                        
                        if ($datatype) {
                            $row[$dataKey]['datatype'] = (string) $datatype->value;
                        }
                        
                        if ($lang) {
                            $row[$dataKey]['xml:lang'] = (string) $lang->value;
                        }
                    break;
                }
            }
            
            // add row
            array_push($bindings, $row);
        }
        
        $result = array(
            'head'     => array('vars' => $vars), 
            'bindings' => $bindings
        );
        
        return $result;
    }
}
