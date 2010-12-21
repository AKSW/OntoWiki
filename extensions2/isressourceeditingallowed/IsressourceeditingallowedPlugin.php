<?php

/**
 * This class includes a plugin to check wheter ressourceediting is allowed
 * on the currently selected resource
 *
 * Long description for class (if any) ...
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Michael Niederstätter <michael.niederstaetter@gmail.com>
 */
class IsressourceeditingallowedPlugin extends OntoWiki_Plugin
{
    public function isressourceeditingallowed($event)
    {
        $owApp = OntoWiki_Application::getInstance();
        $rightProperty = $this->_privateConfig->rightproperty;
        $ownerProperty = $this->_privateConfig->ownerproperty;

        if(isset($rightProperty)) {

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?right')
                ->setWherePart('WHERE {<' . $owApp->selectedResource . '>  <' . $rightProperty . '> ?right.}');

            $result = $owApp->selectedModel->sparqlQuery($query);
            $allowediting = false;

            if(!empty($result)) {
            //check wheter rights are set as "private")
                if($result[0]['right']==='private') {
                    if(isset($ownerProperty)) {
                        $query = new Erfurt_Sparql_SimpleQuery();

                        $query->setProloguePart('SELECT ?owner')
                            ->setWherePart('WHERE {<' . $owApp->selectedResource . '>  <' . $ownerProperty . '> ?owner.}');

                        $result = $owApp->selectedModel->sparqlQuery($query);

                        $owned = false;
                        foreach ($result as $owner) {

                        //check wheter has_owner = currentuser
                            if($owner['owner']===$owApp->getUser()->getUri()) {
                            //User matches to
                                $owned = true;
                            }
                        }
                        if($owned) {
                        //allow editing
                            $allowediting = true;
                        }
                    }
                    else {
                        $allowediting = true;
                    }
                }
                else {
                //resource is not set to private
                    $allowediting = true;
                }

            }
            else {$allowediting = true;}
        }
        else {
        //if property is not set return always true
            $allowediting = true;
        }

        if($allowediting) {
            return true ;
        }
        else {
            return false ;
        }


    }

}
