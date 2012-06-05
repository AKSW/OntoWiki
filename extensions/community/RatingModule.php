<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – comment
 *
 * Allows to post a comment about a resource.
 *
 * @category   OntoWiki
 * @package    Extensions_Community
 * @author     Christian Maier, Niederstätter Michael
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class RatingModule extends OntoWiki_Module {
    public function getTitle() {
        return 'Rating';
    }

    public function getContents() {



        $url = new OntoWiki_Url(array('controller' => 'community', 'action' => 'rate'), array());
        $this->view->actionUrl = (string) $url;

        $query = new Erfurt_Sparql_SimpleQuery();
        $model = new OntoWiki_Model(Erfurt_App::getInstance()->getStore(), $this->_owApp->selectedModel);


        //query ratings for the current resource

        $query->setProloguePart('
                prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                prefix ns0: <http://rdfs.org/sioc/ns#>
                prefix ns1: <http://rdfs.org/sioc/types#>
                prefix terms: <http://purl.org/dc/terms/>
                SELECT *')
            ->setWherePart('
                where {
                    ?rating rdf:type ns1:Poll.
                    ?rating ns0:about <' . $this->_owApp->selectedResource . '>.
                    ?rating ns0:note ?note.
                    ?rating ns0:has_creator ?creator}

              ');

        $results =  $this->_store->sparqlQuery($query);

        $ratingArray = Array();

        $user  = (string) $this->_owApp->getUser()->getUri();

        $creator = '0';
        $creatorNote= '0';
        foreach($results as $result) {
            $ratingArray[] = (double)$result['note'];
            if($user == $result['creator']) {
                $creator = '1';
                $creatorNote = (double)$result['note'];
            }
        }


        $rating = '0';
        $ratingvalue = '0';

        if(count($ratingArray)!=0) {
            $ratingvalue = round(array_sum($ratingArray)/count($ratingArray),2);

            $rating = round(array_sum($ratingArray)/count($ratingArray)*2-1,0);
        }
        $ratingJs ='var rating = '.($rating).'; var count ='.count($ratingArray).'; var creator ='.$creator.';
               var creatorNote ='.($creatorNote-1).'; var ratingValue ='.$ratingvalue.';';



        // append Java Scripts and Style Sheets

        $this->view->headScript()->appendScript($ratingJs);
        $this->view->headScript()->appendFile($this->_config->urlBase.'extensions/modules/rating/jquery.MetaData.js');
        $this->view->headScript()->appendFile($this->_config->urlBase.'extensions/modules/rating/jquery.rating.js');
        $this->view->headScript()->appendFile($this->_config->urlBase.'extensions/modules/rating/jquery.rating.pack.js');
        $this->view->headScript()->appendFile($this->_config->urlBase.'extensions/modules/rating/rating.js');
        $this->view->headLink()->appendStylesheet($this->_config->urlBase.'extensions/modules/rating/jquery.rating.css');

        $this->view->count = count($ratingArray);
        $this->view->rating = $ratingvalue;

        $content = $this->render('rating');

        return $content;
    }

    public function shouldShow() {
        // show only if enabled in private config
        if ($this->_privateConfig->enableRating == false) {
            return false;
        }

        // the rating action is displayed only for registered users
        // and if set in the module.ini file only for predefined resources
        if($this->_owApp->getUser()->getUsername()=='Anonymous' || !$this->typeAllowed()) {
            return false;
        }
        else {
            return true;
        }
    }


    private function typeAllowed() {

        if($this->_privateConfig->ratingClass)
        {
        $classArray = $this->_privateConfig->ratingClass->toArray();

            $store = $this->_owApp->erfurt->getStore();
            $resource = $this->_owApp->selectedResource;

            $query = new Erfurt_Sparql_SimpleQuery();

            $query->setProloguePart('SELECT *')
                ->setWherePart('WHERE
                                     {
                                        <'.$resource.'> <'.EF_RDF_TYPE.'> ?type
                                     }');

            $results = $store->sparqlQuery($query);
            if (in_array($results[0]['type'],$classArray)) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }

    }




}

