<?php 
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_community
 */
require_once 'OntoWiki/Controller/Component.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_community
 */
class CommunityController extends OntoWiki_Controller_Component
{
    public function listAction()
    {
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
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);
        
        $comments = array();
        if ($result = $this->_owApp->selectedModel->sparqlQuery($query)) {
            require_once 'OntoWiki/Utils.php';
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
    
    public function commentAction()
    {
        if (!$this->_owApp->selectedModel->isEditable()) {
            require_once 'Erfurt/Ac/Exception.php';
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
    
    public function rateAction()
    {
        
    }
}

