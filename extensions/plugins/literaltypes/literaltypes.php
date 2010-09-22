<?php
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class LiteraltypesPlugin extends OntoWiki_Plugin {

    public function init() {
    }

    public function onDisplayLiteralPropertyValue( $event ) {

        $parameter = $event->getParams();

        if (!empty($parameter['datatype'])) {

            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            $url->setParam('r', $parameter['datatype'], true);
            $datatypeLink = "<a class=\"hasMenu\" about=\"".$parameter['datatype']."\" href=\"".((string) $url)."\">". (OntoWiki_Utils::getUriLocalPart($parameter['datatype'])) ."</a>";

            $ret = "<span>" . $parameter['value'] . "</span>";
            $ret .= "<span style = 'float:right;margin-left:2em'>[".$datatypeLink."]</span>";
            return $ret;
        }
        if (!empty($parameter['language'])) {
            $ret = "<span>" . $parameter['value'] . "</span>";
            $ret .= "<span style = 'float:right;margin-left:2em'>[".$parameter['language']."]</span>";
            return $ret;
        }
    }

}