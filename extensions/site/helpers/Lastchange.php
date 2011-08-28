<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Lastchange view helper
 *
 * returns metadata of the last change of a resource
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Lastchange extends Zend_View_Helper_Abstract
{
    public function lastchange($uri)
    {
        // TODO: fill this value with the erfurt versioning api
        $versioning = Erfurt_App::getInstance()->getVersioning();
        $limit = $versioning->getLimit();
        $versioning->setLimit(1);
        $history = $versioning->getHistoryForResource($uri, (string)  OntoWiki::getInstance()->selectedModel);

        if (empty($history)) {
            return array(
                'resourceUri'   => $uri,
                'resourceTitle' => '',
                'timeStamp'     => '',
                'timeIso8601'   => '',
                'timeDuration'  => '',
                'userTitle'     => '',
                'userUri'       => '',
                'userHref'      => ''
            );
        }

        $versioning->setLimit($limit);
        $th = new OntoWiki_Model_TitleHelper(OntoWiki::getInstance()->selectedModel);
        $th->addResource($history[0]['useruri']);
        $th->addResource($uri);
        $return = array();
        $userUrl= new OntoWiki_Url(array('route'=>'properties'));
        $userUrl->setParam('r', $history[0]['useruri']);
        $return['resourceUri'] = $uri;
        $return['resourceTitle'] = $th->getTitle($uri);
        $return['timeStamp'] = $history[0]['tstamp']; //unix timestamp
        $return['timeIso8601'] = date('c',$history[0]['tstamp']); // ISO 8601 format

        try {
            $return['timeDuration'] = OntoWiki_Utils::dateDifference($history[0]['tstamp'], null, 3); // x days ago
        } catch (Exception $e) {
            $return['timeDuration'] = '';
        }

        $return['userTitle'] = $th->getTitle($history[0]['useruri']);
        $return['userUri'] = $history[0]['useruri'];
        $return['userHref'] = $userUrl; //use URI helper

        return $return;
    }
}
