<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Objectlist view helper
 *
 * todo: documentation
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Objectlist extends Zend_View_Helper_Abstract
{
    public function objectlist($objectArray, $titleHelper, $separator = ', ')
    {
        $list = array();
        foreach ((array) $objectArray as $object) {
            if (isset($object['type'])) {
                if ($object['type'] == 'uri') {
                    $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
                    $url->setParam('r', $object['value'], true);

                    $link = sprintf('<a href="%s">%s</a>', (string) $url, $titleHelper->getTitle($object['value']));
                    array_push($list, $link);
                } else {
                    array_push($list, $object['value']);
                }
            }
        }

        return implode($separator, $list);
    }
}
