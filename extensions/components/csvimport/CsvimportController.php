<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Component controller for the CSV Importer.
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage Csvimport
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class CsvimportController extends OntoWiki_Controller_Component
{
    protected $_dimensions = null;
    protected $_columnMappings = null;
    protected $_targetGraph = null;
    
    public function init()
    {
        // init component
    }
    
    public function importAction()
    {
        // TODO: show import dialogue and import file
    }
    
    public function mappingAction()
    {
        // TODO: show table and let user define domain mapping
    }
    
    protected function _getColumnMapping()
    {
        $columnMapping = array(
            array(
                'property' => 'http://xmlns.com/foaf/0.1/name', 
                'label' => 'Name', 
                'col' => 3, 
                'row' => 2, 
                'items' => array(
                    'type' => 'uri', 
                    'class' => 'http://xmlns.com/foaf/0.1/Person', 
                    'start' => array('col' => 3, 'row' => 2), 
                    'end' => array('col' => 3, 'row' => 20)
                ), 
            ), 
            array(
                'property' => 'http://purl.org/dc/elements/1.1/', 
                'label' => 'Titel', 
                'col' => 4, 
                'row' => 2,
                'items' => array(
                    'type' => 'literal', 
                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string', 
                    'start' => array('col' => 4, 'row' => 2), 
                    'end' => array('col' => 4, 'row' => 20)
                )
            )
        );
    }
    
    protected function _getDimensions()
    {
        $dimensions = array(
            'http://example.com/dimension1' => array(
                'label' => 'Age', 
                'elements' => array(
                    'http://example.com/dimension1/0-6' => array(
                        'col' => 2, 
                        'row' => 2, 
                        'label' => '0-6', 
                        'items' => array(
                            'start' => array('col' => 2, 'row' => 3), 
                            'end'   => array('col' => 2, 'row' => 20)
                        )
                    )
                ), 
                'http://example.com/dimension1/7-12' => array(
                    'col' => 3, 
                    'row' => 2, 
                    'label' => '7-12', 
                    'items' => array(
                        'start' => array('col' => 3, 'row' => 3), 
                        'end'   => array('col' => 3, 'row' => 20)
                    )
                )
            ),  
            'http://example.com/dimension2' => array(
                'label' => 'Region', 
                'elements' => array(
                    'http://example.com/dimension2/Africa' => array(
                        'col' => 1, 
                        'row' => 3, 
                        'label' => 'Africa', 
                        'items' => array(
                            'start' => array('col' => 2, 'row' => 3), 
                            'end'   => array('col' => 2, 'row' => 20)
                        )
                    )
                )
            )
        );
        
        return $dimensions;
    }
}
