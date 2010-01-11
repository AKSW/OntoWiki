<?php 
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_community
 */

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_community
 */
class CommunityController extends OntoWiki_Controller_Component {

    public function listAction(){

        $resource    = $this->_owApp->selectedResource;
        $translate   = $this->_owApp->translate;

        $title = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace($resource->getIri());
        $windowTitle = sprintf($translate->_('Discussion about %1$s'), $title);
        $this->view->placeholder('main.window.title')->set($windowTitle);

        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $commentType     = $this->_privateConfig->comment->type;
        $contentProperty = $this->_privateConfig->content->property;
        $dateProperty    = $this->_privateConfig->date->property;

        // get all resource comments
        $commentSparql = 'SELECT DISTINCT ?author ?comment ?content ?date ?alabel
            WHERE {
                ?comment <' . $aboutProperty . '> <' . $resource . '>.
                ?comment a <' . $commentType . '>.
                ?comment <' . $creatorProperty . '> ?author.
                OPTIONAL {?author <' . EF_RDFS_LABEL . '> ?alabel}
                OPTIONAL {?author <http://xmlns.com/foaf/0.1/nick> ?anick}
                ?comment <' . $contentProperty . '> ?content.
                ?comment <' . $dateProperty . '> ?date.
            }
            ORDER BY ASC(?date)
            LIMIT 10';

        // var_dump($commentSparql);
        $query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);

        $comments = array();
        if ($result = $this->_owApp->selectedModel->sparqlQuery($query)) {
            foreach ($result as $row) {
                if (!empty($row['anick'])) {
                    $row['author'] = $row['anick'];
                } else if (!empty($row['alabel'])) {
                        $row['author'] = $row['alabel'];
                    } else {
                        $row['author'] = OntoWiki_Utils::getUriLocalPart($row['author']);
                    }

                $row['date'] = OntoWiki_Utils::dateDifference($row['date'], null, 3);

                $comments[] = $row;
            }
        }
        $this->view->comments = $comments;
    }

    public function commentAction() {
        if (!$this->_owApp->selectedModel->isEditable()) {
            throw new Erfurt_Ac_Exception("Access control violation. Model not editable.");
        }

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $user  = $this->_owApp->getUser()->getUri();
        $date  = date('c'); // xsd:datetime
        // $date  = date('Y-m-d\TH:i:s'); // xsd:dateTime

        $resource = (string) $this->_owApp->selectedResource;
        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $commentType     = $this->_privateConfig->comment->type;
        $contentProperty = $this->_privateConfig->content->property;
        $dateProperty    = $this->_privateConfig->date->property;
        $content         = $this->getParam('c');

        if (!empty($content)) {

        // make URI
            $commentUri = $this->_owApp->selectedModel->createResourceUri('Comment');

            // preparing versioning
            $versioning                 = $this->_erfurt->getVersioning();
            $actionSpec                 = array();
            $actionSpec['type']         = 110;
            $actionSpec['modeluri']     = (string) $this->_owApp->selectedModel;
            $actionSpec['resourceuri']  = $commentUri;

            $versioning->startAction($actionSpec);

            // create namespaces (todo: this should be based on used properties)
            $this->_owApp->selectedModel->getNamespacePrefix('http://rdfs.org/sioc/ns#');
            $this->_owApp->selectedModel->getNamespacePrefix('http://rdfs.org/sioc/types#');
            $this->_owApp->selectedModel->getNamespacePrefix('http://localhost/OntoWiki/Config/');

            // insert comment
            $this->_owApp->selectedModel->addStatement(
                $commentUri,
                $aboutProperty,
                array('value' => $resource, 'type' => 'uri')
            );

            $this->_owApp->selectedModel->addStatement(
                $commentUri,
                EF_RDF_TYPE,
                array('value' => $commentType, 'type' => 'uri')
            );

            $this->_owApp->selectedModel->addStatement(
                $commentUri,
                $creatorProperty,
                array('value' => (string) $user, 'type' => 'uri')
            );

            $this->_owApp->selectedModel->addStatement(
                $commentUri, $dateProperty, array(
                'value'    => $date,
                'type'     => 'literal',
                'datatype' => EF_XSD_NS . 'dateTime'
                )
            );
            $this->_owApp->selectedModel->addStatement(
                $commentUri, $contentProperty, array(
                'value' => $content,
                'type'  => 'literal'
                )
            );

            // stop Action
            $versioning->endAction();
        }
    }

    public function rateAction() {

        if (!$this->_owApp->selectedModel->isEditable()) {
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception("Access control violation. Model not editable.");
        }


        $user  = $this->_owApp->getUser()->getUri();
        $date  = date('rating'); // xsd:datetime


        $resource = (string) $this->_owApp->selectedResource;
        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $ratingType     = $this->_privateConfig->rating->type;
        $noteProperty = $this->_privateConfig->note->property;
        $dateProperty    = $this->_privateConfig->date->property;
        
        //get rating Value
        $ratingValue        = $this->getParam('rating');


        if (!empty($ratingValue)) {

            $query = new Erfurt_Sparql_SimpleQuery();
            $model    = OntoWiki_Application::getInstance()->selectedModel;
            // $store    = $this->_erfurt->getStore();



            //query rating and creator of rating

            $query->setProloguePart('
                prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                prefix ns0: <http://rdfs.org/sioc/ns#>
                prefix ns1: <http://rdfs.org/sioc/types#>
                SELECT *')

                ->setWherePart('
                where {
                    ?rating rdf:type ns1:Poll.
                    ?rating ns0:about <' . $this->_owApp->selectedResource . '>.
                    ?rating ns0:has_creator ?creator}');

            $results =  $model->sparqlQuery($query);


            if($results) {

                $creatorExists = false;
                foreach($results as $result) {

                    if((string)$user == $result['creator']) {
                        $creatorExists = true;
                        $ratingNote = $result['rating'];
                        break;
                    }
                }

                if($creatorExists) {
                    $this->_owApp->selectedModel->deleteMatchingStatements($ratingNote, NULL , NULL, array());
                }
            }
            

            // make URI
            $ratingNoteUri = $this->_owApp->selectedModel->createResourceUri('Rating');




            // preparing versioning
            $versioning                 = $this->_erfurt->getVersioning();
            $actionSpec                 = array();
            $actionSpec['type']         = 110;
            $actionSpec['modeluri']     = (string) $this->_owApp->selectedModel;
            $actionSpec['resourceuri']  = $ratingNoteUri;

            $versioning->startAction($actionSpec);

            // create namespaces (todo: this should be based on used properties)
            $this->_owApp->selectedModel->getNamespacePrefix('http://rdfs.org/sioc/ns#');
            $this->_owApp->selectedModel->getNamespacePrefix('http://rdfs.org/sioc/types#');
            $this->_owApp->selectedModel->getNamespacePrefix('http://localhost/OntoWiki/Config/');



            // insert rating
            $this->_owApp->selectedModel->addStatement(
                $ratingNoteUri,
                $aboutProperty,
                array('value' => $resource, 'type' => 'uri')
            );

            $this->_owApp->selectedModel->addStatement(
                $ratingNoteUri,
                EF_RDF_TYPE,
                array('value' => $ratingType, 'type' => 'uri')
            );

            $this->_owApp->selectedModel->addStatement(
                $ratingNoteUri,
                $creatorProperty,
                array('value' => (string) $user, 'type' => 'uri')
            );

            $this->_owApp->selectedModel->addStatement(
                $ratingNoteUri, $dateProperty, array(
                'value'    => $date,
                'type'     => 'literal',
                'datatype' => EF_XSD_NS . 'dateTime'
                )
            );
            $this->_owApp->selectedModel->addStatement(
                $ratingNoteUri, $noteProperty, array(
                'value' => $ratingValue,
                'type'  => 'literal'
                )
            );


            $cache = $this->_erfurt->getQueryCache();
            $ret = $cache->cleanUpCache( array('mode' => 'uninstall') );

        }

        // stop Action
        $versioning->endAction();


    }
}

