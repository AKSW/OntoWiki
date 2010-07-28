<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Lastchange view helper
 *
 * @category OntoWiki
 * @package    OntoWiki_extensions_components_site
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Site_View_Helper_Lastchange extends Zend_View_Helper_Abstract
{
    public function lastchange($uri)
    {
        // TODO: fill this value with the erfurt versioning api
        $return = array();
        $return['resourceUri'] = $uri;
        $return['timeStamp'] = time(); //unix timestamp
        $return['timeIso8601'] = '2010-07-28T07:48Z'; // ISO 8601 format
        $return['timeDuration'] = 'zzz min. ago'; //see last changes module
        $return['userTitle'] = 'Seebi'; // use titlehelper
        $return['userUri'] = 'http://sebastian.tramp.name';
        $return['userHref'] = 'http://sebastian.tramp.name'; //use URI helper

        return $return;
    }

}
