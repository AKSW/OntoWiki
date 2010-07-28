<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Navigation view helper
 *
 * @category OntoWiki
 * @package    OntoWiki_extensions_components_site
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Site_View_Helper_Navigation extends Zend_View_Helper_Abstract
{
    public function navigation()
    {
        return $this->_getSiteNavigationAsArray();
    }

    protected function _getSiteNavigationAsArray()
    {
        $store = OntoWiki::getInstance()->erfurt->getStore();
        $model = OntoWiki::getInstance()->selectedModel;

        $query = 'PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            SELECT ?topConcept
            FROM <' . (string)$model . '>
            WHERE {
                ?cs a skos:ConceptScheme .
                ?topConcept skos:topConceptOf ?cs
            }';

        if ($result = $store->sparqlQuery($query)) {
            $first = current($result);
            $topConcept = $first['topConcept'];
            $closure = $store->getTransitiveClosure(
                (string)$model,
                'http://www.w3.org/2004/02/skos/core#broader',
                $topConcept,
                true);

            $tree = array($topConcept => array());
            $this->_buildTree($tree, $closure);

            return array_merge(array('root' => $topConcept), $tree);
        }

        return array();
    }

    protected function _buildTree(&$tree, $closure)
    {
        foreach ($tree as $treeElement => &$childrenArr) {
            foreach ($closure as $closureElement) {
                if (isset($closureElement['parent']) && $closureElement['parent'] == $treeElement) {
                    $childrenArr[$closureElement['node']] = array();
                }
            }

            $this->_buildTree($childrenArr, $closure);
        }
    }

}
