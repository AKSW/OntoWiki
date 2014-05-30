<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Helper class for the Community component.
 *
 * - register the tab for navigation on properties view
 *
 * @category   OntoWiki
 * @package    Extensions_Community
 */
class CommunityHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        /*
         * check for $request->getParam('mode') == 'multi' if tab should also be displayed for
         * multiple resources/lists ($request->getActionName() == 'instances')
         * And set 'mode' => 'multi' to tell the controller the multi mode
         *
         * Multi mode was disabled because it doesn't seam to work
         */

        $owApp = OntoWiki::getInstance();

        if ($owApp->lastRoute == 'properties' && $owApp->selectedResource != null) {
            $owApp->getNavigation()->register(
                'community',
                array(
                    'controller' => 'community',
                    'action'     => 'list',
                    'name'       => 'Community',
                    'mode'       => 'single',
                    'priority'   => 50
                )
            );
        }
    }

    public function getList($view, $singleResource = true, $limit = null)
    {
        $store      = $this->_owApp->erfurt->getStore();
        $graph      = $this->_owApp->selectedModel;
        $resource   = $this->_owApp->selectedResource;

        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $commentType     = $this->_privateConfig->comment->type;
        $contentProperty = $this->_privateConfig->content->property;
        $dateProperty    = $this->_privateConfig->date->property;

        if ($limit === null) {
            $limit = $this->_privateConfig->limit;
        }

        // get all resource comments
        // Loading data for list of saved queries
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');

        $list = new OntoWiki_Model_Instances($store, $graph, array());

        $list->addTypeFilter($commentType, 'searchcomments');
        $list->addShownProperty($aboutProperty, "about", false, null, false);
        $list->addShownProperty($creatorProperty, "creator", false, null, false);
        $list->addShownProperty($contentProperty, "content", false, null, false);
        $list->addShownProperty($dateProperty, "date", false, null, false);
        $list->setLimit($limit);
        $list->setOrderProperty($dateProperty, false);

        if ($singleResource) {
            $list->addTripleFilter(
                array(
                     new Erfurt_Sparql_Query2_Triple(
                         $list->getResourceVar(),
                         new Erfurt_Sparql_Query2_IriRef($aboutProperty),
                         new Erfurt_Sparql_Query2_IriRef((string)$resource)
                     )
                )
            );
        } else {
            // doesn't work
            $list->addShownProperty($aboutProperty, "about", false, null, false);

            $instances   = $listHelper->getList('instances');
            $query       = clone $instances->getResourceQuery();
            $resourceVar = $instances->getResourceVar();

            $vars = $query->getWhere()->getVars();
            foreach ($vars as $var) {
                if ($var->getName() == $resourceVar->getName()) {
                    $var->setName('listresource');
                }
            }
            $elements = $query->getWhere()->getElements();
            //link old list to elements of the community-list
            $elements[] = new Erfurt_Sparql_Query2_Triple(
                $list->getResourceVar(),
                new Erfurt_Sparql_Query2_IriRef($aboutProperty),
                $var
            );
            $list->addTripleFilter($elements, "listfilter");
        }

        $listName   = "community";
        $other      = new stdClass();
        $other->singleResource  = $singleResource;
        $other->statusBar       = false;
        if ($list->hasData()) {
            return $listHelper->addListPermanently(
                $listName, $list, $view, 'list_community_main', $other, true
            );
        } else {
            return null;
        }
    }

    public function getMultiList()
    {
        $this->store = $this->_owApp->erfurt->getStore();
        $this->model = $this->_owApp->selectedModel;

        /* prepare schema elements */
        // TODO: This should be used from the CommunityController
        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $commentType     = $this->_privateConfig->comment->type;
        $contentProperty = $this->_privateConfig->content->property;
        $dateProperty    = $this->_privateConfig->date->property;

        $realLimit = $this->_privateConfig->limit + 1; // used for query to check for "more"

        // get the latest comments
        $commentSparql
            = 'SELECT DISTINCT ?resource ?author ?comment ?content ?date #?alabel
            WHERE {
                ?comment <' . $aboutProperty . '> ?resource.
                ?comment a <' . $commentType . '>.
                ?comment <' . $creatorProperty . '> ?author.
                ?comment <' . $contentProperty . '> ?content.
                ?comment <' . $dateProperty . '> ?date.
            }
            ORDER BY DESC(?date)
            LIMIT ' . $realLimit;

        $query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);

        return $this->model->sparqlQuery($query);
    }
}
