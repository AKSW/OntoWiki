<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The main class for the basicimporter plugin.
 *
 * @category   OntoWiki
 * @package    Extensions_Basicimporter
 * @author     Sebastian Tramp <tramp@informatik.uni-leipzig.de>
 */
class BasicimporterPlugin extends OntoWiki_Plugin
{
    /*
     * our event method
     */
    public function onProvideImportActions($event)
    {
        $this->provideImportActions($event);
    }

    /*
     * here we add new import actions
     */
    private function provideImportActions($event)
    {
        $myImportActions = array(
            'basicimporter-rdfweb' => array(
                'controller' => 'basicimporter',
                'action' => 'rdfwebimport',
                'label' => 'Import an RDF resource from the web',
                'description' => 'Tries to fetch a graph from the web.'
            ),
            'basicimporter-rdfupload' => array(
                'controller' => 'basicimporter',
                'action' => 'rdfupload',
                'label' => 'Upload an RDF Dump',
                'description' => 'Parse and import turtle, ntriples, rdfxml and other dumps.'
            ),
            'basicimporter-rdfpaster' => array(
                'controller' => 'basicimporter',
                'action' => 'rdfpaster',
                'label' => 'Paste Source',
                'description' => 'Parses and import turtle, ntriples and rdfxml import from a textfield.'
            ),
        );

        // sad but true, some php installation do not allow this
        if (!ini_get('allow_url_fopen')) {
            unset($myImportActions['basicimporter-rdfwebimport']);
        }

        $event->importActions = array_merge($event->importActions, $myImportActions);
        return $event;
    }
}
