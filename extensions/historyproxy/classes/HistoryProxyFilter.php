<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Historyproxy component controller.
 *
 * @category   OntoWiki
 * @package    Extensions_Historyproxy
 * @author     Sebastian Nuck
 * @copyright  Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class HistoryProxyFilter {
    public function filterByType( $type, $data ) {
        foreach ( $data as $elementKey => $element ) {
            $currentType = $this->findType( $element['resource'] );
            $logger = OntoWiki::getInstance()->logger;
            $logger->debug( '[here]' . $currentType );
            if ( $currentType !== $type ) {
                unset( $data[$elementKey] );
            }
        }
        return $data;
    }

    private function findType( $resource ) {
        $model = OntoWiki::getInstance()->selectedModel;
        $query = 'SELECT ?type WHERE { <' . $resource . '> a ?type }';
        $result = $model->sparqlQuery( $query );
        return $result[0]['type'];
    }
}