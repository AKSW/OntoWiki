<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';
require_once realpath( dirname( __FILE__ ) ) . '/classes/HistoryProxyFilter.php';

/**
 * Description goes here.
 *
 * @category   OntoWiki
 * @package    Extensions_Historyproxy
 */
class HistoryproxyPlugin extends OntoWiki_Plugin
{

    private $callback;

    private $function;

    private $parameters;

    private $versioning;

    private $filter;


    public function onQueryHistory( $event ) {

        $logger = OntoWiki::getInstance()->logger;

        // set-up and enable the extended versioning
        require_once 'classes/ExtendedVersioning.php';
        $this->versioning = new Extended_Erfurt_Versioning();
        $this->filter = new HistoryProxyFilter();
        $this->versioning->enableVersioning( true );
        $this->versioning->setLimit( 100 );

        $this->function   = $event->function;
        $this->parameters = $event->parameters;

        // calling the function with the parameters as array
        $event->callback = call_user_func_array( array( $this, $this->function ) , $this->parameters );
    }

    /**
     * Returns the last changed resource.
     *
     * @param string  $graphUri the graph uri
     * @param string  $resource the resource
     */
    private function getLastChange( $graphUri, $resource ) {
        return  $this->versioning->getHistoryForResource( $resource, $graphUri );
    }

    /**
     * Returns the changed resources at a given day.
     *
     * @param string  $graphUri the graph uri
     * @param string  $date     the date
     */
    private function getChangesAtDate( $graphUri, $date, $type=null ) {
        return $this->getChangesFromRange( $graphUri, $date, $date, $type );
    }


    /**
     * Returns the changed resources in a certain range of time.
     *
     * @param [type]  $graphUri [description]
     * @param [type]  $from     [description]
     * @param [type]  $to       [description]
     * @return [type]           [description]
     */
    private function getChangesFromRange( $graphUri, $from, $to , $type=null ) {
        $result = $this->versioning->getRangeOfModifiedResources( $graphUri, $from, $to );
        if ( $type !== null ) {
            return $this->filter->filterByType( $type ,  $result );
        } else {
            return $result;
        }
    }

}
